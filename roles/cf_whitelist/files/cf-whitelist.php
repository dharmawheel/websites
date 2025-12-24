#!/usr/bin/env php
<?php
use Symfony\Component\Console\Input\ArgvInput;

if (php_sapi_name() != 'cli')
{
    echo 'This program must be run from the command line.' . PHP_EOL;
    exit(1);
}

define('IN_PHPBB', true);

$phpbb_root_path = getcwd() . '/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'phpbb/class_loader.' . $phpEx);

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
$phpbb_class_loader->register();

$phpbb_config_php_file = new \phpbb\config_php_file($phpbb_root_path, $phpEx);
extract($phpbb_config_php_file->get_all());

if (!defined('PHPBB_ENVIRONMENT'))
{
    @define('PHPBB_ENVIRONMENT', 'production');
}

require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/functions.' . $phpEx);
require($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);
require($phpbb_root_path . 'includes/functions_compatibility.' . $phpEx);

$phpbb_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
$phpbb_container = $phpbb_container_builder->with_config($phpbb_config_php_file);

$input = new ArgvInput();

/** TODO: Add command line parameters **/

$phpbb_container = $phpbb_container_builder->get_container();
$phpbb_container->get('request')->enable_super_globals();
require($phpbb_root_path . 'includes/compatibility_globals.' . $phpEx);

register_compatibility_globals();

/** @var \phpbb\config\config $config */
$config = $phpbb_container->get('config');

/** @var \phpbb\db\driver\driver_interface $db */
$db = $phpbb_container->get('dbal.conn');

/** @var \phpbb\language\language $language */
$language = $phpbb_container->get('language');
$language->set_default_language($config['default_lang']);
$language->add_lang(array('common', 'acp/common', 'cli'));

/* @var $user \phpbb\user */
$user = $phpbb_container->get('user');
$user->data['user_id'] = ANONYMOUS;
$user->ip = '127.0.0.1';

$cloudflare_account_id = getenv('CLOUDFLARE_ACCOUNT_ID');
if ($cloudflare_account_id == false || $cloudflare_account_id == '') {
    echo 'Environment variable CLOUDFLARE_ACCOUNT_ID is required' . PHP_EOL;
    exit(1);
}
$cloudflare_api_key = getenv('CLOUDFLARE_API_KEY');
if ($cloudflare_api_key == false || $cloudflare_api_key == '') {
    echo 'Environment variable CLOUDFLARE_API_KEY is required' . PHP_EOL;
    exit(1);
}

define('CF_API_URL', 'https://api.cloudflare.com/client/v4');

function callCfApi($method, $endpoint, $data = null) {
    global $cloudflare_api_key;

    assert(in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD']), "Invalid HTTP verb: $method");

    $url = CF_API_URL . $endpoint;
    $headers = [
        'Authorization: Bearer ' . $cloudflare_api_key,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($data !== null && ($method == 'POST' || $method == 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'errors' => [['message' => "cURL Error: $error"]]];
    }

    return json_decode($response, true);
}

function getAllZones() {
    $zones = [];
    $page = 1;
    $perPage = 50;

    do {
        $queryParams = http_build_query([
            "page" => $page,
            "perPage" => $perPage,
        ]);
        $endpoint = "/zones?$queryParams";
        $response = callCfApi('GET', $endpoint);
        if (!response) {
            break;
        }
        foreach ($response['result'] as $zone) {
            $zones[] = [
                'id' => $zone['id']
            ];
            $totalPages = $response['result_info']['total_pages'];
            $page++;
        }
    } while ($page < $totalPages);

    return $zones;
}

function isIp6($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
}

function allowlistIPs(array $ipList) {
    global $cloudflare_account_id;

    foreach ($ipList as $ip) {
        $target = 'ip';
        if (isIp6($ip)) {
            $target = 'ip6';
        }
        $payload = [
            'mode' => 'whitelist',
            'configuration' => [
                'target' => $target,
                'value' => $ip,
            ],
            'notes' => '(automated) registered user allowlist on ' . date('Y-m-d'),
        ];
        $endpoint = "/accounts/$cloudflare_account_id/firewall/access_rules/rules";
        $result = callCfApi('POST', $endpoint, $payload);
        if (!$result['success']) {
            if (!empty($result['errors'])) {
                $errorCode = $result['errors'][0]['code'];
                if ($errorCode !== 10009) {
                    echo 'Failed to add ' . $ip . ' to allowlist: ' . $result . PHP_EOL;
                }
            } else {
                echo 'Failed to add ' . $ip . ' to allowlist: ' . $result . PHP_EOL;
            }
        }
    }
}

function recentIpsOfRegisteredUsers() {
    global $db;

    $result = [];
    $sql = 'select distinct p.poster_ip ip from ' . POSTS_TABLE . ' p inner join ' . USERS_TABLE . ' u on u.user_id = p.poster_id where u.group_id not in (1, 7) and p.post_time >= unix_timestamp() - 86400';
    $sql_result = $db->sql_query($sql);
    while ($row = $db->sql_fetchrow($sql_result)) {
        if (isset($row['ip'])) {
            $result[] = $row['ip'];
        }
    }
    $db->sql_freeresult($sql_result);
    return $result;
}

$ipList = recentIpsOfRegisteredUsers();
allowlistIPs($ipList);

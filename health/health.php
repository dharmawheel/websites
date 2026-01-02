<?php

$virtualHosts = array(
    array('host' => 'www.dhammawheel.com', 'path' => '/', 'nfs_test_dir' => '/var/www/com_dhammawheel_www/images/avatars'),
    array('host' => 'www.dharmawheel.net', 'path' => '/', 'nfs_test_dir' => '/var/www/net_dharmawheel_www'),
    array('host' => 'www.dharmapaths.com', 'path' => '/', 'nfs_test_dir' => '/var/www/com_dharmapaths_www'),
    array('host' => 'www.dhammawiki.com', 'path' => '/index.php?title=Special:Search', 'nfs_test_dir' => '/var/www/com_dhammawiki_www/images/archive'),
);

function checkVirtualHost($host, $path, $nfs_test_dir) {
    $url = "https://$host$path";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RESOLVE, array("$host:443:127.0.0.1"));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $nfs_writable = is_writable($nfs_test_dir);

    return [
        'host' => $host,
        'status' => ($httpCode == 200 && $nfs_writable) ? 'UP' : 'DOWN',
	'httpCode' => $httpCode,
	'nfs' => $nfs_writable
    ];
}

$results = [];
foreach ($virtualHosts as $vh) {
    $results[] = checkVirtualHost($vh['host'], $vh['path'], $vh['nfs_test_dir']);
}

$overallStatus = 'UP';
foreach ($results as $result) {
    if ($result['status'] == 'DOWN') {
        $overallStatus = 'DOWN';
        break;
    }
}

if ($overallStatus == 'UP') {
    http_response_code(200);
} else {
    http_response_code(500);
}

header('Content-Type: application/json');
echo json_encode([
    'overallStatus' => $overallStatus,
    'checks' => $results
]);

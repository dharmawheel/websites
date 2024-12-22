<?php

$virtualHosts = array(
    array('host' => 'www.dhammawheel.com', 'path' => '/'),
    array('host' => 'www.dharmawheel.net', 'path' => '/'),
    array('host' => 'www.dharmapaths.com', 'path' => '/'),
    array('host' => 'www.dhammawiki.com', 'path' => '/index.php?title=Special:Search'),
);

function checkVirtualHost($host, $path) {
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

    return [
        'host' => $host,
        'status' => ($httpCode == 200) ? 'UP' : 'DOWN',
        'httpCode' => $httpCode
    ];
}

$results = [];
foreach ($virtualHosts as $vh) {
    $results[] = checkVirtualHost($vh['host'], $vh['path']);
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

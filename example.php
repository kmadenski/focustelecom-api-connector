<?php
/**
 * Created by PhpStorm.
 * User: kmadenski
 * Date: 01.07.19
 * Time: 18:55
 */
$connector = new \App\Core\Connector(
    "login.admin",
    "apiKey",
    "domain",
    "50char change",
    "baseUrl"
);

echo json_encode(json_decode($connector->campaignList(), JSON_PRETTY_PRINT));

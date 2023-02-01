<?php
define("CONSUMER_KEY", "");
define("CONSUMER_SECRET", "");
define("OAUTH2_TOKEN_URL", "https://drural-api-manager.oasc.fr:9443/oauth2/token");
define("EMAIL", "");
define("PASSWORD", "");

define("DEVICES_IDS", [
    "63d7ef32fe7675d7d4ea9960",
    "63d7ef84fe7675d7d4ea998d",
    "63d7efd6fe7675d7d4ea99a2",
]);

ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("./vendor/autoload.php");

$data = [
    "grant_type" => "client_credentials"
];
$options = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/x-www-form-urlencoded\r\n" .
            "Authorization: Basic " . base64_encode(CONSUMER_KEY . ":" . CONSUMER_SECRET) . "\r\n",
        "content" => http_build_query($data)
    ]
];
$context  = stream_context_create($options);

$response = file_get_contents(OAUTH2_TOKEN_URL, false, $context);

if (!$response) {
    trigger_error("The access token request failed.", E_USER_ERROR);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data["access_token"])) {
    trigger_error("The response to the access token request does not contain the access token.", E_USER_ERROR);
}

$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken($data["access_token"]);

$userApi = new Swagger\Client\Api\UserApi(
    new GuzzleHttp\Client(),
    $config
);

$devicesApi = new Swagger\Client\Api\DevicesApi(
    new GuzzleHttp\Client(),
    $config
);

$data = [
    "email" => EMAIL,
    "password" => PASSWORD
];
$body = new \Swagger\Client\Model\LoginAPI($data);
$result = $userApi->loginUser($body);

define("ACCESS_TOKEN", $result->getObject()->getToken()->getValue());

$devices = [];
foreach(DEVICES_IDS as $id) {
    $devices[] = $devicesApi->readDevice($id, ACCESS_TOKEN)["object"];
}

$devices = ["objects" => $devices];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>dRural API Gateway - PHP SDK example</title>

    <link rel="stylesheet" href="./css/index.css"/>

    <!-- Leaflet 1.8.0 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css"/>

    <!-- Bootstrap 4.6.2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"/>
</head>

<body>
    <div id="map"></div>

    <script>
        const devices = <?= json_encode($devices) ?>;
    </script>

    <!-- jQuery 3.6.0 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Leaflet 1.8.0 -->
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js"></script>

    <!-- Bootstrap 4.6.2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootbox 5.5.3 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.3/bootbox.min.js"></script>

    <script src="./js/index.js?time=<?= time() ?>"></script>
</body>

</html>

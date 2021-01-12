<?php
// $rootUrl = "www.everydaywinner.com";
// to delete
$rootUrl = "west.everydaywinner.com";

// $whichProtocol = ((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == "on") ? 'https://' : 'http://');
//to delete
$whichProtocol = "http://";
$rootUrl = $whichProtocol . $rootUrl;

$prizeEndpoint = $rootUrl . "/feature/EDW/app/confirm/currentPrize/WG";
$response = json_decode(file_get_contents($prizeEndpoint));

if (!empty($response)) {
    echo $response->data->current_prize;
} else {
    echo "Error getting the current prize";
}

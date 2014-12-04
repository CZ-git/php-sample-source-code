<?php

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);
$a = get_app_record($conn, $app_id);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0","../../");

$srv_defaults = array(
    'google_key' => $a['name'],
    'twitter_key' => $a['name'],
    'facebook_key' => $a['name'],
    'background' => ''
);

$v2 = safe_unserialize($t["value2"]);
if(!is_array($v2)) {
    $v2 = $srv_defaults;
}

foreach($v2 AS $key => $srv) {

    if($key == "google") {
        if ( $srv['key'] ) {
        $srv_defaults['google_key'] = $srv['key'];
        }
    } else if($key == "twitter") {
        if ( $srv['key'] ) {
        $srv_defaults['twitter_key'] = $srv['key'];
        }
    } else if($key == "facebook") {
        if ( $srv['key'] ) {
        $srv_defaults['facebook_key'] = $srv['key'];
    }
}
}

$srv_defaults["background"] = $bg_image;
$feed[] = $srv_defaults;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
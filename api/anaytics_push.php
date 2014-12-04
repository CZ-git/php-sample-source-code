<?php

include "dbconnect.inc";
include "app.inc";
require_once "ray_model/app_stats.php";

$data = make_data_safe($_REQUEST);

// $app_name = ereg_replace("[^[:alnum:]]", "", $data["appCode"]);
// Not necessary as APNS are basically broken in the master app
// The user agent should always be enough to identify the app

// Should check if all required pararmeters are enterd
$required_params = array(
    "latitude",
    "longitude",
    "app_code",
    "action",
    "date",
    "hits",
);

$required_params_details = array (
    "tab_id",
);

foreach($required_params AS $v) {
    if(!$data[$v]) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
}

$M = new AppStats($conn);

$hits = intval($data["hits"]);
if($hits < 1) $hits = 1;

if($data["action"] == "1") {
	$M->add_app_stat($app_id, $hits, $data["date"], $data["latitude"], $data["longitude"]);
} else if($data["action"] == "2") {
    
    foreach($required_params_details AS $v) {
        if(!$data[$v]) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

	$M->add_app_stat_details($app_id, intval($data["tab_id"]), intval($data["detail_id"]), $hits, $data["date"], $data["latitude"], $data["longitude"]);
}



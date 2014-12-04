<?php
include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/app_access.php";

$data = make_data_safe($_REQUEST);



$M = new AppAccess($conn);
$access = $M->validate_access($app_id, $data["user"], $data["pass"]);


$feed = array(
    array(
        "error" => ($access)?0:1
    )
);

$json = json_encode($feed);

header('Content-type: application/json');
header("Content-encoding: gzip");
echo gzencode($json);



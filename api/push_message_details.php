<?php

/* 
Push notifications details
*/

include_once "dbconnect.inc";
include_once "app.inc";
include_once "ray_model/push_notifications.php";

$data = make_data_safe($_REQUEST);

$M = new PushNotifications($conn);

$row = $M->get_message_details($data["push_id"], "1", "first");
if($row == false) {
    $row = array();
} else {
    if ( $row['duration_mode'] && $row['active_until'] && $row['active_until'] > '0000-00-00 00:00:00' ) {
        $row['active_until'] = strtotime($row['active_until']) - get_server_timezone_offset();
    } else {
        $row['active_until'] = "0";
    }
}

$message = $M->get_message_by_id($data["push_id"]);
$app = get_app_record($conn, $message["app_id"]);

// Do some processing for ... template thing
if($row["rich_type"] == "3") {
    //$row["rich_type"] = "2";
    $row["rich_url"] = $PUBLIC_WEB_HOME_URL . "/adv_board/" . $app["code"] . "/" . $row["rich_adv"];
}

$json = json_encode(array($row));
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

<?php

include "dbconnect.inc";
include "app.inc";

include "ray_model/common.php";
include "ray_model/laundry.php";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$feed = array();
//-------------------------------------------------------------------
// Retrieve Backgrounds
//-------------------------------------------------------------------
$image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

//-------------------------------------------------------------------
// Retrieve Locations
//-------------------------------------------------------------------
$locations = get_tab_location($conn, $data["tab_id"]);

//-------------------------------------------------------------------
// Retrieve Open Time
//-------------------------------------------------------------------
$open_times = array();
$open_times_set = get_app_tab_time($conn, $app_id, $data["tab_id"]);
foreach($open_times_set AS $tm) {
	
	if($tm["open_time"] == $tm["close_time"]) {
		$tm_time = "Close";
	} else {
		$tm_time = minutes_to_time(intval($tm["open_time"]), $tm["day"])." ~ ".minutes_to_time(intval($tm["close_time"]), $tm["day"]);
	}
	
	$opt = array(
		"day" => $tm["day"],
		"time" => $tm_time,
	);
	$open_times[] = $opt;
}

//-------------------------------------------------------------------
// Retrieve Main Info
//-------------------------------------------------------------------
$main_info = get_house_information($conn, $app_id, $data["tab_id"]);

//-------------------------------------------------------------------
// Compose feed
//-------------------------------------------------------------------
$item = array(
	"name" => $main_info["center_name"],
	"locations" => $locations,
	"brief" => $main_info["center_brief"],
	"background" => $image,
	"open_time" => $open_times,
	"admin_email" => $main_info["admin_email"],
	"with_approval" => $main_info["with_approval"],
);
$feed[] = $item;	

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

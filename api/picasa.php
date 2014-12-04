<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);

$srv_defaults = array(
	'picasa' => array(
			'userid' => '',
			'gtype' => '1',
			'active' => '0'
		)
);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

if (is_array($t)) {
	
	$sett = unserialize($t['value2']);
	
	if(!isset($sett["picasa"])) $sett["picasa"] = array();
	$tv = array_merge($srv_defaults["picasa"], $sett["picasa"]);
} else {
	$tv = $srv_defaults["picasa"];
}

/*
if(strpos($tv['userid'], "@") !== false)   {
	$tv['userid'] = substr($tv['userid'], 0, strpos($tv['userid'], "@"));
}
*/


$feed[] = array(
			"userId" => ($tv['userid'])?$tv['userid']:"", 
			"gallery_type" => ($tv['gtype'] == "1")?"coverflow":"gallery",
			"background" => $bg_image
);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
?>

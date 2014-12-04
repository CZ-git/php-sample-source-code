<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);

$srv_defaults = array(
	'flickr' => array(
			'userid' => '',
			'apikey' => '',
			'disp' => '1',
			'gtype' => '1',
			'active' => '0'
		)
);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

if (is_array($t)) {
	
	$sett = unserialize($t['value2']);
	
	if(!isset($sett["flickr"])) $sett["flickr"] = array();
	$tv = array_merge($srv_defaults["flickr"], $sett["flickr"]);
} else {
	$tv = $srv_defaults["flickr"];
}
  
$feed[] = array(
			"userId" => ($tv['userid'])?$tv['userid']:"", 
			"APIKEY" => ($tv['apikey'])?$tv['apikey']:"",
			"display" => ($tv['disp'] == "1")?"photo_set":"gallery",
			"gallery_type" => ($tv['gtype'] == "1")?"coverflow":"gallery",
			"background" => $bg_image
);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
?>

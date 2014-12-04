<?php

include_once "dbconnect.inc";
include_once "app.inc";

require_once "ray_model/app_connect.php";
include_once "instagram/instagram_conf.php";
		

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);

$srv_defaults = array(
	'instagram' => array(
			'gtype' => '1',
			'active' => '0'
	)
);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

if (is_array($t)) {
	
	$sett = unserialize($t['value2']);
	
	if(!isset($sett["instagram"])) $sett["instagram"] = array();
	$tv = array_merge($srv_defaults["instagram"], $sett["instagram"]);
} else {
	$tv = $srv_defaults["instagram"];
}

/*
if(strpos($tv['userid'], "@") !== false)   {
	$tv['userid'] = substr($tv['userid'], 0, strpos($tv['userid'], "@"));
}
*/

$M = new AppConnect($conn);
$row = $M->get_info($app_id, $data["tab_id"], "2", "first");
$token = $row["token"];
$user = json_decode($row["etc"]);

if($Instagram->validate_auth($token, $user->id)) {
	
	$feed = array(
				"gallery_type" => ($tv['gtype'] == "1")?"coverflow":"gallery",
				"token" => $token,
				"user_id" => $user->id,
				"user_name" => $user->username,
	);
	
} else {

	$feed = array(
				"gallery_type" => ($tv['gtype'] == "1")?"coverflow":"gallery",
				"token" => "",
				"user_id" => "",
				"user_name" => "",
	);
	
}

$feed["background"] = $bg_image;

$json = json_encode( array($feed) );
header("Content-encoding: gzip");
echo gzencode($json);


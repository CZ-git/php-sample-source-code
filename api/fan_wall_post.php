<?php

include_once "dbconnect.inc";
include_once "app.inc";

/**
 * Add field googleplus_id for GooglePlus
 * alter table `bizapps_apps`.`fan_wall_comments` 
   add column `googleplus_id` varchar(64) CHARSET utf8 COLLATE utf8_unicode_ci NULL after `twitter_id`
 */
$data = make_data_safe($_REQUEST);


$stuff = "fan713" . $data["fb_id"] . $data["tw_id"];
$hash = md5($stuff);

if ($hash != $data["hash"]) {
  return_json_error("Hash error: expecting $hash - md5($stuff), received $data[hash]");
}

$required_fields = array("tab_id", "name", "comment");
foreach($required_fields as $field) {
  if (!$data[$field]) {
    return_json_error("Must send a value for $field");
  }
}

// check social network account information
if (!$data["fb_id"] && !$data["tw_id"] && !$data["gp_id"]) {
  return_json_error("Must send either Facebook or Twitter ID");
}

// get facebook and twitter user avatar image url, if avatar parameter is empty
if(empty($data["avatar"]) || $data["avatar"] == "") {
    if($data["fb_id"]) { // facebook
        $data["avatar"] = "http://graph.facebook.com/".$data["fb_id"]."/picture";
    }
}

$data['created'] = gmdate('Y-m-d H:i:s');

if ( !$data['parent_id'] )
	$data['parent_id'] = 0;

$sql = "insert into fan_wall_comments (app_id, tab_id, parent_id, created, name, facebook_id, twitter_id, googleplus_id, youtube_id, comment, avatar)
        values ('$app_id', '$data[tab_id]', '$data[parent_id]', '$data[created]', '$data[name]', '$data[fb_id]', '$data[tw_id]', '$data[gp_id]', '$data[yt_id]', '$data[comment]', '$data[avatar]')";
if($data["debug"] == "1") {
	print_r($data);
	exit;
}

$res = mysql_query($sql, $conn);
if (!$res) {
  return_json_error($sql." ".mysql_error());
} else {
	$feed = array(
		array('result' => 'success')
	);
	$json = json_encode($feed);
	header("Content-encoding: gzip");
	echo gzencode($json);
}

die();

?>
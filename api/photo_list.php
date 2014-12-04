<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$image = "";
$button = "";

$data["type"] = get_app_tab_type($conn, $data["tab_id"]);


$and .= " 	app_id = '$app_id' AND 
			tab_id = '$data[tab_id]' AND
			detail_type = '$data[type]' AND
			detail_id = '$data[id]'     
		";

if (!$data["count"])
  $data["count"] = 20;

$limit = "";
if ($data["count"] > 0) {
  $limit = "limit $data[count]";
  if ($data["offset"] > 0)
    $limit .= " offset $data[offset]";
}

if ($data["show_all"])
  $limit = "";

$sql = "SELECT *
        FROM app_user_photos
        WHERE $and
        ORDER BY created desc
        $limit";  

$res = mysql_query($sql, $conn);

$feed = array();
$now = gmmktime();

$num=0;
while ($l = mysql_fetch_array($res)) {

    $num++;

	$timestamp = strtotime($l["created"]) - get_server_timezone_offset();
	$timeago = time_ago($now - $timestamp);
	if ( strtolower($timeago) == 'just now' )
		$timeago = '1 min ago';
	
	$image = "";
	$image_file = findUploadDirectory($app_id) . "/user_photos/$data[type]/$data[id]/photo_$l[id].$l[ext]";
	if(file_exists($image_file)) {
		$image = $WEB_HOME_URL.'/custom_images/'.$data[app_code].'/user_photos/'.$data[type].'/'.$data[id].'/photo_'.$l[id].'.'.$l[ext];
	}
	
	if($image != "") {
		$images[] = array(
			"id" => $l["id"],
			"time_ago" => $timeago,
			"timestamp" => $timestamp,
			"caption" => $l["caption"],
			"image" => $image
		);
	}
}


if ( !count($images) ) {
	$images = array(
		array("id" => 0, "caption" => "No photos"),
	);  	
}


$json = json_encode($images);
header("Content-encoding: gzip");
echo gzencode($json);

?>

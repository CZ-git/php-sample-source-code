<?php

include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);
/*
if ($data["tab_id"]) {
  $t = get_app_tab_record($conn, $data["tab_id"]);
}
*/

$sql = "SELECT m.*, a.code FROM music_detail AS m
		LEFT JOIN apps AS a ON m.app_id = a.id
		WHERE m.app_id = '$app_id' AND m.id = '$data[id]'";
$res = mysql_query($sql, $conn);

$feed = array();

$albums = array();
while ($music = mysql_fetch_array($res)) {
	foreach ( $music as $k => $v ) {
		$music[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
	}

	$item = array(
		"id" => $music["id"],
		"title" => $music["title"],
		"artist" => $music["artist"],
		"album" => $music["album"],
		"itune" => $music["itune_url"],
		"note" => $music["note"],
		"lyrics" => $music["lyrics"],
	); 
	
	if($item["itune"] != "") {
		$item["onsale"] = "1";
	} else {
		$item["onsale"] = "0";
	}
	
	// Check URL
	$album_art = "";
	if(preg_match("/^http:\/\/(.*)$/", $music["album_art"]) || preg_match("/^http:\/\/(.*)$/", $music["album_art"])) {
		$album_art = $music["album_art"];	
	} else {
		$dir = findUploadDirectory($app_id)."/album_art/".$music["album_art"];
		if(file_exists($dir) && !is_dir($dir)) {
			$album_art = $WEB_HOME_URL.'/custom_images/'.$music[code].'/album_art/'.$music["album_art"].'?width=100&height=100';
		}
	}
	$item["album_art"] = $album_art; 
	
	$track = "";
	if(preg_match("/^http:\/\/(.*)$/", $music["track"]) || preg_match("/^http:\/\/(.*)$/", $music["track"])) {
		$track = $music["track"];
	} else {
		$dir = findUploadDirectory($app_id, "tracks") . "/" . $music["track"];
		if(file_exists($dir) && !is_dir($dir)) {
			$track = $WEB_HOME_URL."/uploads/tracks/" . getUploadRelPath(1, $app_id) . "/" . $music["track"];
		}
	}
	$item["previewUrl"] = $track; 
	
	$feed[] = $item;
}


$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

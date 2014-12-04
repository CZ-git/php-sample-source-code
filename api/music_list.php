<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

if ($data["tab_id"]) {
  $t = get_app_tab_record($conn, $data["tab_id"]);
}
if($t["value1"] == "") $t["value1"] = "000000";

$a = get_app_record($conn, $app_id);

// Retrieve background
$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

// Retrieve header image
$header_image = "";
if ( $data['device'] == 'ipad' ) {
    $header_file = findUploadDirectory($app_id) . "/album_header/$data[tab_id]_ipad.jpg";
    if(file_exists($header_file)) {
        $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/album_header/$data[tab_id]_ipad.jpg";
    }
} else {
    $header_file = findUploadDirectory($app_id) . "/album_header/$data[tab_id].jpg";
    if(file_exists($header_file)) {
        $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/album_header/$data[tab_id].jpg";
    }
}

$feed = array();

$and = "";

$sql = "SELECT m.*, a.code FROM music_detail AS m
        LEFT JOIN apps AS a ON m.app_id = a.id
        WHERE m.app_id = '$app_id' AND m.tab_id = '$data[tab_id]' AND m.is_active=1 AND NOT(m.track='') $and ORDER BY m.seq";

$res = mysql_query($sql, $conn);

$is_first = true;
$ad_info = array(
    "tint" => $t["value1"],
    "background" => $image,
    "header" => $header_image,
);

while ($music = mysql_fetch_array($res)) {

    if ( $data['device'] == 'android' ) {
        if( !ereg("api.7digital.com", $music["track"]) ) {
            if ( intval($music['is_for_android']) ) {
                $filenames = split( '[.]', strtolower( $music["track"] ) );
                if($filenames[count($filenames) - 1] != 'mp3') {
                    if ( !preg_match("/stream/i", $music["track"]) )
                        continue;
                }
            } else {
                continue;
            }
        }
    } else {
        if( ereg("api.7digital.com", $music["track"]) ) {
            continue;
        }
    }
    
    $music["title"] = str_replace( array("&amp;", "&#039;"), array("&", "'"), $music["title"] );
    $music["artist"] = str_replace( array("&amp;", "&#039;"), array("&", "'"), $music["artist"] );
    $music["album"] = str_replace( array("&amp;", "&#039;"), array("&", "'"), $music["album"] );

    $item = array(
        "id" => $music["id"],
        "title" => $music["title"],
        "artist" => $music["artist"],
        "album" => $music["album"],
        "itune" => $music["itune_url"],
        "duration" => $music["duration"],
        "note" => $music["note"],
        "lyrics" => $music["lyrics"],
        "parentTrackTitle" => "",
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
        $dir = findUploadDirectory($app_id) . "/album_art/".$music["album_art"];
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
            $track = $WEB_HOME_URL."/uploads/tracks/" . getUploadRelPath(1, $app_id) . "/" . rawurlencode($music["track"]);
        }
    }

    $item["previewUrl"] = $track; 
    
    if($is_first) {
        $item = array_merge($item, $ad_info);    
        $is_first = false;
    }
    
    $feed[] = $item;    
}


$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);


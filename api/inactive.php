<?php

include "dbconnect.inc";
include "app.inc";

$a = get_app_record($conn, $app_id);
$data = make_data_safe($_GET);

$image = "";
if($data["device"] == "ipad") {
	$image_file = findUploadDirectory($app_id) . "/ipad/home.jpg";
	if (file_exists($image_file))
	  $image = $WEB_HOME_URL.'/custom_images/'.$a["code"].'/ipad/home.jpg';
} else {
	$image_file = findUploadDirectory($app_id) . "/home.jpg";
	if (file_exists($image_file))
	  $image = $WEB_HOME_URL.'/custom_images/'.$a["code"].'/home.jpg';
}	

$feed[] = array("image" => $image);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

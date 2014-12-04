<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$image = "";
$button = "";

/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file))
  $button = base64_encode(file_get_contents($button_file));*/
		
$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

$this_item = array(
	"image" => $image,
	"CustomButton" => $button
);
$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

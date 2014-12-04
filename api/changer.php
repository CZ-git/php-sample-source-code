<?php

include "dbconnect.inc";
include "app.inc";
include "app_byuser.inc";

$this_item = array();


$image_file = findUploadDirectory($app_id) . "/home.jpg";
if (file_exists($image_file))
  $image = base64_encode(file_get_contents($image_file));
else
  $image = "";
$this_item["image"] = $image;


// if ($qry["custom_button"]) {

  $button_file = findUploadDirectory($app_id) . "/button.png";
  if (file_exists($button_file)) {
    $image = base64_encode(file_get_contents($button_file));
    $this_item["CustomButton"] = $image;
  }

// }

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

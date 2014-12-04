<?php

include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);
if ($t["value1"])
  $width = $t["value1"];
else
  $width = 100;

if ($t["value2"])
  $height = $t["value2"];
else
  $height = 100;
  
if ($t["value3"] == '1')
  $isCoverflow = 'y';
else
  $isCoverflow = 'n';


$sql = "SELECT id, seq, width, height, info from gallery_images
	WHERE app_id = '$app_id'
	and tab_id = '$data[tab_id]'
	ORDER BY seq";

$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $feed[] = array("id" => "0");
} else {
	while ($qry = mysql_fetch_array($res)) {

	  foreach ($qry as $key => $val)
		// $qry[$key] = preg_replace('/[^(\x20-\x7F)]*/','', $val);
                $qry[$key] = preg_replace('/[(\x0-\x1F)]*/','', $val);


	  $img[] = array(
	  	"id" => $qry["id"], 
	  	"width" => $qry["width"], 
	  	"height" => $qry["height"],
	   	"info" => $qry["info"]
	  );
	}
}

$feed[] = array(
	"width" => $width, 
	"height" => $height,
	"coverflow" => $isCoverflow,
	"images" => $img
);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
?>

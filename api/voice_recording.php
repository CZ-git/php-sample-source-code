<?php

include_once "dbconnect.inc";
include_once "app.inc";


$data = make_data_safe($_GET);

if ($data[tab_id])
  $and = "and id = '$data[tab_id]' ";

 
  $sql = "select *
        from app_tabs
        where app_id = '$app_id'
        $and
        and view_controller = 'VoiceRecordingViewController'
        order by id";
$res = mysql_query($sql, $conn);
$tab = mysql_fetch_array($res);
 
$image = "";
$button = "";
/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file))
  $button = base64_encode(file_get_contents($button_file));*/
		
$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

foreach ( $tab as $k => $v ) {
	$tab[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
}

$this_item = array("description" => $tab["value1"],
                   "email" => $tab["value2"],
                   "subject" => $tab["value3"],
                   "image" => $image,
                   "CustomButton" => $button);

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

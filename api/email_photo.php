<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

if ($data[tab_id])
  $and = "and id = '$data[tab_id]' ";

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$sql = "select *
        from app_tabs
        where app_id = '$app_id'
        $and
        and view_controller = 'EmailPhotoViewController'
        order by id";
$res = mysql_query($sql, $conn);
$tab = mysql_fetch_array($res);

$button = "";

foreach ( $tab as $k => $v ) {
	$tab[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
}

$this_item = array("description" => $tab["value1"],
                   "email" => $tab["value2"],
                   "subject" => $tab["value3"],
                   "CustomButton" => $button,
				   "background" => $bg_image);

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

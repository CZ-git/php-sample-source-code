<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$t = get_app_tab_record($conn, $data["tab_id"]);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, '0', '../../');

$result = array(
	'background' => $bg_image
);

$json = json_encode( array( $result ) );
header("Content-encoding: gzip");
echo gzencode($json);

die();
?>
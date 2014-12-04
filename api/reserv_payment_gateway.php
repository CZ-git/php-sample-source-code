<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";
include_once "ray_model/reserv.php";


$data = make_data_safe($_GET);
include_once("appuser_action_checktoken.php");

$payment_options = get_payment_options($conn, $app_id, $data["tab_id"], " AND is_main='1' AND gateway_type in (1, 4) ");

$main_info = get_center_information($conn, $app_id, $data["tab_id"]);

$feed = array();

$mandatories = array(
	"1" => array("gateway_appid", "gateway_key", "gateway_password", "gateway_signature",),
	"2" => array("gateway_appid", "gateway_key",),
	"3" => array("gateway_appid", "gateway_key",),
);

foreach($payment_options AS $po) {
	$is_filledup = true;
	foreach($mandatories[$po["gateway_type"]] AS $m) {
		if(trim($po[$m]) == "") {
			$is_filledup = false;
			break;
		} 
	}
	$po["currency"] = $main_info["currency"];
	
	if($is_filledup) $feed[] = $po;
}

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";
include_once "ray_model/reserv.php";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$feed = array();
//-------------------------------------------------------------------
// Retrieve Backgrounds
//-------------------------------------------------------------------
$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

$header_image = "";
if ( $data['device'] == 'ipad' ) {
	$header_file = findUploadDirectory($app_id) . "/reserv_header/$data[tab_id]_ipad.jpg";
	if(file_exists($header_file)) {
		$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/reserv_header/$data[tab_id]_ipad.jpg";
	}
} else {
	$header_file = findUploadDirectory($app_id) . "/reserv_header/$data[tab_id].jpg";
	if(file_exists($header_file)) {
		$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/reserv_header/$data[tab_id].jpg";
	}
}

//-------------------------------------------------------------------
// Retrieve Locations
//-------------------------------------------------------------------
$locations = get_tab_location($conn, $data["tab_id"]);

//-------------------------------------------------------------------
// Retrieve Open Time
//-------------------------------------------------------------------
$open_times = array();
$open_times_set = get_center_time($conn, $main_info["id"]);
foreach($open_times_set AS $tm) {
	
	if($tm["open_time"] == $tm["close_time"]) {
		$tm_time = "Close";
	} else {
		$tm_time = minutes_to_time(intval($tm["open_time"]), $tm["day"])." ~ ".minutes_to_time(intval($tm["close_time"]), $tm["day"]);
	}
	
	$opt = array(
		"day" => $tm["day"],
		"time" => $tm_time,
	);
	$open_times[] = $opt;
}

//-------------------------------------------------------------------
// Retrieve Main Info
//-------------------------------------------------------------------
$main_info = get_center_information($conn, $app_id, $data["tab_id"]);

//-------------------------------------------------------------------
// Compose feed
//-------------------------------------------------------------------
for($i=0; $i<count($locations); $i++) {
	$locations[$i]["timezone"] = intval($locations[$i]["timezone_value"]);
}

//--------------------------------------------------------
// Payment Gateways
//--------------------------------------------------------
$payment_gateways = array();
$payment_options = get_payment_options($conn, $app_id, $data["tab_id"]);
if($payment_options) {
	foreach($payment_options AS $each_p) {
		if($each_p["is_main"] == "1") {
			$gateway = '';

			switch($each_p["gateway_type"]) {
				case "1": //Paypal
					$gateway = "PAYPAL";
					break;
				case "2": //Google Checkout
					$gateway = "GOOGLECHECKOUT";
					break;	
				case "3": //Authorize.Net
					$gateway = "AUTHORIZE";
					break;
				case "4": //Cash
					$gateway = "CASH";
					break;	
			}

			$payment_gateways[] = array(
				'gateway_type' => $each_p["gateway_type"],
				'gateway_title' => $gateway,
				'gateway_appid' => $each_p["gateway_appid"],
				'gateway_key' => $each_p["gateway_key"],
				'gateway_password' => $each_p["gateway_password"],
				'others' => $each_p["others"],
				'currency' => $each_p["currency"]
			);
		}
	}
}

$item = array(
	"name" => $main_info["center_name"],
	"locations" => $locations,
	"payment_gateways" => $payment_gateways,
	"brief" => $main_info["center_brief"],
	"background" => $image,
	"headerImage" => $header_image,
	"open_time" => $open_times,
	"admin_email" => $main_info["admin_email"],
);
$feed[] = $item;	

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

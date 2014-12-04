<?php
include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$dbresult=mysql_query("SELECT store_name, base_domain, api_key, api_secret FROM mst_storeinfo WHERE enable_store = 1 AND app_id = '$app_id' AND tab_id = '$data[tab_id]'", $conn);
  
$stores = array();
while($store = mysql_fetch_object($dbresult)) {
  $stores[] = $store;
}

$tab = get_app_tab_record($conn, $data[tab_id]);

$name_mapping = array(
	"pgateway_applicationID" => "gateway_appid",
	"pgateway_username" => "gateway_key",
	"pgateway_password" => "gateway_password",
	"pgateway_signature" => "gateway_signature",
	"merchant_id" => "gateway_appid",
	"merchant_key" => "gateway_key",
	"currency" => "currency",
	"currency_sign" => "currency_sign",	
);

$payment_gateway = array(
	array(
		"pgateway_applicationID" => "",
		"pgateway_username" => "",
		"pgateway_password" => "",
		"pgateway_signature" => "",
		"pgateway_name" => "PayPal",
		"merchant_id" => "",
		"merchant_key" => "",
	),/*
	array(
		"pgateway_applicationID" => "",
		"pgateway_username" => "",
		"pgateway_password" => "",
		"pgateway_signature" => "",
		"pgateway_name" => "Google Checkout",
		"merchant_id" => "",
		"merchant_key" => "",
	),*/
);


$sql = "SELECT opt.*, cr.currency_sign FROM mst_options AS opt LEFT JOIN currency AS cr ON opt.currency = cr.currency_code 
		WHERE app_id = '$app_id'AND tab_id = '$data[tab_id]' ORDER BY is_main DESC, gateway_type ASC  ";
$res = mysql_query($sql, $conn);

while($opt = mysql_fetch_array($res)) {
	switch($opt["gateway_type"]) {
		case "1": //Paypal
			foreach($name_mapping AS $key => $value) {
				$payment_gateway[0][$key] = $opt[$value];
			}
				$payment_gateway[0]["merchant_id"] = "";
				$payment_gateway[0]["merchant_key"] = "";
				$payment_gateway[0]["pgateway_name"] = "PayPal";
				
			break;
		/*
		case "2": //Google Checkout
			foreach($name_mapping AS $key => $value) {
				$payment_gateway[0][$key] = $opt[$value];
			}
				$payment_gateway[0]["pgateway_applicationID"] = "";
				$payment_gateway[0]["pgateway_username"] = "";
				$payment_gateway[0]["pgateway_password"] = "";
				$payment_gateway[0]["pgateway_signature"] = "";
				$payment_gateway[0]["pgateway_name"] = "Google Checkout";
			
			break;	 */
	}
	break;
}


$image = '';
$bg_url = '';
/*if ($data["version"] != "4") {
	$button_file = findUploadDirectory($app_id) . "/button.png";
	if (file_exists($button_file)) {
		$image = base64_encode(file_get_contents($button_file));
	}
	
} else {
	// Retrieve button images from new system.
	// But wondering if I really should do so...
}*/

if($data["device"] == "ipad") {
	$bg = get_app_bg($conn, '0', $data[tab_id], '1', $app_id);
	if(isset($bg['name']) &&  ($bg['name']!= '') && file_exists(findUploadDirectory($app_id) . "/ipad/$bg[name]")) {
		$bg_url = $WEB_HOME_URL.'/custom_images/'.$a["code"].'/ipad/'.$bg['name'];
	}
} else {
	//$data["version"] = "4";
	$bg_url = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
}
  
echo '{"response":{"tax":'.doubleval($tab["value2"]).', "Stores":'.json_encode($stores).', "PaymentGateways":'.json_encode($payment_gateway).'}, "background":"'.$bg_url.'", "custombutton":"'.$image.'", "status":{"message":"OK","id":200,"method":"appsomen.com/mobilecartsapp/mobile/get_stores.php"}}'

?>
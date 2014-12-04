<?php

	ob_start("ob_gzhandler");
	$path = pathinfo($_SERVER['PHP_SELF']);
	$action = htmlspecialchars($_GET['action'], ENT_QUOTES);
	//echo $action;
	
	if($_GET['fww'] == "on") {
		session_start("restwidg_id");
		$PAYPAL_INFO = array(
			"checkout" => "paypal/DoExpressCheckoutPayment.php",
			"callback" => "web-widget.php?",
			"rollback" => "web-widget.php?",
			"cart" => "web-widget.php#cart?",
			"cancel" => "web-widget.php?",
		);	
	} else {
		session_start("rest_id");
		$PAYPAL_INFO = array(
			"checkout" => "paypal/DoExpressCheckoutPayment.php",
			"callback" => "?p=order&",
			"rollback" => "?p=ordermenu&",
			"cart" => "?p=cart&",
			"cancel" => "?p=ordermenu&",
		);		
	} 
	

	

$host_split = explode('.',$_SERVER['HTTP_HOST']);
$sandbox = false;

function getCurrentBaseURL() {
	global $_SERVER;
	$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	
	$server_name = $_SERVER["HTTP_X_HOST"];
	if($server_name == "") $server_name = $_SERVER["SERVER_NAME"];
	 
	if ($_SERVER["SERVER_PORT"] != "80") {
	    $pageURL .= $server_name.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
	    $pageURL .= $server_name.$_SERVER["REQUEST_URI"];
	}
	
	$url_patterns = explode("/", $pageURL);
	unset($url_patterns[count($url_patterns)-1]);
	$pageURL = implode("/", $url_patterns);
	
	return $pageURL;
}

$domain = getCurrentBaseURL()."/../";

	$sql = "SELECT * FROM mst_options WHERE tab_id=".$data['tab_id']." AND gateway_type='1' AND is_main='1' LIMIT 1";
	//echo $sql;
	$respay = mysql_query($sql, $conn);
	$qry = mysql_fetch_array($respay);
	$paypalappid	= $qry["gateway_appid"];
	$paypaluser		= $qry["gateway_key"];
	$paypalpass		= $qry["gateway_password"];
	$paypalsignature= $qry["gateway_signature"];
	$api_version = '74.0';

	//echo  $qry["gateway_appid"]."<br>".$qry["gateway_key"]."<br>".$qry["gateway_password"]."<br>".$qry["gateway_signature"];

$application_id = $paypalappid;
$developer_account_email = 'devacc@gmail.com';  // This should be what you use to login to developer.paypal.com
$api_username = $paypaluser;
$api_password = $paypalpass;
$api_signature = $paypalsignature;

?>
<?php
	session_start("rest_id");
	
	include_once("dbconnect.inc"); 
	include_once("app.inc");
	
	include_once("ray_model/common.php");
	include_once("ray_model/ordering.php");
	
	$data = make_data_safe($_REQUEST);
	//print_r ($data);
	include_once("ordering_base.php");

	$action = htmlspecialchars($data['action'], ENT_QUOTES);
	//echo "<br>".$action."<br>".$PASS_PARAMS."<br>";
	//print_r($_REQUEST);
	
	$main_info = get_restaurant_information($conn, $app_id, $data["tab_id"]);
	$main_open_times = get_restaurant_time($conn, $main_info["id"]);
	
	
	$_SESSION['rest_id'] = $main_info["id"];
	$_SESSION['orderstr'] = $data["orderstr"];
	
	
	
	$qryrest = $main_info;
	
	if($data["payment"] == '1')
	{
		header("Location: paypal/SetExpressCheckout.php?".$PASS_PARAMS);
		echo "paypal/SetExpressCheckout.php?".$PASS_PARAMS;
		exit;
		//echo "checkout";
	}
	else if($data["payment"] == '3')
	{
		header("Location: anet_php_sdk/Authorizepayment.php?".$PASS_PARAMS);
		exit;
		//echo "checkout";
	}
	else if($data["payment"] == '4')
	{
		$locs = get_tab_location($conn, $data["loc_id"], 'id');
		//print_r($locs);
		date_default_timezone_set('GMT');
		$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
		$object = json_decode(file_get_contents($jsonObject));
		$ordertimesec = time() + ($locs[0]["timezone_value"] * 60 * 60)+$object->dstOffset;
		$orderhour = date("G",$ordertimesec);
		$ordermin = date("i",$ordertimesec);
		$ordersec = date("s",$ordertimesec);
		$sqlorder = 	"UPDATE orders 
						SET
						order_state = '2',
						checkout_method = '4',
						placed_on =  '".date('Y-m-d H:i:s',$ordertimesec)."'					
						WHERE order_str = '".$_SESSION['orderstr']."' and tab_id=".$data["tab_id"];
		$resorder = mysql_query($sqlorder, $conn);
		header("Location: ?p=order&".$PASS_PARAMS."&action=successful");
		echo "<br>"."?p=order&".$PASS_PARAMS."&action=successful";
		exit;
	}
	else if($data["payment"] == '2')
	{
		header("Location: google/googlecheckout.php?".$PASS_PARAMS);
		//echo "google/cartdemo.php?".$PASS_PARAMS;
		exit;
	}
	else
	{
		if(isset($data['orderstr'])) {
			$sql = "UPDATE orders SET order_state = '2' WHERE order_str = '".$_SESSION['orderstr']."'";
			$res = mysql_query($sql, $conn);
		}
		
		unset($_SESSION['orderstr']);
		unset($_SESSION['totalcharge']);
				
		unset($data['orderstr']);
		include_once("ordering_base_params.php");
		
		header("Location: ?p=ordermenu&".$PASS_PARAMS);
		echo "<br>"."?p=ordermenu&".$PASS_PARAMS;
		exit;
	}
	?>000
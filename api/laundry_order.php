<?php

include "dbconnect.inc";
include "app.inc";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

include_once "common_functions.php";
include_once "app_functions.php";
		
include "ray_model/common.php";
include "ray_model/laundry.php";

$data = make_data_safe($_REQUEST);


$currency = get_currency_list($conn);
$currency_dic = array();
foreach ($currency AS $c) {
	$currency_dic[strtoupper($c["currency_code"])] = $c["currency_sign"]; 
}




$feed = array();
$item = "";


if(($app_id == "0") || ($app_id == "19")) {
	// Seems no app_code specified. Just return error message.
	$item["error"] = "4";
} else if(($data["tk"] == "") || (!check_user_token($conn, $app_id, $data["tk"]))){
	$item["error"] = "9";
} else {
	
	// Fetch app user record based on token value
	$app_user = fetch_app_user($conn, array("app_id="=>$app_id, "user_token="=>"'$data[tk]'"));
	$data["user_id"] = $app_user["id"];
	
	if($data["action"] == "1") { // Retrieve Orders by user
		
		$user_id = $data["user_id"];
		if($app_user["is_admin"] == "1") {
			$user_id = "admin";
		}
		
		$order_results = array();
		if(intval($data["loc_id"]) > 0) {
			$orders = get_laundry_orders($conn, $app_id, $data["tab_id"], $user_id, array("order_type IN (" => "1,2,3,4,5)", "location_id=" => intval($data["loc_id"])));
		} else {
			$orders = get_laundry_orders($conn, $app_id, $data["tab_id"], $user_id, array("order_type IN (" => "1,2,3,4,5)"));
		}
		
		foreach($orders AS $order) {
			$o_r_i = array();
			$indiv = array();
			foreach($order AS $ok => $ov) {
				$indiv[$ok] = $ov;
			}
			
			$o_items = get_laundry_order_items($conn, $order["id"], $app_id, $data["tab_id"], $user_id);
			$dir = findUploadDirectory($app_id) . "/laundry/user_photos/";
			for($i=0; $i<count($o_items); $i++) {
				$photo = $o_items[$i]["photo"];
				if(file_exists($dir . $photo) && !is_dir($dir . $photo)) {
					$o_items[$i]["photo"] = $WEB_HOME_URL.'/custom_images/'.$data[app_code].'/laundry/user_photos/'.$photo.'?width=640';
				}
			}
			
			$o_r_i["info"] = $indiv;
			$o_r_i["items"] = $o_items;
			
			$order_results[] = $o_r_i;
		}
		
		
		$item = array(
			"error" => "0",
			"orders" => $order_results,
		);
		
	} else if($data["action"] == "2") { // Update Order state
		
		if($app_user["is_admin"] == "1") {
			change_order_state($conn, $data["order_id"], $data["type"], $data["revised_amount"], $data["revised_note"]);
		}
		
		$item = array(
			"error" => "0",
		);
		
	} else if($data["action"] == "4") { // Delete Order state
		
		// Check if order_id is valid for this user.
		if(is_valid_order($conn, $app_id, $data["order_id"])) {
			delete_order($conn, $data["order_id"]);
		}
		$item = array(
			"error" => "0",
		);
		
	} else if($data["action"] == "50") { // Retrieve Cart by location
		$order = get_laundry_orders($conn, $app_id, $data["tab_id"], $data["user_id"], array("order_type=" => "0", "location_id=" => $data["loc_id"]), true);

		$orders = get_laundry_order_items($conn, $order[0]["id"], $app_id, $data["tab_id"], $data["user_id"]);
		
		$dir = findUploadDirectory($app_id) . "/laundry/user_photos/";
		for($i=0; $i<count($orders); $i++) {
			$photo = $orders[$i]["photo"];
			if(file_exists($dir . $photo) && !is_dir($dir . $photo)) {
				$orders[$i]["photo"] = $WEB_HOME_URL.'/custom_images/'.$data[app_code].'/laundry/user_photos/'.$photo.'?width=640';
			}
		}
		
		$item = array(
			"error" => "0",
			"cart" => array(
				"id" => $order[0]["id"],
				"created" => strtotime($order[0]["created_on"]),
				"updated" => strtotime($order[0]["updated_on"]),
				"items" => $orders,
			),
		);
	} else if($data["action"] == "51") { // Retrieve Orders
		$orders = get_laundry_orders($conn, $app_id, $data["tab_id"], $data["user_id"], array("order_type=" => "1", "location_id=" => $data["loc_id"]));
		
		$item = array(
			"error" => "0",
			"orders" => $orders,
		);
		
	} else if($data["action"] == "52") { // Submit Order = convert cart to order
		
		move2order($conn, $data["order_id"], $data["loc_id"], $data["pa"], $data["pm"], $data["ti"], $data["dt"], $data["pt"], $data["type"]);
		$item = array(
			"error" => "0",
		);
		
		
	}  else if($data["action"] == "61") { // Retrieve Order Items
		$items = get_laundry_order_items($conn, $data["order_id"], $app_id, $data["tab_id"], $data["user_id"]);
		
		$item = array(
			"error" => "0",
			"items" => $items,
		);
			
	} else if($data["action"] == "62") { // Submit Individual Order Item
		
		$photo_name = "";

		$allowed = array("image/gif", "image/jpg", "image/jpeg", "image/bmp", "image/png",);
		if($_FILES["photo"]["size"] > 0) {
			if(in_array($_FILES["photo"]["type"],$allowed)) {
				
				$dir = findUploadDirectory($app_id) . "/laundry/user_photos/";
				createDirectory($dir);
				$photo_name = uniqid() . ".png";
				
				move_uploaded_file($_FILES["photo"]["tmp_name"], $dir . $photo_name);
			}
		}
		
		$set = "
			item_id = $data[itemId],
			quantity = $data[quantity],
			note = '$data[description]',
			details = '',
			order_id = $data[order_id]
		";
		if($photo_name != "") {
			$set .= ", photo = '$photo_name'";
		}
		
		if(intval($data["id"]) > 0) {
			$sql = "UPDATE laundry_order_item SET " . $set . " WHERE id=$data[id]";
		} else {
			$sql = "INSERT INTO laundry_order_item SET " . $set;
		}
		mysql_query($sql, $conn);
		
		$sql = "UPDATE laundry_order SET udpated_on = '" . gmdate("Y-m-d H:i:s") . "' WHERE id = " . $data["order_id"];
		mysql_query($sql, $conn);
		
		$item["error"] = "0";
		
	} else if($data["action"] == "64") { // Delete Order Item
		if(intval($data["id"]) > 0) {
			$sql = "DELETE FROM laundry_order_item WHERE id=$data[id] AND order_id = $data[order_id]";

			mysql_query($sql, $conn);
			
			$item["error"] = "0";
		} else {
			$item["error"] = "1";
		}
	} else {
		$item["error"] = "44";
	}
	
	
}

$feed[] = $item;



$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

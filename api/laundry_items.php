<?php

include "dbconnect.inc";
include "app.inc";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

include_once "common_functions.php";
		
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
} else {
	
	if($data["action"] == "2") { // Retrieve Laundry Categories

		$cats = get_laundry_categories($conn, $app_id, $data[tab_id], 1);
		
		foreach($cats AS $qry) {
			
			$item = array(
				"id" => $qry["id"],
				"name" => $qry["name"],
				"count" => $qry["item_count"],
			);
			
			$feed[] = $item; 
			
		}
		
	} else if($data["action"] == "1") {  // Retrieve Laundry Items
		
		$items = get_laundry_items($conn, $data["cat_id"]);
		
		foreach($items AS $qry) {
			
			// Check URL
			$album_art = "";
			if(preg_match("/^http:\/\/(.*)$/", $qry["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qry["image_url"])) {
				$album_art = $qry["image_url"];	
			} else {
				$dir = findUploadDirectory($a["id"]) . "/laundry/" . $qry["image_url"];
				if(file_exists($dir) && !is_dir($dir)) {
					$album_art = $WEB_HOME_URL.'/custom_images/'.$a[code].'/laundry/'.$qry["image_url"].'?width=100&height=100';
				}
			}
			
			$item = array(
				"id" => $qry["id"],
				"name" => $qry["name"],
				"note" => $qry["note"],
				"tax_exempted" => $qry["tax_exempted"],
				"cost" => $qry["cost"],
				"currency" => $qry["currency"],
				"currency_sign" => $qry["currency_sign"],
				"thumbnail" => $album_art,
			);
			
			$feed[] = $item; 
			
		}
		
		
	} else if($data["action"] == "0") {  // Retrieve Laundry Item Detail
		
		$qry = get_laundry_item($conn, $data["id"]);
		if($qry == false) {
			$qry = array(
				"id" => "",
				"cat_id" => "0",
				"name" => "",
				"note" => "",
				"cost" => "0",
				"seq" => "0",
				"is_available" => "1",
				"tax_exempted" => "0",
				"image_url" => "",
				"thumbnail" => "",
				"size" => "",
				"options" => "",	
			);	
		} else {
			
			// Check URL
			$album_art = "";
			if(preg_match("/^http:\/\/(.*)$/", $qry["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qry["image_url"])) {
				$album_art = $qry["image_url"];	
			} else {
				$dir = findUploadDirectory($app_id) . "/laundry/" . $qry["image_url"];
				if(file_exists($dir) && !is_dir($dir)) {
					$album_art = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/laundry/'.$qry["image_url"].'?width=100&height=100';
				}
			}
			$qry["thumbnail"] = $album_art; 
			
			//---------------------------------------------------------------------------
			//Options and sizes
			//---------------------------------------------------------------------------
			$opts = get_laundry_item_options($conn, $qry[id]);
			$sz = get_laundry_item_sizes($conn, $qry[id]);
			
			$qry["options"] = $opts;
			$qry["size"] = $sz;
			
		}

		$feed[] = $qry;
		
	}
}


$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";
include_once "ray_model/reserv.php";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$currency = get_currency_list($conn);
$currency_dic = array();
foreach ($currency AS $c) {
	$currency_dic[strtoupper($c["currency_code"])] = $c["currency_sign"]; 
}

//-------------------------------------------------------------------
// Retrieve Main Info
//-------------------------------------------------------------------
$main_info = get_center_information($conn, $app_id, $data["tab_id"]);
//-------------------------------------------------------------------
// Retrieve Open Time
//-------------------------------------------------------------------
$default_off_days = array();
$open_times_set = get_center_time($conn, $main_info["id"]);
foreach($open_times_set AS $tm) {
	
	if($tm["open_time"] == $tm["close_time"]) {
		$default_off_days[] = $tm["day"];
	}
}

//-------------------------------------------------------------------
// Retrieve Service Items
//-------------------------------------------------------------------
$feed = array();
//include("fetch_limit.php");
$sql = "SELECT 
  		i.*, r.currency, cc.currency_sign
 	  FROM service_item AS i
  	  LEFT JOIN service_center AS r ON r.app_id = i.app_id AND r.tab_id = i.tab_id
      LEFT JOIN currency AS cc ON r.currency = cc.currency_code
  	  WHERE i.app_id = '$app_id' AND i.tab_id = '$data[tab_id]' AND NOT (i.is_available = '0') AND i.is_deleted = 0
	  GROUP BY i.id 
  	  ORDER BY i.seq DESC".$SQL_LIMIT;
$res = mysql_query($sql, $conn);

$is_first = true;
$ad_info = array();

while ($qry = mysql_fetch_array($res)) {
	
	if($qry["currency"] == "") $qry["currency"] = "USD";
	$qry["currency_sign"] = (isset($currency_dic[strtoupper($qry["currency"])]))?$currency_dic[strtoupper($qry["currency"])]:"$";
	
	$item = array(
		"id" => $qry["id"],
		"name" => $qry["service_name"],
		"mins" => $qry["duration"],
		"price" => $qry["cost"],
		"currency" => $qry["currency"],
		"currency_sign" => $qry["currency_sign"],
		"note" => $qry["note"],
	);
	
	if($qry["reservfee_type"] == "0") {
		$reserv_fee = round(($qry["reservfee_cost"] * $qry["cost"] / 100), 2);
	} else {
		$reserv_fee = $qry["reservfee_cost"];
	}
	$item["reserv_fee"] = $reserv_fee; 
		 
	// Check URL
	$album_art = "";
	if(preg_match("/^http:\/\/(.*)$/", $qry["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qry["image_url"])) {
		$album_art = $qry["image_url"];	
	} else {
		$dir = findUploadDirectory($a["id"]) . "/reserv/" . $qry["image_url"];
		if(file_exists($dir) && !is_dir($dir)) {
			$album_art = $WEB_HOME_URL.'/custom_images/'.$a[code].'/reserv/'.$qry["image_url"].'?width=100&height=100';
		}
	}
	$item["thumbnail"] = $album_art; 
	
	//---------------------------------------------------------------------------
	//Retrieving Off Days
	//---------------------------------------------------------------------------
	$rest_days = array();
	if($qry["is_available"] == "2") {
		$open_times = get_service_time($conn, $qry["id"], $main_info["id"]);
		foreach($open_times AS $tm) {
			if($tm["open_time"] == $tm["close_time"]) {
				$rest_days[] = $tm["day"];
			}
		}
	} else {
		$rest_days = $default_off_days;
	}
	$item["rest_week"] = $rest_days;
	
	//---------------------------------------------------------------------------
	//Additional process for first element
	//---------------------------------------------------------------------------
	if($is_first) {
		$item = array_merge($item, $ad_info);	
		$is_first = false;
	}
	
	$feed[] = $item;	
}


$json = json_encode($feed);
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>

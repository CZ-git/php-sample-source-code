<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "common_functions.php";
include_once "app_functions.php";


require_once "ray_model/app_connect.php";


$data = make_data_safe($_REQUEST);


if($data["action"] == "1") { // Connect Facebook
	// Should padd following param
	// token, user_id
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$M = new AppConnect($conn);	
		$M->update_token($data["id"], 0, $data["token"], 0, $data["userid"]);
		
		$item = array(
			"error" => "0",
			"action" => "facebook_connect",
		);
		
	} else {
		$item["error"] = "9";		
	}	
} else if($data["action"] == "2") { // Connect Twitter
	// Should padd following param
	// token, user_id
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$M = new AppConnect($conn);	
		$M->update_token($data["id"], 0, $data["tokenKey"], 1, $data["tokenSecret"]);
		
		$item = array(
			"error" => "0",
			"action" => "twitter_connect",
		);
		
	} else {
		$item["error"] = "9";		
	}	
} else if($data["action"] == "41") { // DisConnect Facebook
	// Should padd following param
	// token, user_id
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$M = new AppConnect($conn);	
		$M->update_token($data["id"], 0, "", 0, "");
		
		$item = array(
			"error" => "0",
			"action" => "facebook_disconnect",
		);
		
	} else {
		$item["error"] = "9";		
	}	
} else if($data["action"] == "42") { // DisConnect Twitter
	// Should padd following param
	// token, user_id
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$M = new AppConnect($conn);	
		$M->update_token($data["id"], 0, "", 1, "");
		
		$item = array(
			"error" => "0",
			"action" => "twitter_disconnect",
		);
		
	} else {
		$item["error"] = "9";		
	}	
} else {
	$item["error"] = "44";
	
}


$feed[] = $item;

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);


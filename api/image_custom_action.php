<?php

/* step4.php
   Step4 Page for AppsOmen Build Wizard
   Rudy Aceves
   Aceves Consulting, Dec. 2010
*/

require_once "logging.inc";
include "dbconnect.inc";
include "session.inc";
include "login.inc";
include "wizard.inc";

// CHECK THE APP RECORD
$a = get_app_record($conn, $auth_user["id"]);

if (!is_array($a) || $a["id"] != $auth_user["id"]) {
  log_fatal(__FUNCTION__, "Invalid app record.", $auth_user["id"]);
  header('HTTP/1.1 403 Forbidden');
  exit;
}

$data = make_data_safe($_REQUEST);

if($data["action"] == "4") { // Delete file 
	
	$fn = $data["fn"];
	$fn = explode("_", $fn, 2);
	
	if($fn[0] == "mobile") {
		$file = findUploadDirectory($auth_user[id]) . "/".$fn[1];
		$device_type = "0";
	} else if($fn[0] == "ipad") {
		$file = findUploadDirectory($auth_user[id]) . "/ipad/".$fn[1];
		$device_type = "1";
	}
	
	$sql = "UPDATE images_bg SET name = '' WHERE name = '".$fn[1]."' AND device_type = '".$device_type."' AND app_id = '".$auth_user["id"]."'";
	mysql_query($sql, $conn);
	
	unlink($file);
	
	header('Content-Type: application/json');
	print(json_encode("1"));
	
} else if($data["action"] == "44") { // remove background image 
	
	
	// This is classic way
	$fn = $data["fn"];
	$info = array();
	if($fn == "bgset[]") {
		$info = explode("_", $data["fv"]);
		
		$fn = array();
		$fn[0] = ($info[2] == "0")?"mobile":"ipad";
		
		switch($info[0]) {
			case "0":
				$fn[1] = "tab[]";
			case "1":
				$fn[1] = "loc[]";
			case "51":
				$fn[1] = "more[]";
		}
		$data["fv"] = $info[1];
		
	} else {
		$fn = explode("_", $fn, 2);
	}
	
	if($fn[0] == "mobile") {
		$device_type = "0";
	} else if($fn[0] == "ipad") {
		$device_type = "1";
	}
	
	
	$dir = findUploadDirectory($auth_user[id]);
	if($device_type == "1") {
		$dir .= "/ipad";
	}
				
	switch($fn[1]) {
		case "tab[]":
			$detail_type = "0";
			if($data["fv"] != "0") {
				$tab = get_app_tab_record($conn, $data["fv"]);
				if($tab["app_id"] != $auth_user[id]) {
					print(json_encode("1"));
					exit;
				}
				$old_file = getOldBackgroundPath($tab, $device_type);
			} else {
				$old_file = $dir."/home.jpg";
			}
			break;
		case "loc[]":
			$detail_type = "1";
			$old_file = $dir."/location-".$data["fv"].".jpg";
			break;
		case "cp[]":
			$detail_type = "2";
			break;
		case "qrcp[]":
			$detail_type = "3";
			break;
		case "loyalty[]":
			$detail_type = "7";
			break;
		case "more[]":
			$detail_type = "51";
			break;
	}
	
	$sql = "UPDATE images_bg SET name = '' WHERE detail_id = '".$data["fv"]."' AND detail_type = '".$detail_type."' AND device_type = '".$device_type."' AND app_id = '".$auth_user["id"]."'";
	mysql_query($sql, $conn);
	
	if(file_exists($old_file) && !is_dir($old_file)) {
		unlink($old_file);
	}
	

	header('Content-Type: application/json');
	print(json_encode("1"));
	
} else if($data["action"] == "2") { // Retrieve file mapping info
	$fn = $data["fn"];
	$fn = explode("_", $fn, 2);
	
	// Popup template definition
	$tpl_name = "step4_design_bg_info";
	$page_title = "Image Details";
	$tpl_extra = array();
	$layout = "layout_popup";
	include "layout_popup.inc";

	$tpl->assign("BACKGROUND_IMAGE", "<p>Something went wrong... The background image does not exist on server.</p>");
	$tpl->assign("TAB_LIST", "No tab is assigned with this background.");
	$tpl->assign("LOC_LIST", "No location is assigned with this background.");
	$tpl->assign("SLIDER_LIST", "No slider is assigned with this background.");
	$tpl->assign("CP_LIST", "No Coupon is assigned with this background.");
	$tpl->assign("QRCP_LIST", "No QR Coupon is assigned with this background.");
	$tpl->assign("LOYALTY_LIST", "No Loyalty is assigned with this background.");
	
	// Request processing
	if($fn[0] == "mobile") {
		$file = findUploadDirectory($auth_user[id]) . "/".$fn[1];
		$device_type = "0";
		
		if(file_exists($file)) {
			$tpl->assign("BACKGROUND_IMAGE", '<img width="320" height="411" src="/custom_images/'.$a["code"].'/'.$fn[1].'" />');
		} else {}
		
	} else if($fn[0] == "ipad") {
		$file = findUploadDirectory($auth_user[id]) . "/ipad/".$fn[1];
		$device_type = "1";
		if(file_exists($file)) {
			$tpl->assign("BACKGROUND_IMAGE", '<img width="320" height="411" src="/custom_images/'.$a["code"].'/ipad/'.$fn[1].'" />');
		} else {}
	}
		
	$background_having = array(
		"LocationViewController",
		"EmailPhotoViewController",
		"FanWallViewController",
		"MailingListViewController",
		"MortgageCalculatorViewController",
		"QRViewController",
		"ProductViewController",
		"StatRecorderViewController",
		"TipCalculatorViewController",
		"VoiceRecordingViewController",
		"TellFriendViewController",
		"UserStatsViewController",
		"SocialViewController"
	);

	// ----------------------------------
	// Tab info
	// ----------------------------------
	$tab_infos = "";
	$sql = "SELECT tabs.id, tabs.view_controller, tabs.tab_label, bg.detail_id FROM 
		(SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND device_type = '".$device_type."' AND detail_type = '0') AS bg 
		LEFT JOIN app_tabs AS tabs ON bg.app_id = tabs.app_id AND bg.detail_id = tabs.id";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		
		if(!(in_array($qry["view_controller"], $background_having))) {
			continue;
		}
		
		if($qry["detail_id"] == 0) {
			$tab_infos .= '<li>Home Background</li>';
		} else {
			if($qry["id"]) {
				$tab_infos .= '<li>'.$qry["tab_label"].'</li>';
			}
		}
	}
	if($tab_infos != "") $tpl->assign("TAB_LIST", $tab_infos);
	
	// ----------------------------------
	// Location info
	// ----------------------------------
	$loc_infos = "";
	$sql = "SELECT loc.id, loc.* FROM 
		(SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND device_type = '".$device_type."' AND detail_type = '1') AS bg 
		LEFT JOIN app_locations AS loc ON bg.app_id = loc.app_id AND bg.detail_id = loc.id";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		if($qry["id"]) {
			$loc_infos .= '<li>'.$qry["city"].'</li>';
		}
	}
	if($loc_infos != "") $tpl->assign("LOC_LIST", $loc_infos);
	/*
	// ----------------------------------
	// Coupon info
	// ----------------------------------
	$cp_infos = "";
	$sql = "SELECT cp.id, cp.name AS cp_name FROM 
		(SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND detail_type = '2' AND device_type = '".$device_type."') AS bg 
		LEFT JOIN coupons AS cp  
		ON cp.app_id = bg.app_id AND cp.id = bg.detail_id";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		if($qry["id"]) {
			$cp_infos .= '<li>'.$qry["cp_name"].'</li>';
		}
	}
	if($cp_infos != "") $tpl->assign("CP_LIST", $cp_infos);
	
	// ----------------------------------
	// QR Coupon info
	// ----------------------------------
	$cp_infos = "";
	$sql = "SELECT cp.id, cp.name AS cp_name FROM 
		(SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND detail_type = '3' AND device_type = '".$device_type."') AS bg 
		LEFT JOIN qr_coupons AS cp  
		ON cp.app_id = bg.app_id AND cp.id = bg.detail_id";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		if($qry["id"]) {
			$cp_infos .= '<li>'.$qry["cp_name"].'</li>';
		}
	}
	if($cp_infos != "") $tpl->assign("QRCP_LIST", $cp_infos);
	
	// ----------------------------------
	// Loyalty Coupon info
	// ----------------------------------
	$ly_infos = "";
	$sql = "SELECT cp.id, cp.reward_text AS cp_name FROM 
		(SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND detail_type = '7' AND device_type = '".$device_type."') AS bg 
		LEFT JOIN loyalty AS cp  
		ON cp.app_id = bg.app_id AND cp.id = bg.detail_id";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		if($qry["id"]) {
			$ly_infos .= '<li>'.$qry["cp_name"].'</li>';
		}
	}
	if($ly_infos != "") $tpl->assign("LOYALTY_LIST", $ly_infos);
	*/
	// ----------------------------------
	// Slider info
	// ----------------------------------
	$slider_infos = "";
	$sql = "SELECT * FROM images_bg WHERE app_id= '".$auth_user["id"]."' AND name = '".$fn[1]."' AND device_type = '".$device_type."' AND detail_type = '4'";
	$res = mysql_query($sql, $conn);
	while($qry = mysql_fetch_array($res)) {
		$no = 5 - intval($qry["seq"]) + 1;
		$slider_infos .= '<li>Slider '.$no.'</li>';
	}
	if($tab_infos != "") $tpl->assign("SLIDER_LIST", $slider_infos);
	
	$tpl->parse("MAIN", "main");
	$tpl->parse("LAYOUT", "layout");
	$tpl->FastPrint("LAYOUT");
	
}

?>

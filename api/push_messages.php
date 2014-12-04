<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "common_functions.php";
include_once "app_functions.php";
include_once "../client/wizard.inc";

require_once "ray_model/push_notifications.php";


$data = make_data_safe($_REQUEST);


$data["timezone"] = doubleval($data["timezone"]);

if($data["action"] == "1") { // total, sent message total
	// Should padd following param
	// id, tk
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$sql = "SELECT count(id) AS total FROM push_notifications_plan
        WHERE 
        	app_id = '$data[id]' AND 
        	is_sent = '0' 
        ";
		$res = mysql_query($sql, $conn);
		$total_scheduled = intval(mysql_result($res, 0, 0));
		
		$sql = "SELECT count(id) AS total FROM push_notifications
        WHERE 
        	app_id = '$data[id]'
        ";
		$res = mysql_query($sql, $conn);
		$total_sent = intval(mysql_result($res, 0, 0));
		
		$item = array(
			"error" => "0",
			"scheduled" => "".$total_scheduled,
			"sent" => "".$total_sent,
		);
		
	} else {
		$item["error"] = "9";		
	}
} else if($data["action"] == "2") { // Retrieve Messages
	// Should pass following param
	// id, tk
	
        
        
	if(check_token($conn, $data["id"], $data["tk"])) {
        
        

        		
		$sql = "SELECT count(id) AS total FROM push_notifications_plan
        WHERE 
        	app_id = '$data[id]' AND 
        	is_sent = '0' 
        ";
		$res = mysql_query($sql, $conn);
		$total_scheduled = intval(mysql_result($res, 0, 0));
		
		$sql = "SELECT count(id) AS total FROM push_notifications
        WHERE 
        	app_id = '$data[id]'
        ";
		$res = mysql_query($sql, $conn);
		$total_sent = intval(mysql_result($res, 0, 0));
		
		
		$msg_sch = array();
		$sql = "SELECT * FROM push_notifications_plan
	        WHERE app_id = '$data[id]' AND is_sent = '0' 
	        ORDER BY dueon";
		$res = mysql_query($sql, $conn);
		while ($qry = mysql_fetch_array($res)) {
			
			$old_date = $qry["dueon"]; 
			$qry["dueon"] = tz_convert_by_offset($data["timezone"], $qry["dueon"]);
			
			$sent_info = explode(" ", trim($qry["dueon"]));
		  $msg_sch[] = array(
		  		"id" => $qry["id"],
		  		"msg" => $qry["message"],
		  		"diff" => trim(str_replace("left", "", calcDateDiff(gmdate("Y-m-d H:i:s"), $old_date))),
		  		"sent_date" => $sent_info[0],
		  		"sent_time" => $sent_info[1],
		  );
		}
		
		$msg_sent = array();
		$sql = "SELECT * FROM push_notifications
	        WHERE app_id = '$data[id]'  
	        ORDER BY created DESC";
		$res = mysql_query($sql, $conn);
		while ($qry = mysql_fetch_array($res)) {
			
			$qry["created"] = tz_convert_by_offset($data["timezone"], $qry["created"]);
			
			$sent_info = explode(" ", trim($qry["created"]));
		  $msg_sent[] = array(
		  		"id" => $qry["id"],
		  		"msg" => $qry["message"],
		  		"sent_date" => $sent_info[0],
		  		"sent_time" => $sent_info[1],
		  );
		}
		
		$item = array(
			"error" => "0",
			"scheduled_total" => "".$total_scheduled,
			"sent_total" => "".$total_sent,
			"scheduled" => $msg_sch,
			"sent" => $msg_sent,
		
		);
		
	} else {
		$item["error"] = "9";
	}
	
} else if($data["action"] == "3") { // Send Push Notification


	// Should padd following param
	// id, tk, msg_type{0:NOW,1:SCHEDULE}, dueon, msg, msg_id: optional for edit case - only for msg_type = 1
    
    
	if(check_token($conn, $data["id"], $data["tk"])) {
	    
        
        /*
        print_r($data);
        print_r($_FILES);
        */    
                        
        $idv = explode("_", $data["chk_detail_id"]);
        $depth = intval($idv[1]);
        
        $cat_id = explode("x", $idv[0], 2);
        $detail_id = intval($cat_id[1]);
        $cat_id = intval($cat_id[0]);
        
        $lat = doubleval($data[lat]);
        $long = doubleval($data[long]);
        $radius = doubleval($data[radius]);
        
        $more_info = array(
            "push_id" => 0,
            "push_type" => intval($data["msg_type"]) + 1,
            "loc_type" => $data["loc_type"],
            "rich_type" => $data["rich_type"],
            "rich_url" => $data["rich_url"],
            "rich_tab_id" => $data["link_tab_id_list"],
            "rich_cat_id" => $cat_id,
            "rich_detail_id" => $detail_id,
            "rich_depth" => $depth,
            "latitude" => $lat,
            "longitude" => $long,
            "radius" => $radius,
            "distance_type" => $data["distance_type"],
            "send_to_iphone" => 1, //($data["send_to_iphone"])?"1":"0",
            "send_to_android" => 1, //($data["send_to_android"])?"1":"0",
            "send_to_facebook" => 1, //($data["send_to_facebook"])?"1":"0",
            "send_to_twitter" => 1, //($data["send_to_twitter"])?"1":"0",
            "rich_adv" => 0,
        );
        
        // Need to upload Image File if attached ....
       
        if (is_array($_FILES["image"]) && $_FILES["image"]["size"] != "0") {
            
            $allowed = array("image/gif", "image/jpeg", "image/png", "image/pjpeg", "image/bmp");
            if(in_array($_FILES["image"]["type"],$allowed)) {
                
                $fs_limit = 5 * 1024 * 1024;    // 5 Mbyte of Maximum
                if($fs_limit < $_FILES["image"]["size"]) {
                    // Maximum size exceeded ...    
                }  else {
                    
                    $dir = findUploadDirectory($data[id]) . "/adv";
                    createDirectory($dir);  
                    
                    $filename = findValidName($dir, $_FILES["image"]["name"]);
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], "$dir/$filename")) {
                        
                        $app = get_app_record($conn, $data[id]);
                        $data["adv_value"]["image"] = "/custom_images/" . $app["code"] . "/adv/" . $filename;
                        
                    }
                    
                }
            }
        }
        
        // Need to create adv works first
        if($data["rich_type"] == "3") {
            
            
            require_once "ray_model/adv_pages.php";
            $ADV_M = new AdvPages($conn);
            // fix return problem
            //$data["adv_value"]["content"] = str_replace("\\n", "<br>", $data["adv_value"]["content"]);
            $data["adv_value"]["content"] = nl2br($data["adv_value"]["content"]);

            $more_info["rich_adv"] = $ADV_M->save_work(false, $data[id], $data["adv_value"]["tpl_id"], make_data_raw($data["adv_value"]), $data["adv_value"]["bg"]);
            
        }
        
        $M = new PushNotifications($conn);
        
		if($data["msg_type"] == "1") {
			// Schedule a message
			if ( !isset($data["timezone"]) )
				$data["timezone"] = -6;
			$dueon = tz_convert_by_offset($data["timezone"], $data["dueon"], "Y-m-d H:i:s", true);
			
            $isDST = intval($_POST['isDST']);
            if($isDST) { // substract one hour, to adjust mismatch(one hour diff) between client scheduled time and server time
                $dueon = Date("Y-m-d H:i:s", strtotime($dueon) - 3600);
            }
            
            $id = $M->add_scheduled_message($data[id], gmdate("Y-m-d H:i:s"), $data["msg"], $dueon, $data["msg_id"]);
            $more_info["push_id"] = $id;
            $M->add_message_details($more_info);
			
			$item["error"] = "0";
			
		} else if($data["msg_type"] == "0") {
			// Send a message
			
			$id = $M->add_message($data[id], gmdate("Y-m-d H:i:s"), $data["msg"]);
    
            if(isset($data["loc_type"])) {
                $more_info["push_id"] = $id;
                $M->add_message_details($more_info);
            } else {
                $more_info = false;
            }
            $more_info["date"] = strtotime(gmdate("Y-m-d H:i:s"));
        
            // Send Push Notification Message
            $res = send_push_message_v1($conn, $data[id], stripslashes($_REQUEST["msg"]), $more_info);
            
			$msg = "";
			// Let's build error message
			if($res["iphone"] == "2") {
				$msg_iphone = "The message has been added to the list below, however we cannot send a push notification until your app is in the App Store.";
			} else if($res["iphone"] == "4") {
				$msg_iphone = "Sending message has been failed for iPhone App. Please contact administrator.";				
			} else if($res["iphone"] == "5") {
				$msg_iphone = "Sending message has been failed. Please contact administrator.";				
			}
			
			if($res["android"] == "1") {
				$msg_android = "Sending message has been failed for Android App due to incorrect Android Market URL. Please contact admin to fix the issue.";
			} else if($res["iphone"] == "4") {
				$msg_android = "Sending message has been failed for Android App. Please contact administrator.";				
			}
			
			$msg = $msg_iphone;
			if(($msg != "") && ($msg_android != "")) $msg .= "\n"; 
			$msg .= $msg_android;
			
			if($res["facebook"] == "1") {
				if($msg != "") $msg .= "\n"; 
				$msg .= "Sending message to Facebook failed.";
			}
			
			if($res["twitter"] == "1") {
				if($msg != "") $msg .= "\n"; 
				$msg .= "Sending message to Twitter failed.";
			}
			
			if($msg == "") {
				$item = array(
					"error" => "0",
				);
			} else {
				$item = array(
					"error" => "1",
					"msg" => $msg,
				);
			}
			
		}
		
	} else {
		$item["error"] = "9";
	}
	
} else if($data["action"] == "4") { // Remove Message
	// Should padd following param
	// id, tk, msg_id, msg_type {0:SENT,1:SCHEDULE}
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$tbl_name = "push_notifications_plan";
		if($data["msg_type"] == "0") $tbl_name = "push_notifications";
		
		$sql = "DELETE FROM $tbl_name WHERE app_id = $data[id] AND id = $data[msg_id]";
		mysql_query($sql, $conn);
		
		$item["error"] = "0";
		
	} else {
		$item["error"] = "9";
	}
	
} else if($data["action"] == "5") { // Retrieve Initial Location set...
    // Should padd following param
    // id, tk, msg_id, msg_type {0:SENT,1:SCHEDULE}
    if(check_token($conn, $data["id"], $data["tk"])) {
        
        // New changes...
        // Try to find locations...
        
        $loc_list = array();
        $loc_pointer = false;
        $sql = "SELECT * FROM app_locations WHERE app_id = '" . $data["id"] . "'";
        $res = mysql_query($sql, $conn);
        $locs = array();
        while($row = mysql_fetch_array($res)) {
                
            $loc_list[] = array(
                "lat" => "" . $row["latitude"],
                "long" => "" . $row["longitude"],
                "addr" => "" . compose_address_string('','','','',$row["address_1"],$row["address_2"], $row["city"], $row["state"], $row["zip"]),
            );
        }
        
        if(count($loc_list) > 0) {
            // Should set default postion to...
            $loc_pointer = $loc_list[0];
        } else {
            $loc_pointer = array(
                "lat" => "",
                "long" => "",
            );    
        }
        $loc_pointer["radius"] = "16";
        
        $item["marker"] = $loc_pointer;
        $item["locations"] = $loc_list;
        
    } else {
        $item["error"] = "9";
    }
} else if($data["action"] == "6") { // Retrieve App Tab list
    // Should padd following param
    // id, tk
    if(check_token($conn, $data["id"], $data["tk"])) {
        // New changes...
        
        $detail_able = array(
            "EventsViewController",    // 0
            "EventsManagerViewController",    // 0
            "MenuViewController",    // 1
            "CouponsViewController",    // 0
            "QRCouponViewController",    // 0
            "LoyaltyTabViewController",    // 0
            "WebViewController",    // 0
            "RestaurantBookingViewController",    // 0
            "WuFooViewController",    // 0
            "PDFViewController",    // 0
            "InfoItemsViewController",    // 0
            "InfoSectionViewController",    // 1
            "MusicViewController",    // 0
        );

        $sql = "SELECT * FROM app_tabs WHERE app_id = '".$data["id"]."' AND is_active = 1 ORDER BY seq";
        $res = mysql_query($sql, $conn);
        $tabs = array();
        while($qry = mysql_fetch_array($res)) {
            $tabs[] = array(
                "id" => $qry["id"],
                "label" => $qry["tab_label"],
                "icon" => ($qry["tab_icon_new"]) ? $qry["tab_icon_new"] : "",
                "with_details" => (in_array($qry["view_controller"], $detail_able)?"1":"0"),
            );
        }
        $item['tabs'] = $tabs;
    } else {
        $item["error"] = "9";
    }
} else if($data["action"] == "7") { // Retrieve Tab Detail Info
    // Should padd following param
    // id, tk, tab_id, cat_id
    if(check_token($conn, $data["id"], $data["tk"])) {
        // New changes...
        $detail_able = array(
            "EventsViewController",    // 0
            "EventsManagerViewController",    // 0
            "MenuViewController",    // 1
            "CouponsViewController",    // 0
            "QRCouponViewController",    // 0
            "LoyaltyTabViewController",    // 0
            "WebViewController",    // 0
            "RestaurantBookingViewController",    // 0
            "WuFooViewController",    // 0
            "PDFViewController",    // 0
            "InfoItemsViewController",    // 0
            "InfoSectionViewController",    // 1
            "MusicViewController",    // 0
        );

        // Parameters
        // tab_id
        // cat_id : could be empty

        $tab = get_app_tab_record($conn, $data["tab_id"]);

        $details = array();
        if(in_array($tab["view_controller"], $detail_able)) {
            if(($tab["view_controller"] == "EventsViewController") || ($tab["view_controller"] == "EventsManagerViewController")) {
                $sql = "SELECT * FROM events WHERE app_id = '".$data["id"]."' AND tab_id = '$data[tab_id]' AND isactive = 1";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[name] . "(" . date("m/d/Y", $qry["event_date"]) . ")",
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            } else if($tab["view_controller"] == "MenuViewController") {
                if(intval($data["cat_id"]) > 0) {
                    $sql = "SELECT * FROM menu_items WHERE menu_category_id = '$data[cat_id]' AND is_active = 1";
                    $res = mysql_query($sql, $conn);
                    while ($qry = mysql_fetch_array($res)) {
                        $details[] = array(
                            "id" => $qry[id],
                            "name" => $qry[name] . "($" . $qry["price"] . ")",
                            "depth" => "0",
                            "cat" => "$data[cat_id]",
                        );    
                    }
                } else {
                    $sql = "SELECT * FROM menu_categories WHERE app_id = '".$data["id"]."' AND tab_id = '$data[tab_id]' AND is_active = 1";
                    $res = mysql_query($sql, $conn);
                    while ($qry = mysql_fetch_array($res)) {
                        $details[] = array(
                            "id" => "0",
                            "cat" => $qry[id],
                            "name" => $qry[name],
                            "depth" => "1",
                        );    
                    }    
                }
                
            } else if($tab["view_controller"] == "CouponsViewController") {
                $sql = "SELECT * FROM coupons WHERE app_id = '".$data["id"]."'";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    $dur = "";
                    if($qry["start_date"]) {
                        $dur .= date("m/d/Y", $qry["start_date"]) . " ~ ";
                    }
                    if($qry["end_date"]) {
                        $dur .= date("m/d/Y", $qry["end_date"]);
                    } else {
                        $dur .= "ongoing";
                    }
                    
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[name] . "($dur)",
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            } else if($tab["view_controller"] == "QRCouponViewController") {
                $sql = "SELECT * FROM qr_coupons WHERE app_id = '".$data["id"]."' AND is_active = 1";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    
                    $dur = "";
                    if($qry["start_date"]) {
                        $dur .= date("m/d/Y", $qry["start_date"]) . " ~ ";
                    }
                    if($qry["end_date"]) {
                        $dur .= date("m/d/Y", $qry["end_date"]);
                    } else {
                        $dur .= "ongoing";
                    }
                    
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[name] . "($dur)",
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            } else if($tab["view_controller"] == "LoyaltyTabViewController") {
                $sql = "SELECT * FROM loyalty WHERE app_id = '".$data["id"]."' AND tab_id = '$data[tab_id]'";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[reward_text],
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            } else if(in_array($tab["view_controller"], array("WebViewController", "RestaurantBookingViewController", "WuFooViewController", "PDFViewController"))) {
                $sql = "SELECT * FROM web_views WHERE app_id = '".$data["id"]."' AND tab_id = '$data[tab_id]'";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[name],
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            } else if($tab["view_controller"] == "InfoItemsViewController") {
                
                $sql = "SELECT id FROM info_categories WHERE app_id='".$data["id"]."' AND tab_id = '$data[tab_id]' order by seq ";
                $res = mysql_query($sql, $conn);
                if (mysql_num_rows($res)) {
                  $info_cat_id = mysql_result($res, 0, 0);
                  $sql = "SELECT * FROM info_items WHERE info_category_id = '$info_cat_id' ORDER BY seq ";
                  $res = mysql_query($sql, $conn);
                  while ($qry = mysql_fetch_array($res)) {
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[name],
                        "depth" => "0",
                        "cat" => "0",
                    );    
                  }  
                }
            } else if($tab["view_controller"] == "InfoSectionViewController") {
                if(intval($data["cat_id"]) > 0) {
                    $sql = "SELECT * FROM info_items WHERE info_category_id = '$data[cat_id]' ORDER BY seq ";
                    $res = mysql_query($sql, $conn);
                    while ($qry = mysql_fetch_array($res)) {
                        $details[] = array(
                            "id" => $qry[id],
                            "name" => $qry[name],
                            "depth" => "0",
                            "cat" => "$data[cat_id]",
                        );    
                    }  
                } else {
                    $sql = "SELECT * FROM info_categories WHERE app_id='".$data["id"]."' AND tab_id = '$data[tab_id]' AND is_active = 1 order by seq ";
                    $res = mysql_query($sql, $conn);
                    while ($qry = mysql_fetch_array($res)) {
                        $details[] = array(
                            "id" => "0",
                            "cat" => $qry[id],
                            "name" => $qry[name],
                            "depth" => "1",
                        );    
                    }    
                }
                
            } else if($tab["view_controller"] == "MusicViewController") {
                $sql = "SELECT * FROM music_detail WHERE app_id = '".$data["id"]."' AND tab_id = '$data[tab_id]' AND is_active = 1 AND  NOT (track LIKE '%mzstatic.com%')";
                $res = mysql_query($sql, $conn);
                while ($qry = mysql_fetch_array($res)) {
                    $details[] = array(
                        "id" => $qry[id],
                        "name" => $qry[title],
                        "depth" => "0",
                        "cat" => "0",
                    );    
                }
            }
        }
        $item['details'] = $details;
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
<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";

include_once "common_functions.php";
include_once "app_functions.php";

$data = make_data_safe($_REQUEST);

$feed = array();
$item = "";
if(($app_id == "0") || ($app_id == "19")) {
	// Seems no app_code specified. Just return error message.
	$item["error"] = "4";
} else {
	if($data["action"] == "1") { //Check token
		if(check_user_token($conn, $app_id, $data["tk"])) {
			$item["error"] = "0";
		} else {
			$item["error"] = "9";
		}
	  	
	} else if($data["action"] == "2") { 
		// Login
		$uin = fetch_app_user($conn, array(
				"email" => " = '$data[u]'", 
				"login_password" => " = password('$data[p]')",
				"app_id" => " = '$app_id'",
				));
	  	
	  	if($uin) {
	  		
	  		$item["error"] = "0";
	  		$item["user_info"] = array(
	  				"u" => $uin["email"],
	  				"f" => $uin["contact_first_name"],
	  				"l" => $uin["contact_last_name"],
	  				"c" => $uin["contact_phone"],
	  				"is_admin" => $uin["is_admin"],
	  		);
	  		
	  		// Retrieve billing information
	  		$billing_address = get_billing_address($conn, $uin["id"]);
	  		if($billing_address) {
	  			unset($billing_address["id"]);
	  			unset($billing_address["user_id"]);
	  		} else {
	  			$billing_address = array(
	  					"first_name" => "",
	  					"last_name" => "",
	  					"address1" => "",
	  					"address2" => "",
	  					"country" => "",
	  					"city" => "",
	  					"zipcode" => "",
	  					"state" => "",
	  					"company" => "",
	  					"fax" => "",
	  					"type" => "",
	  					"phone" => "",
	  			);
	  		}
	  		$item["billing_info"] = $billing_address;
	  		
	  		// Generate Token
	  		$tk = gen_user_token($conn, $uin["id"]);
	  		$item["token"] = urlencode($tk);
	  		
	  	} else {
	  		$item["error"] = "4";
	  	}
	  	
	} else if($data["action"] == "52") { // Retrieve user info with ADMIN PERMISSION
		 
		if(!check_user_token($conn, $app_id, $data["tk"])) {
			$item["error"] = "9";
		} else {
			
			// Login
			$uin = fetch_app_user($conn, array(
					"user_token" => " = '" . $data["tk"] . "'",
					"app_id" => " = '$app_id'",
			));
			
			if($uin && ($uin["is_admin"] == "1")) {
				
				$uin = fetch_app_user($conn, array(
						"id" => " = '" . $data["user_id"] . "'",
						"app_id" => " = '$app_id'",
				));
				
			 
				$item["error"] = "0";
				
				$item["user_info"] = array(
					"u" => $uin["email"],
					"f" => $uin["contact_first_name"],
					"l" => $uin["contact_last_name"],
					"c" => $uin["contact_phone"],
		  			"is_admin" => $uin["is_admin"],
		  		);
					
			} else {
				$item["error"] = "4"; // Wrong user... permission denied
			}
			
			
			
		}
		
		
	  	
	} else if($data["action"] == "7") { 
		// Register
		
		$uin = fetch_app_user($conn, array(
				"email" => " = '$data[u]'",
				"app_id" => " = '$app_id'",
		));
		
	  	if($uin) {
	  		// user id duplicated.
	  		$item["error"] = "2";
	  	} else {
	  		
	  		$filledup = true;
	  		$mandatory_fields = array("u", "p", "c", "f", "l");
	  		foreach($mandatory_fields As $mf) {
	  			if(trim($data[$mf]) == "") {
	  				$filledup = false;
	  				break;
	  			}
	  		}

	  		if($filledup) {
	  			$tk = hashSSHA(time("0"));
	  			
	  			$sql = "INSERT INTO app_users SET 
			  			email = '$data[u]',
			  			facebook_id = '',
			  			login_password = password('$data[p]'),
			  			register_type = '0',
			  			contact_phone = '$data[c]',
			  			contact_first_name = '$data[f]',
			  			contact_last_name = '$data[l]',
			  			app_id = '$app_id',
			  			user_token = '$tk'
			  	";
			  	mysql_query($sql, $conn);
			  	
			  	if(mysql_affected_rows($conn) > 0) {
			  		$item["error"] = "0";
			  		
			  		$uin = fetch_app_user($conn, array(
			  				"id" => " = ".mysql_insert_id($conn),
			  				"app_id" => " = '$app_id'",
			  		));
			  		// Now lets make this user logged in
			  		
			  		$item["user_info"] = array(
			  				"u" => $uin["email"],
			  				"f" => $uin["contact_first_name"],
			  				"l" => $uin["contact_last_name"],
			  				"c" => $uin["contact_phone"],
			  				"is_admin" => $uin["is_admin"],
			  		);
			  		$billing_address = array(
			  				"first_name" => "",
			  				"last_name" => "",
			  				"address1" => "",
			  				"address2" => "",
			  				"country" => "",
			  				"city" => "",
			  				"zipcode" => "",
			  				"state" => "",
			  				"company" => "",
			  				"fax" => "",
			  				"type" => "",
			  				"phone" => "",
			  		)
			  		;
			  		$item["billing_info"] = $billing_address;
			  		 
			  		// Generate Token
			  		$tk = gen_user_token($conn, $uin["id"]);
			  		$item["token"] = urlencode($tk);
			  		
			  	}
			  	
	  		} else {
	  			// mandatory fields not filled up
	  			$item["error"] = "3";
	  		} 
		  	
	  	}
	}   else if($data["action"] == "4") { 
		// Password Recovery
		$item["error"] = "0";
		
		$uin = fetch_app_user($conn, array(
				"email" => " = '$data[email]'",
				"app_id" => " = '$app_id'",
		));
		
		if($uin == false) {
			$item["error"] = "4";
		} else {
			
			$reseller = getResellerInfo($conn, $app_id);
			$password = generate_password($conn, 5, 0);
			
			if($data["debug"] == "1") {
				print_r($reseller);
				exit;
			}

			$sql = "UPDATE app_users SET 
			  			login_password = password('$password')
			  		WHERE email = '$data[email]' AND app_id = '$app_id'"; 
			mysql_query($sql, $conn);
			
			
			$mail_body = array(
					"subject" => $data[service].' Password Reset',
					"match_key" => array("{PASSWORD}", "{SERVICE}"),
					"html" =>  array($password, $data[service]),
					"text" => array($password, $data[service]),
					"extra_text" => array(
							"pre" => "",
							"last" => "",
					),
					"extra_html" => array(
							"pre" => "",
							"last" => "",
					),
					"tpl" => "appuser_lost_password",
					"tpl_path" => $reseller["mail_template"],
			);
			send_email($data[email], $mail_body, "$reseller[name] <$reseller[support_email]>");
			
		}
		
	}  else if($data["action"] == "8") {  
		// User Update
		if(!check_user_token($conn, $app_id, $data["tk"])) {
			$item["error"] = "9";
		} else {
			
			
			
			include_once("appuser_action_checktoken.php");
			
			$filledup = true;
			/*
	  		$mandatory_fields = array("u", "p", "c", "f", "l");
	  		foreach($mandatory_fields As $mf) {
	  			if(trim($data[$mf]) == "") {
	  				$filledup = false;
	  				break;
	  			}
	  		}
			*/
		  	if($filledup == false) {
	  			// mandatory fields not filled up
	  			$item["error"] = "3";
	  		} else {
				$sql = "SELECT * FROM app_users 
			          WHERE email = '$data[u]' AND app_id='$app_id' AND NOT (id = '$app_user[id]') ";
			  	$res = mysql_query($sql, $conn);
			  	if(mysql_num_rows($res) == 1) {
			  		// user id duplicated.
			  		$item["error"] = "2";
			  	} else {
		  			$set_sql = "
				  			contact_phone = '$data[c]',
				  			contact_first_name = '$data[f]',
				  			contact_last_name = '$data[l]',
				  			app_id = '$app_id' ";
		  			
		  			if($data[p]) {
		  				$set_sql .= ",login_password = password('$data[p]') ";
		  			} 
		  			$sql = "UPDATE app_users SET $set_sql 
				  			WHERE id = '$app_user[id]'
				  	";
		  			
				  	mysql_query($sql, $conn);
				  	$item["error"] = "0";
				  	
		  		}
		  	}
		  	
		}
		
	} else if($data["action"] == "21") { 
		// Update billing info
		if(!check_user_token($conn, $app_id, $data["tk"])) {
			$item["error"] = "9";
		} else {
			$billing = get_billing_address($conn, $app_user["id"]);
			
			$sql = " SET
						user_id = '$app_user[id]',
						first_name = '$data[f]',
						last_name = '$data[l]',
						address1 = '$data[a1]',
						address2 = '$data[a2]',
						country = '$data[cr]',
						city = '$data[ct]',
						zipcode = '$data[z]',
						state = '$data[s]',
						company = '$data[wc]',
						fax = '$data[fx]',
						type = '0',
						phone = '$data[c]'
				";
			if($billing == false) {
				$sql = "INSERT INTO app_users_address ".$sql;
			} else {
				$sql = "UPDATE app_users_address ".$sql." WHERE id = '$billing[id]'";
			}
			mysql_query($sql, $conn);
			$item["error"] = "0";
		}
		
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

<?php

include_once "dbconnect.inc";

error_reporting(E_ERROR | E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_USER_ERROR);
ini_set('display_errors', '1');

include_once "app.inc";

include_once "common_functions.php";
include_once "app_functions.php";

require_once "ray_model/app_connect.php";


$data = make_data_safe($_REQUEST);


if($data["action"] == "1") { // Token Check
	// Should padd following param
	// id, tk
	if(check_token($conn, $data["id"], $data["tk"])) {
		$item["error"] = "0";
	} else {
		$item["error"] = "1";		
	}
	
} else if($data["action"] == "2") { // Login Action
	// Should padd following param
	// code, id, pw
			
	$a = fetch_app($conn, 
										array(
											"username = " => "'$data[id]'",
											"password = " => "old_password('$data[pw]')",
											//"LOWER(code) = " => "'" . strtolower($data[code]) . "'",
										));
	if($a == false) {
		$item["error"] = "4";	// Failed
	} else if($a["is_active"] != "1") {
		$item["error"] = "2"; // The app is inactive now
	} else {
		
		$M = new AppConnect($conn);	
		// Facebook Connected or not?
		include_once "facebook/facebook_conf.php";
		$is_facebook_connected = false;
		$fb = $M->get_info($a["id"], 0, 0, "first");
		
		try{
			$facebook->setAccessToken($fb["token"]);
			$is_facebook_connected = $facebook->isValid();
		} catch (Exception $e) {}
	
		// Twitter Connected or not?
		include_once "twitter/twitter_conf.php";
		$twitter = $M->get_info($a["id"], 0, 1, "first");
		$is_twitter_connected = false;
		try{
			$t_c = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter["token"], $twitter["etc"]);
			$is_twitter_connected = $t_c->isValid();
		} catch (Exception $e) {}
		
		
		$item = array(
			"error" => "0",
			"tk" => gen_token($conn, $a["id"]),
			"id" => $a["id"],
			"facebook" => $is_facebook_connected?"1":"0",
			"twitter" => $is_twitter_connected?"1":"0",
		);
		
	}
} else if($data["action"] == "3") { // Register
	// Should padd following param
	// email, password
	$is_empty = false;
	$fields = array("email", "password");
	foreach($fields AS $fld) {
		if(trim($data[$fld]) == "") {
			$is_empty = true;
		}
	}
	
	if($is_empty) {
		$item = array("error" => "1"); // Mandatory fields are missing
	} else if (ereg("['<>\"&\\]", $data["password"])) {
    $item = array("error" => "2"); // Password could not contain ", ', <, >, &
  } else {
		
		$e = get_app_record($conn, $data["email"], "owner_email");
	  if ($e["id"]) {
	    $item = array("error" => "3"); // User name duplicated
	  } else {
		  
		$now = time();

		$sql = sprintf("insert into apps (username, password, date_joined, owner_name, name, code, icon_name, owner_telephone, owner_email, signup_ip, is_active, html5_url, isNewbgMode)
                value ('%s', old_password('%s'), '%s', '', '', '', '', '', '%s', '%s', '1', '', '1')",
                  $data["email"], 
                  $data["password"],
                  mysql_real_escape_string($now), 
                  $data["email"],
                  mysql_real_escape_string($_SERVER["REMOTE_ADDR"])
			);
			$res = mysql_query($sql, $conn);
			if(!$res) {
				$item = array("error" => "4"); // Register Failed	
			} else {
				
				// Send an email to new user
				$mail_body = array(
					"subject" => "Welcome to AppsOmen",
					"match_key" => array("{NAME}", "{LINK}"),
					"html" => array(
									$data["email"],
									"",
								),
					"text" => array(
									$data["email"],
									"", 
								),
					"extra_text" => array(
									"pre" => "",
									"last" => "",
								),
					"extra_html" => array(
									"pre" => "",
									"last" => "",
								),
					"tpl" => "welcome",
					"tpl_path" => "/home/bizapps/public_html/emails/", 
				);
				
				send_email($data["email"], $mail_body, "AppsOmen <support@appsomen.com>"); 
				
				// Register to MailChimp
				include_once "MCAPI/MCAPI.class.php";
			  define(MC_KEY, "f41579d757f193b22bf7fe57558603cd-us2");
			  define(MC_LIST_ID, "2ec4a5b3f7");
			  
			  $optin = false; //yes, send optin emails
			  $up_exist = true; // yes, update currently subscribed users
			  $replace_int = false; // no, add interest, don't replace
			  
			  $api = new MCAPI(MC_KEY);
			  $batch = array();
			  $name = explode(" ", $data["email"], 2);
			  $batch[] = array('EMAIL'=>$data["email"], 'FNAME'=>'', 'LNAME'=>'');
			    
			  $vals = $api->listBatchSubscribe(MC_LIST_ID, $batch, $optin, $up_exist, $replace_int);
				
			  
			  $e = get_app_record($conn, $data["email"], "owner_email");
			  $item = array(
			  	"error" => "0"
			 	); // Register Failed
			  
			}
			
	  }
	  
	}
	
} else if($data["action"] == "4") { // Password Recovery
	// Should padd following param
	// code, id(User ID)
	
	$a = fetch_app($conn, 
										array(
											"username = " => "'$data[id]'",
											//"LOWER(code) = " => "'" . strtolower($data[code]) . "'",
										));
	
	if($a == false) {
		$item["error"] = "4";	// Failed
	} else if($a["is_active"] != "1") {
		$item["error"] = "2"; // The app is inactive now
	} else {
		
		$item["error"] = "0"; // Bingo
			
		$password = generate_password($conn, 7, 0);
		
		$sql = sprintf("UPDATE apps SET password = old_password('%s') WHERE id = '%s'", $password, $a['id']);
		$res = mysql_query($sql, $conn);
		
		include_once("../reseller/publish_elem_parent.php");
		
	
		$password = generate_password($conn, 7, 0);
		
	  $sql = sprintf("update apps set password = old_password('%s') where id = '%s'", $password, $a['id']);
	  $res = mysql_query($sql, $conn);
	  
	  $domain = $parent_partner["domain"];
		if(!(preg_match("/^http:\/\/(.*)$/", $domain) || preg_match("/^https:\/\/(.*)$/", $domain))) {
			$domain = "http://".$domain;	
		}
			
		if ($domain == "http://www.appsomen.com" || empty($domain)) { 
			$domain = "http://www.appsomen.com/client";
		} else if (strpos($domain, "appsomen.jp") !== false) {
			$domain = "https://www.appsomen.jp/client";
		}
		$name = $parent_partner["name"];
		
		$mail_body = array(
			"subject" => "$name " . "Password Reset",
			"match_key" => array("{OWNER_NAME}", "{PASSWORD}", "{DOMAIN}", "{NAME}"),
			"html" => array(
							$a["owner_name"],
							$password,
							$domain,
							$name,
						),
			"text" => array(
							$a["owner_name"],
							$password,
							$domain,
							$name, 
						),
			"extra_text" => array(
							"pre" => "",
							"last" => "",
						),
			"extra_html" => array(
							"pre" => "",
							"last" => "",
						),
			"tpl" => "lost_password",
			"tpl_path" => $mail_template_path."emails/", 
		);

		$to_email = 	$data["id"];
		if(validateEmailAddress($a["owner_email"]))	$to_email = 	$a["owner_email"];
		
		send_email($to_email, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>"); 
			
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
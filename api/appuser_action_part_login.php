<?php

  		$item["error"] = "0";

  		$item["user_info"] = array(
  			"u" => $uin["email"],
  			"f" => $uin["contact_first_name"],
  			"l" => $uin["contact_last_name"],
  			"c" => $uin["contact_phone"], 
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
  		 
  		$fetch_rows = 1;
  		while (intval($fetch_rows) > 0) {
  			$item["token"] = hashSSHA($uin["id"]);
	  		$sql = "SELECT * FROM app_users WHERE user_token = '$item[token]' AND app_id = '$app_id'";
	  		$res = mysql_query($sql, $conn);
	  		$fetch_rows = mysql_num_rows($res);
  		}
  		
  		$sql = "UPDATE app_users SET user_token = '$item[token]' WHERE id = '$uin[id]'";
  		$res = mysql_query($sql, $conn);
  		
  		$item["token"] = urlencode($item["token"]);
	  		

?>

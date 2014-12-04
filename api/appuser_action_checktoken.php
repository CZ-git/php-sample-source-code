<?php
	
	$token = $data["tk"];
	$sql = "SELECT * FROM app_users WHERE app_id = '$app_id' AND user_token = '$token'";
  	$res = mysql_query($sql, $conn);
  	
  	$wrong_token = false;
  	
  	if(mysql_num_rows($res) == 1) {
  		$app_user = mysql_fetch_array($res);
  		
  		if(check_hash($app_user["id"], $token)) {
  			// Right token !!!
  		} else {
  			$wrong_token = true;
  		}
  		
  	} else {
  		$wrong_token = true;
  	} 
  		
  	if($wrong_token){
  		$feed[] = array("error" => "9"); // Kick this user log off.
		$json = json_encode($feed);
		header("Content-encoding: gzip");
		echo gzencode($json);
		exit;
  	}
	  		

?>

<?php

include_once "ray_model/form.php";

$forms = get_form_list($conn, array( 
			"f.user_id" => " = '$app_id'", 
			"f.user_type" => " = '1'",
			"f.tab_id" => " = '$data[tab_id]'",
			"f.is_removed" => " = '0'",
			"f.is_active" => " = '1'",
		));

if(count($forms) > 0) {

	
				
	foreach($forms AS $qry) {
		
		$url = "";
		if($PUBLIC_WEB_HOME_URL = "http://www.appsomen.com") {
			$url = $PUBLIC_WEB_HOME_URL . "/client/form_build.php?tk=" . $qry["tk"];
		} else {
			$url = $PUBLIC_WEB_HOME_URL . "/form_build.php?tk=" . $qry["tk"];
		}
		
		$feed[] = array(
			"id" => $qry["id"],
            "title" => $qry["form_title"],
            "url" => $url,
  			"openInSafari" => "NO",
		);	
	}
} else {
	$feed[] = array("title" => "No data");
}

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
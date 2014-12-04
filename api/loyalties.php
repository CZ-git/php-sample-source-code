<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

if(isset($data["tab_id"]) && ($data["tab_id"] != "")) {
	$and = " AND ly.tab_id = '".$data["tab_id"]."'";
}

$t = get_app_tab_record($conn, $data["tab_id"]);

$device_type = "0";
if(($data["device"] == "ipad")) {
	$device_type = "1";
}

$sql = "
	SELECT ly.*, a.code 
	FROM loyalty AS ly
	LEFT JOIN apps AS a ON ly.app_id = a.id 
	WHERE ly.app_id = '".$app_id."'".$and." ORDER BY ly.seq DESC";

$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array(
		"RewardID" => "0",
		"text" => "No Loyalty",
		"rewardItemImage" => "",
		"couponsData" => "",
	);
} else {
	while ($qry = mysql_fetch_array($res)) {
	  
		$thumb_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[img_thumb]";

		$thumbnail = "";
		if( $qry['img_thumb'] && file_exists($thumb_image_file) ) {
			$thumbnail = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[img_thumb]";
		} else {
			$thumb_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[id].jpg";
			if ( file_exists($thumb_image_file) ) {
				$thumbnail = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[id].jpg";
			}
		}

		$header_image = "";
		if ( $data['device'] == 'ipad' ) {
			$header_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[img_header_ipad]";

			if( $qry['img_header_ipad'] && file_exists($header_image_file) ) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[img_header_ipad]";
			} else {
				$header_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[id]_header_ipad.jpg";
				if ( file_exists($header_image_file) ) {
					$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[id]_header_ipad.jpg";
				}
			}		
		} else {
			$header_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[img_header]";

			if( $qry['img_header'] && file_exists($header_image_file) ) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[img_header]";
			} else {
				$header_image_file = findUploadDirectory($app_id) . "/loyalty/$qry[id]_header.jpg";
				if ( file_exists($header_image_file) ) {
					$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/loyalty/$qry[id]_header.jpg";
				}
			}		
		}
		  
		$cp_rtn = array();

		for($i=1; $i<=intval($qry["coupons_count"]); $i++) {
			$cp_rtn[] = array(
				"couponID" => $i,
				"couponCode" => $qry["coupons_data"],
			);
		}
		  
        foreach ( $qry as $k => $v ) {
			$qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
		}

		$feed[] = array(
			"RewardID" => $qry["id"],
			"text" => $qry["reward_text"],
			"headerImage" => $header_image,
			"thumbnail" => $thumbnail,
			"couponsData" => $cp_rtn,	
		);
	}
}

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
$feed[0]["background"] = $bg_image;

$json = json_encode(array("rewardItems" => $feed));
header("Content-encoding: gzip");
echo gzencode($json);

?>
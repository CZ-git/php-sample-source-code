<?php

/**
 * qrcoupon_detail.php
 * This api is called when loading qr coupon detail.
 * @params: app_code, device, tab_id
 * Modified by Daniel 4/24/2014
 */

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

$data = make_data_safe($_GET);

$id = mysql_real_escape_string($data["id"]);
$sql = "SELECT q.*, a.code as app_code
        FROM qr_coupons AS q
        LEFT JOIN apps AS a ON a.id = q.app_id
        where q.id = '$id'";
$res = mysql_query($sql, $conn);

$feed = array();

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array(
		"id" => "0",
		"title" => "No coupon detail found.",
		"start_date" => "",
		"end_date" => "",
		"reusable" => "",
		"checkin_target" => "",
		"checkin_interval" => "",
		"code" => "",
		"image" => "",
		"description" => ""
	);
} else {
	
	while ($qry = mysql_fetch_array($res)) {
	
        foreach ( $qry as $k => $v ) {
			$qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
		}

		$header_image = "";
		if ( $data['device'] == 'ipad' ) {
			$header_file = findUploadDirectory($app_id, "qr_coupons") . "/$qry[img_header_ipad]";
			if( $qry['img_header_ipad'] && file_exists($header_file) ) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[img_header_ipad]?extra=qr_coupons";
			} else {
				$header_file = findUploadDirectory($app_id, "qr_coupons") . "/$qry[id]_header_ipad.jpg";
				if(file_exists($header_file)) {
					$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[id]_header_ipad.jpg?extra=qr_coupons";
				}
			}
		} else {
			$header_file = findUploadDirectory($app_id, "qr_coupons") . "/$qry[img_header]";
			if( $qry['img_header'] && file_exists($header_file) ) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[img_header]?extra=qr_coupons";
			} else {
				$header_file = findUploadDirectory($app_id, "qr_coupons") . "/$qry[id]_header.jpg";
				if(file_exists($header_file)) {
					$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[id]_header.jpg?extra=qr_coupons";
				}
			}
		}
	  
		$startdate = $qry["start_date"];
		$end_date = $qry["end_date"];
        $timezoneDST = $timeZoneLists[$qry['timezone']][2];
        if ( $timezoneDST ) {
            if ( isDSTTime($startdate, $qry['timezone']) ) {
                $startdate += 3600;
            }
            if ( isDSTTime($end_date, $qry['timezone']) ) {
                $end_date += 3600;
            }
        }
        
		$feed[] = array(
			"id" => $qry["id"],
			"title" => $qry["name"],
			"start_date" => $startdate,
			"end_date" => $end_date,
            "timezone_value" => $timeZoneLists[$qry['timezone']][0],
            "timezone_name" => $timeZoneLists[$qry['timezone']][1],
			"reusable" => $qry["reusable"],
			"checkin_target" => $qry["checkin_target"],
			"checkin_interval" => $qry["checkin_interval"],
			"code" => $qry["code"],
			"description" => $qry["description"],
			"image" => $header_image
		);
	}
}
$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
?>
<?php

/**
 * qrcoupons.php
 * This api is called when loading QR Coupon tab.
 * @params: app_code, device, tab_id
 * Modified by Daniel 4/24/2014
 */

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

$data = make_data_safe($_REQUEST);

$more_whr = '';
if(intval($data["tab_id"]) > 0) {
    $more_whr = ' AND tab_id IN (0, ' . intval($data["tab_id"]) . ') ';
}

$sql = "select * from qr_coupons
        where is_active =1
        	AND app_id = '$app_id' " . $more_whr . " 
        order by seq, name";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $feed[] = array("id" => "0",
				  "title" => "No coupons",
				  "start_date" => "", "end_date" => "",
				  "checkin_target" => "");
} else {
    $bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
    
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
        
        $description = str_replace("\r", "", $qry["description"]);
        $description_rows = split("\n", $description);
        if ( count($description_rows) > 3 ) {
            $description = $description_rows[0] . "\n" . $description_rows[1] . "\n" . $description_rows[2];
        }

	    $feed[] = array(
			"id" => $qry["id"],
			"title" => $qry["name"],
            "description" => $description,
            "start_date_format" => date('Y-m-d H:i:s', $startdate),
			"start_date" => $startdate,
			"end_date" => $end_date,
            "timezone_value" => $timeZoneLists[$qry['timezone']][0],
            "timezone_name" => $timeZoneLists[$qry['timezone']][1],
			"reusable" => $qry["reusable"],
			"checkin_target" => $qry["checkin_target"],
			"checkin_interval" => $qry["checkin_interval"],
			"code" => $qry["code"],
			"headerImage" => $header_image
		);
	}
    
    $feed[0]['background'] = $bg_image;
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
<?php

/**
 * coupon_detail.php
 * This api is called when tapping one GPS coupon.
 * @params: app_code, device, tab_id, version
 * Modified by Daniel 4/24/2014
 */

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

$data = make_data_safe($_GET);
$id = mysql_real_escape_string($data["id"]);

$sql = "SELECT c.* FROM coupons AS c WHERE c.id = '$id'";
$res = mysql_query($sql, $conn);

// Get all location for this app
$loc_sql = "select * from app_locations where app_id = '$app_id'";
$loc_res = mysql_query($loc_sql, $conn);  
$all_locations = array();
while ( $loc_qry = mysql_fetch_array($loc_res)) {
    $all_locations[] = array(
        "address_1" => $loc_qry["address_1"],
        "address_2" => $loc_qry["address_2"],
        "city" => $loc_qry["city"],
        "state" => $loc_qry["state"],
        "zip" => $loc_qry["zip"],
        "telephone" => $loc_qry["telephone"],
        "email" => $loc_qry["email"],
        "website" => $loc_qry["website"],
        "latitude" => $loc_qry["latitude"],
        "longitude" => $loc_qry["longitude"]
    );
}

while ($qry = mysql_fetch_array($res)) {

    $header_image = "";
    if ( $data['device'] == 'ipad' ) {
        $header_file = findUploadDirectory($app_id, "coupons") . "/$qry[img_header_ipad]";
        if( $qry['img_header_ipad'] && file_exists($header_file) ) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[img_header_ipad]?extra=coupons";
        } else {
            $header_file = findUploadDirectory($app_id, "coupons") . "/$qry[id]_header_ipad.jpg";
            if(file_exists($header_file)) {
                $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[id]_header_ipad.jpg?extra=coupons";
            }
        }
    } else {
        $header_file = findUploadDirectory($app_id, "coupons") . "/$qry[img_header]";
        if( $qry['img_header'] && file_exists($header_file) ) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[img_header]?extra=coupons";
        } else {
            $header_file = findUploadDirectory($app_id, "coupons") . "/$qry[id]_header.jpg";
            if(file_exists($header_file)) {
                $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[id]_header.jpg?extra=coupons";
            }
        }
    }

    $radius = floatval( $qry['radius'] );
    if ( !$radius )
        $radius = 0.5;
    $radius_unit = $qry['radius_unit'];
    if ( !$radius_unit )
        $radius_unit = 'km';

    foreach ( $qry as $k => $v ) {
        $qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
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
    
    $locations = array();
    if ( $qry["location_id"] > 0 ) {
        $loc_sql = "select * from app_locations where app_id = '$app_id' and id = '$qry[location_id]'";
        $loc_res = mysql_query($loc_sql, $conn);
        
        while ( $loc_qry = mysql_fetch_array($loc_res) ) {
            $locations[] = array(
                "address_1" => $loc_qry["address_1"],
                "address_2" => $loc_qry["address_2"],
                "city" => $loc_qry["city"],
                "state" => $loc_qry["state"],
                "zip" => $loc_qry["zip"],
                "telephone" => $loc_qry["telephone"],
                "email" => $loc_qry["email"],
                "website" => $loc_qry["website"],
                "latitude" => $loc_qry["latitude"],
                "longitude" => $loc_qry["longitude"]            
            );
        }
    }
        
    if( count($locations) == 0 ) {
        $locations = $all_locations;
    }    

    $feed[] = array(
        "id" => $qry["id"],
        "title" => $qry["name"],
        "start_date" => $startdate,
        "end_date" => $end_date,
        "timezone_value" => $timeZoneLists[$qry['timezone']][0],
        "timezone_name" => $timeZoneLists[$qry['timezone']][1],
        "description" => $description,
        "radius" => $radius,
        "radius_unit" => $radius_unit,
        "locations" => $locations,
        "image" => $header_image
    );
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
<?php

/**
 * event_v2ex_detail.php
 * This api is called when loading event on events v2 tab.
 * @params: app_code, device, id
 * Modified by Daniel 4/24/2014
 */

global $timeZoneLists;

include_once("dbconnect.inc");
include_once("app.inc");
include_once("common_functions.php");

$data = make_data_safe($_GET);
$id = mysql_real_escape_string($data["id"]);
$recurring = intval($data["recurring"]);
$header_image = "";
$current_day = date('w');

if ( $recurring == '0' ) {
    $sql = "SELECT * FROM events WHERE id = '$id'";
    $res = mysql_query($sql, $conn);

    if ( $data['device'] == 'ipad' ) {
        $header_file = findUploadDirectory($app_id) . "/events/" . $data['id'] . "/header_ipad.jpg";
        if(file_exists($header_file)) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/events/'.$data[id].'/header_ipad.jpg';
        }
    } else {
        $header_file = findUploadDirectory($app_id) . "/events/" . $data['id'] . "/header.jpg";
        if(file_exists($header_file)) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/events/'.$data[id].'/header.jpg';
        }
    }
} else {
    $sql = "SELECT * FROM recurring_events WHERE id = '$id'";
    $res = mysql_query($sql, $conn);

    if ( $data['device'] == 'ipad' ) {
        $header_file = findUploadDirectory($app_id) . "/recur_events/" . $data['id'] . "/header_ipad.jpg";
        if(file_exists($header_file)) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/recur_events/'.$data[id].'/header_ipad.jpg';
        }
    } else {
        $header_file = findUploadDirectory($app_id) . "/recur_events/" . $data['id'] . "/header.jpg";
        if(file_exists($header_file)) {
            $header_image = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/recur_events/'.$data[id].'/header.jpg';
        }
    }
}

while ($qry = mysql_fetch_array($res)) {

    $from_hour = $qry["from_hour"];
    if ( !$from_hour ) {
        $from_hour = '00';
    } else {
        if ( $from_hour < 10 ) {
            $from_hour = '0' . $from_hour;
        }
    }
    
    $from_min = $qry["from_min"];
    if ( !$from_min ) {
        $from_min = '00';
    } else {
        if ( $from_min < 10 ) {
            $from_min = '0' . $from_min;
        }
    }

    $to_hour= $qry["to_hour"];
    if ( !$to_hour ) {
        $to_hour = '00';
    } else {
        if ( $to_hour < 10 ) {
            $to_hour = '0' . $to_hour;
        }
    }
    
    $to_min = $qry["to_min"];
    if ( !$to_min ) {
        $to_min = '00';
    } else {
        if ( $to_min < 10 ) {
            $to_min = '0' . $to_min;
        }
    }
    
    $fromtime = $from_hour . ':' . $from_min;
    $totime = $to_hour . ':' . $to_min;

    if ( $qry["time_from"] && $qry["time_to"] ) {
        $start_time_unix_stamp = strtotime( date("Y-m-d", $qry["time_from"]) . ' ' . $fromtime . ':00' );
        $end_time_unix_stamp = strtotime( date("Y-m-d", $qry["time_to"]) . ' ' . $totime . ':00' );
    } else {
        $start_time_unix_stamp = strtotime( date("Y-m-d", $qry["event_date"]) . ' ' . $fromtime . ':00' );
        $end_time_unix_stamp = strtotime( date("Y-m-d", $qry["event_date"]) . ' ' . $totime . ':00' );
    }
    
    $title = $qry["name"];
    $title = str_replace( array("&amp;", "&#039;"), array("&", "'"), $title );
  
    // calculate user comments count for event tab2
    $sql = "SELECT count(*) as cnt
            FROM app_user_comments
            WHERE 
                app_id = '$qry[app_id]' AND
                detail_type = '1' AND 
                detail_id = '$qry[id]'";
    $res1 = mysql_query($sql, $conn);
    $commentsCount = intval(mysql_result($res1, 0, 0));
    
    // calculate going count for event tab2
    $sql = "SELECT count(*) as cnt
            FROM app_user_going
            WHERE 
                app_id = '$qry[app_id]' AND
                detail_type = '1' AND 
                    detail_id = '$qry[id]'";
    $res2 = mysql_query($sql, $conn);
    $goingCount = intval(mysql_result($res2, 0, 0));

    // calculate photo count for event tab2
    $sql = "SELECT count(*) as cnt
            FROM app_user_photos
            WHERE 
                app_id = '$qry[app_id]' AND
                detail_type = '1' AND 
                ext != '' AND
                detail_id = '$qry[id]'";
    $res3 = mysql_query($sql, $conn);
    $photoCount = intval(mysql_result($res3, 0, 0));

    // Check recurring event
    if ( $recurring == '1' ) {
        if ( intval($qry["period"]) == 0 ) {
            $from_hr = intval($qry["from_hour"]) * 60 + intval($qry["from_min"]);
            $to_hr = intval($qry["to_hour"]) * 60 + intval($qry["to_min"]);
            if ( $to_hr < $from_hr ) {
                $to_hr += 24*60;
            }
            
            $qry["period"] = intval(($to_hr - $from_hr) / 60);
        }
        $duration = split(':', $qry['period']);
        $hours = intval( abs($duration[0]) ) + intval( abs($duration[1]) ) / 60;
        $endtime = intval($qry['from_hour']) + intval($qry['from_min']) / 60 + $hours;
        
        if ( $qry['day_of_week'] > $current_day ) {
            $offset_days = $qry['day_of_week'] - $current_day;
        } else {
            $offset_days = $qry['day_of_week'] + 7 - $current_day;
        }
        $start_date_unix_stamp = strtotime('+' . $offset_days . ' day');
        $start_time_unix_stamp = strtotime( date("Y-m-d", $start_date_unix_stamp) . ' ' . $fromtime . ':00' );
        
        $end_date_unix_stamp = $start_date_unix_stamp;
        if ( $endtime >= 24 ) {
            $end_date_unix_stamp = $start_date_unix_stamp + intval( $endtime / 24 ) * 24 * 3600;
            $end_time_unix_stamp = strtotime( date("Y-m-d", $end_date_unix_stamp) . ' ' . $totime . ':00' );
        } else {
            $end_time_unix_stamp = strtotime( date("Y-m-d", $end_date_unix_stamp) . ' ' . $totime . ':00' );
        }
    }

    $timezoneDST = $timeZoneLists[$qry['timezone']][2];
    if ( $timezoneDST ) {
        if ( isDSTTime($start_time_unix_stamp, $qry['timezone']) ) {
            $start_time_unix_stamp += 3600;
        }
        if ( isDSTTime($end_time_unix_stamp, $qry['timezone']) ) {
            $end_time_unix_stamp += 3600;
        }
    }

    $feed[] = array(
        "id" => $qry["id"],
        "title" => $title,
        "description" => $qry["description"],
        "header_image" => $header_image,
        "goingcount" => $goingCount,
        "photocount" => $photoCount,
        "commentscount" => $commentsCount,
        "longitude" => $qry["longitude"],
        "latitude" => $qry["latitude"],
        "address1"=>$qry["address_1"],
        "address2"=>$qry["address_2"],
        "city" => $qry["city"],
        "state" => $qry["state"],
        "zip" => $qry["zip"],
        "country" => $qry["country"],
        "start_time" => $start_time_unix_stamp,
        "end_time" => $end_time_unix_stamp
    );
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
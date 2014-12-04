<?php

/**
 * event_detail.php
 * This api is called when loading event on events v1 tab.
 * @params: app_code, device, id, version
 * Modified by Daniel 4/24/2014
 */

global $timeZoneLists;

include_once("dbconnect.inc");
include_once("app.inc");
include_once("buildwiz/class.PageColors.php");
include_once("buildwiz/class.GlobalFont.php");
include_once("common_functions.php");

$data = make_data_safe($_GET);
$id = mysql_real_escape_string($data["id"]);
$recurring = intval($data["recurring"]);
$image = "";
$current_day = date('w');

if ( $recurring == '0' ) {
    $sql = "SELECT * FROM events WHERE id = '$id'";
    $res = mysql_query($sql, $conn);

    $image_file = findUploadDirectory($app_id, "events") . "/$id.jpg";
    if ( file_exists($image_file) ) {
        $image = $WEB_HOME_URL."/custom_images/".$qry["code"]."/$id.jpg?extra=events";
    }
} else {
    $sql = "SELECT * FROM recurring_events WHERE id = '$id'";
$res = mysql_query($sql, $conn);

    $image_file = findUploadDirectory($app_id, "recurring_events") . "/$id.jpg";
    if ( file_exists($image_file) ) {
        $image = $WEB_HOME_URL."/custom_images/".$qry["code"]."/$id.jpg?extra=recurring_events";
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
    
    $title = $qry["name"];
    $title = str_replace( array("&amp;", "&#039;"), array("&", "'"), $title );

    $old_style = "";
    $description = $qry["description"];
    $content_style = getDescriptionStyle($conn, $qry['app_id'], $qry['tab_id']);

    if(strpos($description, "<body") !== false) {
        preg_match_all("/<body([^`]*?)>/", $description, $matches);
        $old_style = $matches[1][0];
        if(!empty($old_style)) {
            // check if background image is set
            if(($bgurl_pos = strpos($old_style, "background-image")) !== false) {
                $bg_url = substr($old_style, $bgurl_pos+16);
                preg_match_all("/url\(([^`]*?)\)/", $bg_url, $bg_matches);
                $bg_url = $bg_matches[0][0];
            }
        }
    }
    
    $pcolor = new PageColors($conn, $qry['app_id']);
    if ($pcolor->Retrieve($qry['tab_id'], 0, $qry["id"])) {
        $text_color = $pcolor->FGColor();
        $bg_color = $pcolor->BGColor();

        $global_tab_font = new GlobalFont($conn, $qry['app_id']);
        $global_font = $global_tab_font->FontFamily();

        if(!empty($bg_url)) {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font.";background-image:".$bg_url.";\"";
        } else {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font."\"";
        }
    }
    
    if ( !empty($old_style) ) {
        $description = str_replace($old_style, $content_style, $description);
    } else {
        $description = str_replace("<body>", "<body ".$content_style.">", $description);
    }
      
      $feed[] = array(
        "id" => $qry["id"],
        "title" => $title,
        "description" => $description,
        "image" => $image,
        "timefrom" => $fromtime,
        "timeto" => $totime,
        "start_date" => $start_time_unix_stamp,
        "end_date" => $end_time_unix_stamp,
        "timezone_value" => $timeZoneLists[$qry['timezone']][0],
        "timezone_name" => $timeZoneLists[$qry['timezone']][1]
      );
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
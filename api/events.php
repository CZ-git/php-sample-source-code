<?php

/**
 * events.php
 * This api is called when loading events tab.
 * @params: app_code, device, tab_id
 * Modified by Daniel 4/24/2014
 */

global $timeZoneLists;

include_once("dbconnect.inc");
include_once("app.inc");
include_once("common_functions.php");

$data = make_data_safe($_GET);

if ( $data["tab_id"] ) {
  $and = "and e.tab_id = '$data[tab_id]' ";
}

$t = get_app_tab_record($conn, $data[tab_id]);

// Order type: "" => byDate, "s" => by Seq
$v1 = $t["value1"];

// Atomatic inactive option: "" => yes, "n" => No
$v2 = $t["value2"];

if($v1 == "s") {
    $ordertype = 'manual';
    $orderby = " ORDER BY e.seq";
} else {
    $ordertype = 'date';
    $orderby = " ORDER BY e.event_date, e.from_hour * 60 + e.from_min";
}

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$normal_recurring_events = array();

// Get regular events
$sql = "
    SELECT e.*, re.isactive AS re_active, re.day_of_week, re.id as recurring_id FROM events AS e
LEFT JOIN recurring_events AS re ON e.recurring_event_id = re.id
WHERE e.app_id = '$app_id' and e.isactive = 1 $and ".$orderby;

$res = mysql_query($sql, $conn);

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
    
    $feed[] = array(
        "id" => $qry["id"],
        "title" => $title,
        "section"  => date("m/Y",$qry["event_date"]),
        "start_time" => $start_time_unix_stamp,
        "end_time" => $end_time_unix_stamp,
        "start_date" => date('Y-m-d H:i:s', $start_time_unix_stamp),
        "end_date" => date('Y-m-d H:i:s', $end_time_unix_stamp),
        "recurring" => "0",
        "recurring_day" => $qry[day_of_week],
        "timezone_value" => $timeZoneLists[$qry['timezone']][0],
        "timezone_name" => $timeZoneLists[$qry['timezone']][1]
    );
    
    $normal_recurring_events[$qry['recurring_id']] = $feed_indv;
}

// Get recurring events
$inx_recurring = 0;

$sql = "
    SELECT * FROM recurring_events
    WHERE app_id = '$app_id' AND tab_id = '$data[tab_id]'
";

$sql .= " ORDER BY isactive, seq";

$res = mysql_query($sql, $conn);

while ($qry = mysql_fetch_array($res)) {
    
    $title = $qry["name"];
    $title = str_replace( array("&amp;", "&#039;"), array("&", "'"), $title );

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

    // Check if passed recurring day of the week
    if ( $qry[day_of_week] > $current_day ) {
        $offset_days = $qry[day_of_week] - $current_day;
    } else {
        $offset_days = $qry[day_of_week] + 7 - $current_day;
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

    $timezoneDST = $timeZoneLists[$qry['timezone']][2];
    if ( $timezoneDST ) {
        if ( isDSTTime($start_time_unix_stamp, $qry['timezone']) ) {
            $start_time_unix_stamp -= 3600;
        }
        if ( isDSTTime($end_time_unix_stamp, $qry['timezone']) ) {
            $end_time_unix_stamp -= 3600;
        }
    }
    
    if ( $normal_recurring_events && $normal_recurring_events[$qry['id']]['start_time'] == $start_time_unix_stamp && $normal_recurring_events[$qry['id']]['end_time'] == $end_time_unix_stamp ) {
        continue;
    }

    $feed[] = array(
        "id" => $qry["id"],
        "title" => $title,
        "section"  => date("m/Y",$start_time_unix_stamp),
        "start_time" => $start_time_unix_stamp,
        "end_time" => $end_time_unix_stamp,
        "start_date" => date('Y-m-d H:i:s', $start_time_unix_stamp),
        "end_date" => date('Y-m-d H:i:s', $end_time_unix_stamp),
        "recurring" => "1",
        "recurring_day" => $qry[day_of_week],
        "timezone_value" => $timeZoneLists[$qry['timezone']][0],
        "timezone_name" => $timeZoneLists[$qry['timezone']][1]
    );
}

$feed[0]["background"] = $bg_image;
$feed[0]["order_type"] = $ordertype;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

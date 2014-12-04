<?php

/* 
Push notifications feed
Compare message date to date of installation on handset?
*/

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

$data = make_data_safe($_REQUEST);
$deviceType = isset($data['device']) ? $data['device'] : "ios";
$deviceToken = $data['dev_token'];
$exist = 0;
// Retrieve current device location
if($deviceType == "android")
    $sql = "SELECT * FROM devices_android WHERE app_id='$app_id' AND devToken='$deviceToken'";
else
$sql = "SELECT * FROM devices WHERE app_id='$app_id' AND devToken='$deviceToken'";
$res = mysql_query($sql, $conn);
if($qry = mysql_fetch_array($res)) {
	$exist = 1;
	$lat = $qry['latitude'];
	$long = $qry['longitude'];
}

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0","../../");

$sql = "SELECT d.*, p.* FROM push_notifications AS p 
        LEFT JOIN push_details AS d ON p.id = d.push_id AND d.push_type = 1  
        WHERE 
            p.app_id = '$app_id'  
        ORDER BY 
            created desc";
            
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 || $exist == 0) {
	$feed[] = array(
		"id" => 0,
		"title" => "No messages",
		"date" => "",
		"rich_type" => 0,
		"rich_url" => "",
		"rich_tab_id" => 0,
		"rich_cat_id" => 0,
		"rich_detail_id" => 0,
		"rich_depth" => 0,
        "active_until" => 0,
        );
} else {
	while ($qry = mysql_fetch_array($res)) {
        // check whether current message is delivered to current device
        if(intval($qry['loc_type']) == 1) {
            if(intval($qry['geofence']) == 0) { // check for circle mode
                $push_latitude = $qry['latitude'];
                $push_longitude = $qry['longitude'];
                $radius = $qry["radius"];

                $distance = (((acos(sin(($push_latitude*pi()/180)) *  sin(($lat*pi()/180))+cos(($push_latitude*pi()/180)) *  cos(($lat*pi()/180)) * cos((($push_longitude-$long) *pi()/180))))*180/pi())*60*1.1515*1.609344);
                if($radius < $distance) { // current user ins't in current push's location. User is out of location
                    continue;
                }
            } else { // check for geofencing
                $paths = explode(' ', $qry["paths"]);
                if ( $paths ) {
                    $points = array();
                    foreach( $paths as $pt ) {
                        $pt = str_replace( array('(', ')'), array('', ''), $pt);
                        $pt = explode(',', $pt);
                        $points[] = $pt;
                    }
                    
                    if ( !isContainCoordinate($points, array($lat, $long)) ) { // case current user location is not in push fence area
                        // in appropriate push for current user, so ignore it.
                        continue;
                    }
                }                
            }
            
        }

	  $msg_type = 0;

	  if($qry["rich_type"] == "3") {
			$qry["rich_url"] = $PUBLIC_WEB_HOME_URL . "/adv_board/" . $data["app_code"] . "/" . $qry["rich_adv"];
		}

      // check whether current message is duration enabled
      if(intval($qry['duration_mode']) == 1) {
          $active_until = strtotime($qry['active_until']);
      } else {
          $active_until = 0;
      }

	  $created = strtotime($qry["created"]) - get_server_timezone_offset();

	  $item = array(
		"id" => $qry["id"],
		"title" => $qry["message"],
		"date" => $created,
		"rich_type" => $qry["rich_type"]?$qry["rich_type"]:0,
        "rich_url" => $qry["rich_url"]?$qry["rich_url"]:"",
        "rich_tab_id" => $qry["rich_tab_id"]?$qry["rich_tab_id"]:0,
        "rich_cat_id" => $qry["rich_cat_id"]?$qry["rich_cat_id"]:0,
        "rich_detail_id" => $qry["rich_detail_id"]?$qry["rich_detail_id"]:0,
        "rich_depth" => $qry["rich_depth"]?$qry["rich_depth"]:0,
        "active_until" => $active_until
	  );
      
      if ( !$qry['rich_type'] )
        $item['rich_tab_id'] = 0;
      
      $feed[] = $item;
	}
}

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

/*$feed[] = array("time" => date("m/d/Y H:i"),
                "message" => "Dummy message");

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);*/

?>
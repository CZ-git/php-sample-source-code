<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$tab_id = $data['tab_id'];
$location_id = $data['location_id'];

if ( !$tab_id && !$location_id ) {
    $feed = array(
        array('error' => 'Invalid tab or location id.')
    );
} else {

    if ( $tab_id ) {
        $sql = "select a.id, a.code from app_tabs t left join apps a on t.app_id = a.id where t.id = '".$tab_id."'";
        $res_tab = mysql_query($sql, $conn);
        $qry_tab = mysql_fetch_array($res_tab);
        $app_id = $qry_tab['id'];
        $app_code = $qry_tab['code'];
    } else if ( $location_id ) {
        $sql = "select a.id, a.code from app_locations l left join apps a on l.app_id = a.id where l.id = '".$location_id."'";
        $res_loc = mysql_query($sql, $conn);
        $qry_loc = mysql_fetch_array($res_loc);
        $app_id = $qry_loc['id'];
        $app_code = $qry_loc['code'];
    }
    
    if ( !$app_id ) {
        $app_id = $data["app_id"];
    }
    
    if ( !$app_id ) {
        $feed = array(
            array('error' => 'Invalid tab id.')
        );        
    } else {
    
        if ( !$app_code )
            $app_code = $data['app_code'];
		if (!is_numeric($location_id) || $location_id == 0){
			$sql = "select l.*, a.name, a.custom_button
                from app_locations l, apps a
                where l.app_id = a.id and l.app_id = '$app_id'  order by seq";
	        	
		}else{
			$sql = "select l.*, a.name, a.custom_button
                from app_locations l, apps a
                where l.app_id = a.id and l.app_id = '$app_id' and l.id=$location_id  order by seq";
		}
        
        $res = mysql_query($sql, $conn);
        $qry = mysql_fetch_array($res);
        
        $location_id = $qry[id];

		// Detect language
		$lang_sql = '
			SELECT w.* FROM week_days w
			LEFT JOIN countries c ON c.lang_weekday = w.id 
			WHERE c.country_code = "' . $qry['country'] . '"
			LIMIT 0, 1
		';
		$lang_res = mysql_query($lang_sql, $conn);
		$lang_weekdays = array();
		if ( mysql_num_rows($lang_res) > 0 ) {
			$lang_weekdays = mysql_fetch_array($lang_res);
		}
        
        $sql = "select * from app_location_opening_times
                where app_location_id = '$qry[id]'
                order by seq";
        $res = mysql_query($sql, $conn);
        while ($o = mysql_fetch_array($res)) {
			$days_of_week = "Sunday";
			if (is_numeric($o["day"])){
				switch($o["day"]) {
					case 1 :
						$days_of_week = "Monday"; break;
					case 2 :
						$days_of_week = "Tuesday"; break;
					case 3 :
						$days_of_week = "Wednesday"; break;
					case 4 :
						$days_of_week = "Thursday"; break;
					case 5 :
						$days_of_week = "Friday"; break;
					case 6 :
						$days_of_week = "Satursday"; break;
					default :
						$days_of_week = "Sunday";
				}
			}else{
				$days_of_week = $o["day"];
			}

			if ( $lang_weekdays ) {
				$days_of_week = $lang_weekdays[strtolower($days_of_week)];
			}

			$opening[] = array(
				"day" => $days_of_week,
				"open_from" => $o["open_from"],
				"open_to" => $o["open_to"]
			);
		}

        $image = "";
        $button = "";
        /*$button_file = findUploadDirectory($app_id) . "/button.png";
        if (file_exists($button_file)) {
	        $button = base64_encode(file_get_contents($button_file));
        }*/

        $current_tab = $tab_id;
        //if ($data["location_id"]) {
	        $data["tab_id"] = $qry[id];
	        $image = getBackgroundImageValue($conn, $app_id, $data, 1, "../../");
        //}
        
        if(($image == "")  && ($current_tab != '') && ($current_tab != '0')){
	        $data["tab_id"] = $current_tab;
	        $image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
        }
        
        if($image == "") {
	        $image_file = findUploadDirectory($app_id) . "/contact.jpg";
	        if (file_exists($image_file)) {
		        $a = get_app_record($conn, $app_id);
		        $image = $WEB_HOME_URL.'/custom_images/'.$a["code"].'/contact.jpg';
	        }
        }

        $img_file1 = findUploadDirectory($app_id) . "/location/$location_id.jpg";

        if (file_exists($img_file1)) {
            $img_url1 = $WEB_HOME_URL."/custom_images/".$app_code."/location/$location_id.jpg";
        }

        $img_file2 = findUploadDirectory($app_id) . "/location/$support@appsomen.com";

        if (file_exists($img_file2)) {
            $img_url2 = $WEB_HOME_URL."/custom_images/".$app_code."/location/$support@appsomen.com";
        }

        foreach ( $qry as $k => $v ) {
			$qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
		}

        $this_item = array(
			"id" => $location_id,
			"name" => $qry["name"],
			"address_1" => $qry["address_1"],
			"address_2" => $qry["address_2"],
			"city" => $qry["city"],
			"state" => $qry["state"],
			"zip" => $qry["zip"],
			"country" => $qry["country"],
			"telephone_display" => $qry["telephone_display"],
			"telephone" => $qry["telephone"],
			"email" => $qry["email"],
			"website" => $qry["website"],
			"latitude" => $qry["latitude"],
			"longitude" => $qry["longitude"],
			"distance_type" => (intval($qry['distance_type']) == 1) ? "mile" : "km",
			"comment" => $qry["comments"],
			"opening_times" => $opening,
			"image" => $image,
			"image1" => $img_url1,
			"image2" => $img_url2,
			"CustomButton" => $button,
        );

        $feed[] = $this_item;
    }
    
}

$json = json_encode($feed);
$json = str_replace(':null', ':""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>
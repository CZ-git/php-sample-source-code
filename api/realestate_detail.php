<?php

/**
 * realestate_detail.php
 * This api is called when tapping a real estate on real estate tab.
 * @params: app_code, tab_id, id
 * Modified by Daniel 4/24/2014
 */

include_once("dbconnect.inc");
include_once("app.inc");

$data = make_data_safe($_REQUEST);

// Get tab background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$sql = "
    SELECT m.*, d.*, a.*, cc.currency_sign FROM realestate_main m 
    LEFT JOIN realestate_detail d ON d.rm_id=m.rm_id 
    LEFT JOIN realestate_additionals a ON a.rm_id=m.rm_id
    LEFT JOIN currency cc ON cc.id=m.currency_id
    WHERE m.rm_id=" . $data['id'];

$res = mysql_query($sql, $conn);
$rs_detail = array();
if ( mysql_num_rows($res) == 0 ) {

    $assign_key_arr = array(
        "ADDRESS_1", 
        "ADDRESS_2", 
        "CITY", 
        "STATE", 
        "ZIP", 
        "COUNTRY",
        "RENT",
        "PRICE", 
        "BEDS", 
        "BATHS", 
        "LATITUDE", 
        "LONGITUDE", 
        "DISTANCE_TYPE", 
        "PRICE_UNIT", 
        "DESCRIPTION", 
        "AGENT", 
        "PHONENUMBER", 
        "EMAIL", 
        "TYPE", 
        "STATUS", 
        "YEARBUILT", 
        "YEARUPDATED", 
        "LOTSIZE", 
        "LOTUNIT", 
        "SQFT", 
        "BASEMENT", 
        "ARCHITECTURAL_STYLE"
    );

    foreach ($assign_key_arr as $val){
        $rs_field = strtolower($val);
        $rs_detail[$rs_field] = "";
    }

    $rs_detail['thumbnail_url'] = "";

    $additionals_fields = array(
        "APPLIANCES",
        "BASEMENT",
        "COOLING_TYPE",
        "FLOOR_COVERING",
        "HEATING_TYPE",
        "HEATING_FUEL",
        "INDOOR_FEATURES",
        "ROOMS",
        "BUILDING_AMENITIES",
        "ARCHITECTURAL_STYLE",
        "EXTERIOR",
        "OUTDOOR_AMENITIES",
        "PARKING",
        "ROOF",
        "VIEW"
    );

    foreach ( $additionals_fields as $field ) {
        $rs_detail[ strtolower($field) ] = '';
    }

}else {
    
    while ($qry = mysql_fetch_array($res, MYSQL_ASSOC)) {

        $rs_detail['address_1'] = $qry['address1'];
        $rs_detail['address_2'] = $qry['address2'];

        $assign_key_arr = array(
            "CITY", 
            "STATE",
            "ZIP",
            "COUNTRY",
            "RENT",
            "PRICE",
            "BEDS",
            "BATHS",
            "LAT",
            "LNG",
            "DISTANCE_TYPE",
            "CURRENCY_SIGN",
            "DESCRIPTION",
            "AGENT",
            "PHONENUMBER",
            "EMAIL",
            "TYPE",
            "STATUS",
            "YEARBUILT",
            "YEARUPDATED",
            "LOTSIZE",
            "LOTUNIT",
            "SQFT",
            "BASEMENT",
            "ARCHITECTURAL_STYLE"
        );

        foreach ($assign_key_arr as $key){
            
            $key = strtolower($key);
            switch ($key) {
                case 'lat':
                    $rs_detail["latitude"] = $qry[$key];
                    break;
                case 'lng':
                    $rs_detail["longitude"] = $qry[$key];
                    break;
                case 'currency_sign':
                    $rs_detail["price_unit"] = $qry[$key];
                    break;
                case 'country':
                    $rs_detail["country"] = is_null($qry[$key]) ? '' : $qry[$key];
                    break;
                case 'rent':
                    $rs_detail["rent"] = intval($qry[$key]);
                    break;
                default:
                    $rs_detail[$key] = $qry[$key];
                    break; 
            }
        }

        $rs_detail['distance_type'] = (intval($qry['distance_type']) == 1) ? "mile" : "km";

        $rs_detail['baths'] = strval( intval($qry['baths']) + floatval($qry['baths_float']) );

        $thumbnail_file = findUploadDirectory($app_id) . "/realestate/" . $qry['rm_id'] . "/thumb.jpg";

        if ( file_exists($thumbnail_file) ) {
            $rs_detail['thumbnail_url'] = $WEB_HOME_URL . '/custom_images/' . $data["app_code"] . '/realestate/' . $qry['rm_id'] . '/thumb.jpg';
        }

        $rs_detail['basement'] = $qry['basement'];
        $rs_detail['architectural_style'] = $qry['architectural_style'];

        $additionals_fields = array(
            "APPLIANCES",
            "COOLING_TYPE",
            "FLOOR_COVERING",
            "HEATING_TYPE",
            "HEATING_FUEL",
            "INDOOR_FEATURES",
            "ROOMS",
            "BUILDING_AMENITIES",
            "EXTERIOR",
            "OUTDOOR_AMENITIES",
            "PARKING",
            "ROOF",
            "VIEW"
        );

        foreach ( $additionals_fields as $field ) {
            $values = unserialize( $qry[ strtolower($field) ] );
            if ( !$values ) {
                $values = '';
            }
            $rs_detail[ strtolower($field) ] = $values;
        }

        // Photos
        $rs_detail['gallery'] = array();
        $rs_detail['gallery']['enable_description'] = $qry['has_image_desc'];
        $photos_sql = "
            SELECT *, unix_timestamp(created) AS timestamp
                FROM app_user_photos
            WHERE app_id = '$app_id' AND tab_id = '$data[tab_id]' AND detail_type = '7' AND detail_id = '" . $qry['rm_id'] . "'
            ORDER BY id desc
        ";
        $photos_res = mysql_query($photos_sql, $conn);

        while ($l = mysql_fetch_array($photos_res)) {
            $image_file = findUploadDirectory($app_id) . "/user_photos/7/" . $qry['rm_id'] . "/photo_$l[id].$l[ext]";
            if(file_exists($image_file)) {
                
                $image = $WEB_HOME_URL . '/custom_images/'.$data['app_code'].'/user_photos/7/'.$qry['rm_id'].'/photo_'.$l[id].'.'.$l[ext];                          
                $photo = array(
                    'description' => $l['caption'],
                    'url' => $image
                );
                $rs_detail['gallery']['photos'][] = $photo;
            }
        }
    }
          
}

$rs_detail["background"] = $bg_image;

$json = json_encode( array($rs_detail) );

header("Content-encoding: gzip");
echo gzencode($json);

?>
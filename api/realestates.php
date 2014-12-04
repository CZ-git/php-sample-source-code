<?php

/**
 * realestates.php
 * This api is called when loading real estate tab.
 * @params: app_code, device, tab_id
 * Modified by Daniel 4/24/2014
*/

include_once("dbconnect.inc");
include_once("app.inc");

$data = make_data_safe($_REQUEST);

// Get tab background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$sql = "
    SELECT m.*, d.type, cc.currency_sign FROM realestate_main m 
    LEFT JOIN realestate_detail d ON d.rm_id=m.rm_id
    LEFT JOIN currency cc ON cc.id=m.currency_id
    WHERE m.app_id=$app_id and m.tab_id=" . $data['tab_id']." order by m.seq asc, m.rm_id asc";

$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
    $real_estate[] = array(
        "id" => 0,
        "address_1" => "",
        "address_2" => "",
        "city" => "",
        "state" => "",
        "zip" => "",
        "country" => "",
        "type" => "",
        "latitude" => "",
        "logitude" => "",
        "rent" => "",
        "distance_type" => "",
        "price" => "",
        "price_unit" => "",
        "baths" => "",
        "beds" => "",
        "thumbnail_url" => "",
    );    
} else {
    while ($qry = mysql_fetch_array($res, MYSQL_ASSOC)) {

        $item = array(
            "id" => $qry["rm_id"],
            "address_1" => $qry["address1"],
            "address_2" => $qry["address2"],
            "city" => $qry["city"],
            "state" => $qry["state"],
            "zip" => $qry["zip"],
            "country" => !is_null($qry["country"]) ? $qry["country"] : '',
            "rent" => intval($qry["rent"]),
            "type" => $qry["type"],
            "latitude" => $qry["lat"],
            "longitude" => $qry["lng"],
            "distance_type" => (intval($qry['distance_type']) == 1) ? "mile" : "km",
            "price" => $qry["price"],
            "price_unit" => $qry["currency_sign"],
            "baths" => strval( intval($qry['baths']) + floatval($qry['baths_float']) ),
            "beds" => $qry["beds"]            
        );

        $thumbnail_file = findUploadDirectory($app_id) . "/realestate/" . $qry['rm_id'] . "/thumb.jpg";

        if ( file_exists($thumbnail_file) ) {
            $item['thumbnail_url'] = $WEB_HOME_URL . '/custom_images/' . $data["app_code"] . '/realestate/' . $qry['rm_id'] . '/thumb.jpg';
        }

        $real_estate[] = $item;

    }
}

$real_estate[0]["background"] = $bg_image;

$json = json_encode( $real_estate );
header("Content-encoding: gzip");
echo gzencode($json);

?>
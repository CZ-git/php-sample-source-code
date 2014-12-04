<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$sql = "select *
        from app_locations
        where app_id = '$app_id'
        order by seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array("title" => "No locations");
} else {
	while ($qry = mysql_fetch_array($res)) {

		$data["tab_id"] = $qry["id"];
		$each_image = getBackgroundImageValue($conn, $app_id, $data, "1", "../../");

        foreach ( $qry as $k => $v ) {
			$qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
		}

  		$feed[] = array("id" => $qry["id"],
                                "address_1" => $qry["address_1"],
                                "address_2" => $qry["address_2"],
                                "city" => $qry["city"],
                                "state" => $qry["state"],
                                "zip" => $qry["zip"],
								"country" => $qry["country"],
                                "telephone" => $qry["telephone"],
                                "email" => $qry["email"],
                                "website" => $qry["website"],
                                "latitude" => $qry["latitude"],
                                "longitude" => $qry["longitude"],
                                "distance_type" => (intval($qry['distance_type']) == 1) ? "mile" : "km",
								"each_background" => $each_image,
			);
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

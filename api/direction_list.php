<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$and = "";
if(isset($data["tab_id"]) && ($data["tab_id"] != "")) {
	$and = " AND tab_id = '".$data["tab_id"]."'";
}

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$sql = "select * from direction
        where app_id = '$app_id' $and
        order by seq DESC";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array("title" => "No locations");
} else {
	while ($qry = mysql_fetch_array($res)) {

  		$feed[] = array(
  			"id" => $qry["id"],
  			"title" => $qry["title"],
            "address_1" => $qry["address_1"],
            "address_2" => $qry["address_2"],
            "city" => $qry["city"],
            "state" => $qry["state"],
            "zip" => $qry["zip"],
            "latitude" => $qry["latitude"],
            "longitude" => $qry["longitude"]);
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

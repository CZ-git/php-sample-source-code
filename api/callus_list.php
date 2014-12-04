<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$and = "";
if(isset($data["tab_id"]) && ($data["tab_id"] != "")) {
	$and = " AND tab_id = '".$data["tab_id"]."'";
}

include_once("fetch_limit.php");
$sql = "select * from callus
        where app_id = '$app_id' $and
        order by seq DESC".$SQL_LIMIT;
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array("title" => "No locations");
} else {
	while ($qry = mysql_fetch_array($res)) {

  		$feed[] = array(
  			"id" => $qry["id"],
  			"title" => $qry["title"],
            "phone" => $qry["phone"],
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

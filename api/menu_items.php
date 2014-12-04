<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$id = mysql_real_escape_string($data["id"]);

/* TODO: VALIDATE $id AGAINST $app_id */

$sql = "select *
        from menu_items
        where menu_category_id = '$id'
        and is_active > 0
        order by seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $feed[] = array("title" => "No items");
} else {
	while ($qry = mysql_fetch_array($res)) {

  	$feed[] = array("id" => $qry["id"],
                  	"title" => $qry["name"],
                  	"price" => $qry["price"]);
	}
}

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

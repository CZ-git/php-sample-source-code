<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$and = "";
if($data[tab_id]) {
	$and = " AND ((m.tab_id = '$data[tab_id]') OR (m.tab_id = 0))";
} else {
	//$and = " AND tab_id > 0";
}

$sql = "SELECT m.*
        FROM menu_categories AS m
        LEFT JOIN app_tabs AS t ON m.tab_id = t.id 
        where m.app_id = '$app_id' $and and m.is_active > 0 AND ((t.view_controller = 'MenuViewController' AND t.is_active > 0) OR (m.tab_id = 0))
        order by m.seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $feed[] = array("title" => "No categories");
} else {
	while ($qry = mysql_fetch_array($res)) {

  	$feed[] = array("id" => $qry["id"],
                  	"section" => $qry["section"],
                  	"title" => $qry["name"]);
	}
}

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

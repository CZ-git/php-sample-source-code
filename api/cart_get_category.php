<?php
include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);

include("fetch_limit.php");
$dbresult=mysql_query("SELECT catid, catname FROM mst_category WHERE app_id = '$app_id' AND tab_id = '$data[tab_id]'".$SQL_LIMIT, $conn);
  
$categories = array();
while($category = mysql_fetch_object($dbresult)) {
  $categories[] = $category;
}

echo '{"response":{"Catagories":'.json_encode($categories).'},"status":{"message":"OK","id":200,"method":"appsomen.com/mobilecartsapp/mobile/get_category.php"}}';

?>


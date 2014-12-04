<?php

include_once "dbconnect.inc";
include_once "app.inc";

// TODO: Should be reading interest from DB!
/*$sql = "select *
        from app_locations, apps
        where app_locations.app_id = apps.id and app_id = '$app_id'";
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);*/

$data = make_data_safe($_GET);

$sql = "select * from app_tabs
        where app_id = '$app_id'
        and view_controller = 'RepaymentViewController'";
$res = mysql_query($sql, $conn);

if (mysql_num_rows($res))
  $t = mysql_fetch_array($res);

$this_item = array(
				"interest" => ($t["value1"])?$t["value1"]:'',
				"readonly" => ($t["value2"])?$t["value2"]:'',
			);

$image = "";
$button = "";

/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file)) 
  $button = base64_encode(file_get_contents($button_file));*/

$image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

$this_item["CustomButton"] = $button;
$this_item["image"] = $image;



$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

if ($data["tab_id"])
  $and = "and id = '$data[tab_id]' ";

$sql = "select *
        from app_tabs
        where app_id = '$app_id' $and
        and view_controller = 'MailingListViewController'
        order by id";
$res = mysql_query($sql, $conn);
$tab = mysql_fetch_array($res);

$app_id = $tab['app_id'];

$app_sql = "select code 
        from apps
        where id = '$app_id'";
$app_res = mysql_query($app_sql, $conn);
$app_code = mysql_result($app_res, 0, 0);

$image = "";
$button = "";

/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file)) 
  $button = base64_encode(file_get_contents($button_file));*/

$image = getBackgroundImageValue($conn, $app_id, $data, "0","../../");

$sql = "select * from mailing_list_categories
        where app_id = '$app_id'
        order by seq, name";
$res = mysql_query($sql, $conn);
if (mysql_num_rows($res)) {
  while ($qry = mysql_fetch_array($res))
    $categories[] = array($qry[id], $qry[name]);
}
else {
  $categories = array();
}

$logo_img_file = findUploadDirectory($app_id) . "/mailing_list_logo.jpg";
if (file_exists($logo_img_file)) {
    global $WEB_ROOT_PATH;
    $logo_img = 'http://www.appsomen.com/custom_images/' . $app_code . '/mailing_list_logo.jpg';
} else {
    $logo_img = '';    
}

$tab["value1"] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $tab["value1"] );

$this_item = array(
    "description" => $tab["value1"]."",
    "image" => $image,
    "logo_image" => $logo_img,
    "categories" => $categories,
    "CustomButton" => $button
);

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
die();

?>
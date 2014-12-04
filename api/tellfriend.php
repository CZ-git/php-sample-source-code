<?php
//ini_set ("display_errors", "1"); // At the top of the page for page errors.
//error_reporting(E_ALL); // At the top of the page for page errors 
include_once "dbconnect.inc";
include_once "app.inc";


$data = make_data_safe($_REQUEST);
$a = get_app_record($conn, $app_id);

$this_item = array(
	
	"facebook_api_key" => "124661487570397",
	"twitter_key" => "w7v7fdJfJic6Xk8OmLSM0g",
	"twitter_secret" => "ymoXT5sdZlhppJH0nI3dNl48JubUaQoLtU3w6mAk",
	"name" => $a["name"],
	"app_store_url" => ($a["app_store_url"]=="")?"http://www.appstore.com":$a["app_store_url"],
);

// Retrieve background image
$mobile_bg = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
$this_item["image"] = $mobile_bg;
	
// Load custom button
$design = get_app_design($conn, $app_id);
if (!is_array($design) || $design["app_id"] != $app_id) {
  // no setting found for new design, then just lets go for classic design part.
  // Need to find first template
  $tpls = get_app_design_templates($conn, "11", "id");
  $design = $tpls;
} 

$sql = "SELECT * FROM template_detail WHERE id = '$design[detail_id]'";
$res = mysql_query($sql, $conn);
$details = mysql_fetch_array($res);

$button_file = findUploadDirectory($app_id) . "/templates/inner_buttons/".$details["inner_button"];
if (file_exists($button_file) && !is_dir($button_file)) {
	//$image = base64_encode(file_get_contents($button_file));
	$image = $WEB_HOME_URL.'/custom_images/'.$a["code"]."/templates/inner_buttons/".$details["inner_button"];
	$this_item["CustomButton"] = $image;
} else {
	$this_item["CustomButton"] = "";		
}

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

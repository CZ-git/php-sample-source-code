<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

if ($data[tab_id])
  $and = "and t.id = '$data[tab_id]' ";

$sql = "select t.*, 
				p.even_row_color, p.odd_row_color, p.odd_row_text_color, p.even_row_text_color
        from app_tabs as t
        left join apps as p on t.app_id = p.id
        where t.app_id = '$app_id'
        $and
        and t.view_controller = 'QRViewController'
        order by t.id";
        
$res = mysql_query($sql, $conn);
$tab = mysql_fetch_array($res);

$image = "";
$button = "";

/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file)) 
  $button = base64_encode(file_get_contents($button_file));*/

$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

$lastUpdated = $tab["last_updated"] - get_server_timezone_offset();

$this_item = array(
	"tab_id" => $data[tab_id],
	"TabLabel" => $tab["tab_label"],
	"TabImage" => $tab["tab_icon"],
	"ViewController" => $tab["view_controller"],
	"NavigationController" => $tab["navigation_controller"] ? "YES" : "NO",
	"LastUpdated" => $lastUpdated,
	"EvenRowColor" => $tab["even_row_color"],
	"OddRowColor" => $tab["odd_row_color"],
	"EvenRowTextColor" => $tab["even_row_text_color"],
	"OddRowTextColor" => $tab["odd_row_text_color"],
	"CustomButton" => $button,
	"image" => $image
);

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

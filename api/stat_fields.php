<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$sql = "select *
        from stat_fields
        where app_id = '$app_id' 
        and tab_id = '$data[tab_id]'
        order by seq, name";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $fields[] = "No fields"; // $sql $_SERVER[HTTP_USER_AGENT] $_SERVER[QUERY_STRING] $_SERVER[REQUEST_URI]";
} else {
	while ($qry = mysql_fetch_array($res)) {

  	//foreach ($qry as $key => $val)
    	// $qry[$key] = preg_replace('/[^(\x20-\x7F)]*/','', $val);
    //    $qry[$key] = preg_replace('/[(\x0-\x1F)]*/','', $val);
    
        foreach ( $qry as $k => $v ) {
            $qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
        }

  	    $fields[] = $qry["name"];
	}
}


$image = "";
$button = "";

/*$button_file = findUploadDirectory($app_id) . "/button.png";
if (file_exists($button_file))
  $button = base64_encode(file_get_contents($button_file));*/
		
$image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

$sql = "select * from app_tabs
        where app_id = '$app_id' 
        and view_controller = 'StatRecorderViewController'
        and id = '$data[tab_id]'";
$res = mysql_query($sql, $conn);
$t = mysql_fetch_array($res);

if (!$t["value1"])
  $t["value1"] = "xxx";

if (!$t["value2"])
  $t["value2"] = "Here are your stats";

$feed[] = array("fields" => $fields,
                "CustomButton" => $button,
                "image" => $image,
                "email" => $t["value1"],
                "message" => $t["value2"]);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

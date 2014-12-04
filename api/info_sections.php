<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0","../../");

if ($data["tab_id"])
  $and = "and tab_id = '$data[tab_id]' ";

$sql = "select *
        from info_categories
        where app_id = '$app_id' $and
        and is_active > 0
        order by seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
  $feed[] = array("title" => "No categories");
} else {
	while ($qry = mysql_fetch_array($res)) {

		$thumbnail = "";
		$dir = findUploadDirectory($app_id)."/info";
		if(file_exists($dir."/cat_".$qry[id].".png")) {
			$thumbnail = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/info/cat_'.$qry["id"].'.png';
		}

  	$feed[] = array("id" => $qry["id"],
                  	"section" => $qry["section"],
                  	"title" => $qry["name"],
					"thumbnail" => $thumbnail,
		);
	}
}

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

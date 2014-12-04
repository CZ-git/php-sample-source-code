<?php

// Get tab background
$bg_image = getBackgroundImageValue($conn, $app_id, $data,"0","../../");

if ($data["tab_id"])
  $and = "and tab_id = '$data[tab_id]' ";

$sql = "
	SELECT * FROM web_views
	WHERE app_id = '$app_id' AND tab_id = '$data[tab_id]'
	ORDER BY seq
";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
	$feed[] = array("title" => "No categories");
} else {
	while ( $qry = mysql_fetch_array($res) ) {

		$dir = findUploadDirectory($app_id, "webview");
		if ( file_exists($dir."/".$qry[id].".jpg") ) {
			$thumb_image = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/'.$qry[id].'.jpg?extra=webview';
		} else {
			$thumb_image = "";
		}

		if ( (strpos($qry["url"], "http://") === false) && (strpos($qry["url"], "https://") === false) ) {
			$qry["url"] = "http://" . $qry["url"];
		}

		if ( ereg("youtube.com/watch\?v=([a-zA-Z0-9\-_]+)", $qry["url"], $regs) ) {
			$qry["url"] = "youtube:$regs[1]";
		}

		if ( ereg("youtube.com/v/([a-zA-Z0-9\-_]+)", $qry["url"], $regs) ) {
			$qry["url"] = "youtube:$regs[1]";
		}

		if ( ereg("youtube:([a-zA-Z0-9\-_]+)", $qry["url"], $regs) ) {
			$qry["url"] = "http://www.appsomen.com/iphone/youtube.php?v=$regs[1]";
		}

		$isd = "NO";
		if ( $qry["is_donate"] == "1")
			$isd = "YES";
            
        $qry["url"] = str_replace('&amp;', '&', $qry["url"]);

		$feed[] = array(
			"id" => $qry["id"],
			"title" => $qry["name"],
			"url" => $qry["url"],
			"openInSafari" => $isd,
			"thumbnail" => $thumb_image,
		);

	}
}

$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
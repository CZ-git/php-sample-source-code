<?php

include_once "dbconnect.inc";
include_once "app.inc";
include_once "buildwiz/class.PageColors.php";
include_once "buildwiz/class.GlobalFont.php";

include_once "common_functions.php";

$data = make_data_safe($_GET);
$id = mysql_real_escape_string($data["id"]);

$sql = "SELECT i.*, a.code, a.id AS app_id , ic.tab_id 
        FROM info_items AS i
        LEFT JOIN info_categories AS ic ON i.info_category_id = ic.id
        LEFT JOIN apps AS a ON a.id = ic.app_id 
        WHERE i.id = '$id'";
$res = mysql_query($sql, $conn);

while ($qry = mysql_fetch_array($res)) {

	//foreach ($qry as $key => $val)
    // $qry[$key] = preg_replace('/[^(\x20-\x7F)]*/','', $val);
    //$qry[$key] = preg_replace('/[(\x0-\x1F)]*/','', $val);
    $data['tab_id'] = $qry['tab_id'];
    $bg_image = getBackgroundImageValue($conn, $app_id, $data,"0", "../../");

	$image = "";

	$image_file = findUploadDirectory($app_id, "tier") . "/$qry[img_thumb]";
	if( $qry['img_thumb'] && file_exists($image_file) ) {
		$image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[img_thumb]?extra=tier";
	} else {
		$image_file = findUploadDirectory($app_id, "tier") . "/$qry[id].jpg";
		if (file_exists($image_file)) {
			$image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/$qry[id].jpg?extra=tier";
		}
	}
	
	$header_image = "";
	if ( $data['device'] == 'ipad' ) {
		$header_file = findUploadDirectory($app_id) . "/info/$qry[id]/$qry[img_header_ipad]";
		if( $qry['img_header_ipad'] && file_exists($header_file) ) {
			$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/info/$qry[id]/$qry[img_header_ipad]";
		} else {
			$header_file = findUploadDirectory($app_id) . "/info/$qry[id]/header_ipad.png";
			if(file_exists($header_file)) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/info/$qry[id]/header_ipad.png";
			}
		}
	} else {
		$header_file = findUploadDirectory($app_id) . "/info/$qry[id]/$qry[img_header]";
		if( $qry['img_header'] && file_exists($header_file) ) {
			$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/info/$qry[id]/$qry[img_header]";
		} else {
			$header_file = findUploadDirectory($app_id) . "/info/$qry[id]/header.png";
			if(file_exists($header_file)) {
				$header_image = $WEB_HOME_URL."/custom_images/".$data['app_code']."/info/$qry[id]/header.png";
			}
		}
	}
    
	if(false || ($data["device"] == "android")) $qry["description"] = add_a_link_for_iframe($qry["description"]);
	
    $old_style = "";
    $description = $qry["description"];
    $content_style = getDescriptionStyle($conn, $app_id, $data['tab_id']);
    
    if(strpos($description, "<body") !== false) {
        preg_match_all("/<body([^`]*?)>/", $description, $matches);
        $old_style = $matches[1][0];
        if(!empty($old_style)) {
            // check if background image is set
            if(($bgurl_pos = strpos($old_style, "background-image")) !== false) {
                $bg_url = substr($old_style, $bgurl_pos+16);
                preg_match_all("/url\(([^`]*?)\)/", $bg_url, $bg_matches);
                $bg_url = $bg_matches[0][0];
            }
        }
    }

    $pcolor = new PageColors($conn, $app_id);
    if ($pcolor->Retrieve($data['tab_id'], $qry['info_category_id'], $qry["id"])) {
        $text_color = $pcolor->FGColor();
        $bg_color = $pcolor->BGColor();

        $global_tab_font = new GlobalFont($conn, $app_id);
        $global_font = $global_tab_font->FontFamily();

        if(!empty($bg_url)) {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font.";background-image:".$bg_url.";\"";
        } else {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font."\"";
        }
    }

    if(!empty($old_style))
        $description = str_replace($old_style, $content_style, $description);
    else
        $description = str_replace("<body>", "<body ".$content_style.">", $description);

  	$feed[] = array("id" => $qry["id"],
                  "title" => $qry["name"],
                  "description" => $description,
                  "background" => $bg_image,
  				  "image" => $image,
  				  "header_image" => $header_image,
				  "isNewDesign" => ($qry["type"])?"1":"0",
  	);
}


if($data["debug"] == "1") {
	echo $feed[0]["description"];
	exit;
}


$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
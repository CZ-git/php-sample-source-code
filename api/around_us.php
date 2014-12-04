<?php
include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

$image = "";
$button = "";

// Retrieve background
$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");

//$image = $bg_image;

if($a["isNewDesign"] == "1") { 
	//-------------------------------------------------------------------
	// If this is for new Design, then get the data from the database
	//-------------------------------------------------------------------
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
		$button = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/templates/inner_buttons/".$details["inner_button"];
	}
} else {
	//------------------------------------------------------------------------------------
	// If this is not for new Design, then get the button from the file system directly
	//------------------------------------------------------------------------------------
	$button_file = findUploadDirectory($app_id) . "/button.png";
	if (file_exists($button_file)) {
		$button = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/button.png";
	}
}

$sql = "select * from poi_categories 
        where app_id = '$app_id'
        and tab_id = '$data[tab_id]'";
$res = mysql_query($sql, $conn);

if (($res)&&(mysql_num_rows($res) > 0)) {
	$cat = mysql_fetch_array($res);

	foreach ( $cat as $k => $v ) {
		$cat[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
	}
}
else {	
  $cat = array("red_title" => "Category1",
               "green_title" => "Category2",
               "purple_title" => "Category3",
  			   "red_color" => "FF0000",
               "green_color" => "00FF00",
               "purple_color" => "800080");
}

/*
// Now should set color to default one
$cat['red_color'] = "FF0000";
$cat['green_color'] = "00FF00";
$cat['purple_color'] = "800080";
*/

$mapping = array("red_title" => "0", "green_title" => "1", "purple_title" => "2");
foreach($mapping AS $key=>$v) {
	$col = $cat[str_replace('_title','_color',$key)];
	$rgb = str_split($col, 2);
	$forcol = (((0.213*hexdec($rgb[0]) + 0.715*hexdec($rgb[1]) + 0.072*hexdec($rgb[2]))/255) < 0.5) ? 'FFFFFF':'000000';
	$cat[str_replace('_title','_color',$key)."_text"] = $forcol; 
}

$sql = "SELECT * FROM pois
        WHERE app_id = '$app_id'
        AND tab_id = '$data[tab_id]'
		ORDER BY seq DESC";
$res = mysql_query($sql,$conn);
while ($qry = mysql_fetch_array($res)) {
	switch($qry["color"]) {
		case "0":
			$bgcol = $cat['red_color'];
			break;
		case "1":
			$bgcol = $cat['green_color'];
			break;
		case "2":
			$bgcol = $cat['purple_color'];
			break;	
	}
	$rgb = str_split($bgcol, 2);
	$forcol = (((0.213*hexdec($rgb[0]) + 0.715*hexdec($rgb[1]) + 0.072*hexdec($rgb[2]))/255) < 0.5) ? 'FFFFFF':'000000';
	// get image url
    $img_file = findUploadDirectory($app_id) . "/aroundus/$qry[id].jpg";

    if (file_exists($img_file)) {
        $img_url = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/aroundus/$qry[id].jpg";
    } else {
        $img_url = $WEB_HOME_URL."/uploads/icons/aroundus.png";
    }
	
    // invalid latitude & longitude value fixing...
    if($qry["latitude"] > 90) $qry["latitude"] = 90;
    if($qry["latitude"] < -90) $qry["latitude"] = -90;
    if($qry["longitude"] > 180) $qry["longitude"] = 180;
    if($qry["longitude"] < -180) $qry["longitude"] = -180;

	foreach ( $qry as $k => $v ) {
		$qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
	}

    $old_style = "";
    $description = $qry["info"];
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
                if(!empty($bg_url)) {
                    $content_style = substr($content_style, 0, strlen($content_style)-1).";background-image:".$bg_url.";";
                }
            }
        }
    }
    
    if(!empty($old_style))
        $description = str_replace($old_style, $content_style, $description);
    else
        $description = str_replace("<body>", "<body ".$content_style.">", $description);

    $poi[] = array("id" => intval($qry['id']),
		"name" => $qry["name"],
		"website" => $qry["website"],
                 "latitude" => $qry["latitude"],
                 "longitude" => $qry["longitude"],
  				 "description"=>$description,
  				 "address1"=>$qry["address_1"],
  				 "address2"=>$qry["address_2"],
  				 "city"=>$qry["city"],
  				 "state"=>$qry["state"],
  				 "zip"=>$qry["zip"],
				 "country"=>!is_null($qry["country"]) ? $qry["country"] : '',
				 "distance_type"=> $qry["distance_type"] ? 'Mile' : 'Kilometer',
                 "email"=>$qry["email"],
                 "telephone"=>$qry["telephone"],
                 "image"=> $img_url,
                 "color" => $bgcol,
  				 "forcolor" => $forcol);

}
$feedv = array("poi" => $poi);

$filter = array("red_title", "green_title","purple_title","red_color","green_color","purple_color", "red_color_text","green_color_text","purple_color_text");
foreach($filter AS $v) {
	$feedv[$v] = $cat[$v];
}
$feedv["CustomButton"] = $button;
$feedv["image"] = $image;
$feedv["background"] = $bg_image;

$feed[] = $feedv;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
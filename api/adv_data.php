<?php

include_once "dbconnect.inc";
// Preprocessing...
if (!isset($_REQUEST["app_code"]) && (intval($_REQUEST["id"]) > 0)) {
    $app = get_app_record($conn, intval($_REQUEST["id"]));
    $_REQUEST["app_code"] = $app["code"];
}

include_once "app.inc";

include_once "common_functions.php";
include_once "app_functions.php";

require_once "ray_model/adv_pages.php";
require_once "ray_model/adv_theme.php";

$data = make_data_safe($_REQUEST);

$feed = array();

if($data["action"] == "1") { // total, sent message total
    
	// Should padd following param
	// id, tk
	if(check_token($conn, $data["id"], $data["tk"]) || true) {
	    
		$feed["error"] = "0";
        
        // Retrieve Adv Categories, Tpls
        $adv_cats = array();
        
        $M = new AdvPages($conn);
        
        $image_cat_path = $WEB_ROOT_PATH . "/images/adv_resources/cats/";
        $image_tpl_path = $WEB_ROOT_PATH . "/images/adv_resources/tpls/";
        
        $cats = $M->retrieve_cats();
        
        for($i=0; $i<count($cats); $i++) {
            
            $thumbnail = "";
            if(file_exists($image_cat_path . $cats[$i]["id"] . ".png")) {
                $thumbnail = "/res_images/adv_resources/cats/" . $cats[$i]["id"] . ".png?width=150";
            } else {
                $sd = json_decode($cats[$i]["sample_data"], true);
                $thumbnail = $sd["image"];
            }
            
            
            $ref_box = explode("?", $thumbnail);
            if(count($ref_box) > 1) {
                $thumbnail = $ref_box[0] . "?" . $ref_box[1];
            }
            
            if(!(preg_match("/^http:\/\/(.*)$/", $thumbnail) || preg_match("/^https:\/\/(.*)$/", $thumbnail))) {
                $thumbnail = $PUBLIC_WEB_HOME_URL . str_replace("//", "/", "/" . $thumbnail);    
            }
            
            
            // Retrieve Sub TPLs
            
            $tpls = $M->retrieve_tpls($cats[$i]["id"]);
            
            for($j=0; $j<count($tpls); $j++) {
                
                // Retrieve Sample Data    
                $sd = json_decode($tpls[$j]["sample_data"], true);
                if($sd["image"] != "") {
                    $ref_box = explode("?", $sd["image"]);
                    if(count($ref_box) > 1) {
                        $sd["image"] = $ref_box[0] . "?" . $ref_box[1];
                    }
                    
                    if(!(preg_match("/^http:\/\/(.*)$/", $sd["image"]) || preg_match("/^https:\/\/(.*)$/", $sd["image"]))) {
                        $sd["image"] = $PUBLIC_WEB_HOME_URL . str_replace("//", "/", "/" . $sd["image"]);    
                    }
                }

                $tpls[$j]["thumbnail"] = $sd["image"];
                $tpls[$j]["sample_data"] = $sd;
                
                // Retrieve Theme
                $thm = explode(" ", $sd["theme"], 2);
                $thm[1] = str_replace("sub", "", $thm[1]);
                
                $tpls[$j]["mother_theme"] = ($thm[0] && ($thm[0]!=""))?$thm[0]:"overlay";
                $tpls[$j]["child_theme"] = $thm[1]?$thm[1]:"";
            }
            
            $adv_cats[] = array(
                "id" => $cats[$i]["id"],
                "name" => $cats[$i]["cat_name"],
                "thumbnail" => $thumbnail,
                "total" => $cats[$i]["total"],
                "tpls" => $tpls,
            );
        }

        // Retrieve Adv Theme
        $thms = array();
        
        $AM = new AdvTheme($conn);
        $adv_themes_cats = $AM->retrieve_cats();
        
        $adv_themes_sub = array(
            "classic" => array(
                array(
                    "id" => "0",
                    "cat_name" => "classic",
                    "name" => "Classic",
                    "colors" => array(
                        "bg" => array(
                            "image" => "",
                            "color" => "FFFFFF",
                        ),
                        "banner" => array("frame" => ""),
                        "header" => array(
                            "color" => "000000",
                            "font" => "AmericanTypewriter",
                        ),
                        "content" => array(
                            "color" => "000000",
                            "font" => "Arial",
                        ),
                        "author" => array(
                            "color" => "000000",
                            "font" => "AmericanTypewriter",
                        ),
                        "website" => array(
                            "color" => "000000",
                            "font" => "Arial-BoldMT",
                        ),
                        "border" => array(
                            "outer" => "000000",
                            "inner" => "000000"
                        ),
                    ),
                    "extra" => "false",
                    "active" => "1",
                    "icon" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/classic.png",
                ),
            ),
            
            "minimal" => array(
                array(
                    "id" => "0",
                    "cat_name" => "minimal",
                    "name" => "Minimal",
                    "colors" => array(
                        "bg" => array(
                            "image" => "",
                            "color" => "FFFFFF",
                        ),
                        "banner" => array("frame" => ""),
                        "header" => array(
                            "color" => "000000",
                            "font" => "AmericanTypewriter",
                        ),
                        "content" => array(
                            "color" => "000000",
                            "font" => "Arial",
                        ),
                        "author" => array(
                            "color" => "333333",
                            "font" => "AmericanTypewriter",
                        ),
                        "website" => array(
                            "color" => "000000",
                            "font" => "Arial-BoldMT",
                        ),
                        "border" => array(
                            "outer" => "000000",
                            "inner" => "000000"
                        ),
                    ),
                    "extra" => "false",
                    "active" => "1",
                    "icon" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/minimal.png",
                ),
            ),
            "modern" => array(
                array(
                    "id" => "0",
                    "cat_name" => "modern",
                    "name" => "Modern",
                    "colors" => array(
                        "bg" => array(
                            "image" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/images/bg_white.png",
                            "color" => "FFFFFF",
                        ),
                        "banner" => array("frame" => ""),
                        "header" => array(
                            "color" => "555555",
                            "font" => "AmericanTypewriter",
                        ),
                        "content" => array(
                            "color" => "555555",
                            "font" => "Arial",
                        ),
                        "author" => array(
                            "color" => "555555",
                            "font" => "AmericanTypewriter",
                        ),
                        "website" => array(
                            "color" => "555555",
                            "font" => "Arial-BoldMT",
                        ),
                        "border" => array(
                            "outer" => "999999",
                            "inner" => "999999"
                        ),
                    ),
                    "extra" => "false",
                    "active" => "1",
                    "icon" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/modern.png",
                ),
            ),
            "overlay" => array(
                array(
                    "id" => "0",
                    "cat_name" => "overlay",
                    "name" => "Overlay",
                    "colors" => array(
                        "bg" => array(
                            "image" => "",
                            "color" => "777777",
                        ),
                        "banner" => array("frame" => ""),
                        "header" => array(
                            "color" => "FFFFFF",
                            "font" => "AmericanTypewriter",
                        ),
                        "content" => array(
                            "color" => "FFFFFF",
                            "font" => "Arial",
                        ),
                        "author" => array(
                            "color" => "FFFFFF",
                            "font" => "AmericanTypewriter",
                        ),
                        "website" => array(
                            "color" => "333333",
                            "font" => "Arial-BoldMT",
                        ),
                        "border" => array(
                            "outer" => "777777",
                            "inner" => "777777"
                        ),
                    ),
                    "extra" => "false",
                    "active" => "1",
                    "icon" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/overlay.png",
                ),
            ),
            "two_tone" => array(
                array(
                    "id" => "0",
                    "cat_name" => "two_tone",
                    "name" => "Two Tone",
                    "colors" => array(
                        "bg" => array(
                            "image" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/images/bg_2tone_blue.png",
                            "color" => "000000",
                        ),
                        "banner" => array("frame" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/images/pf_2tone_blue.png"),
                        "header" => array(
                            "color" => "000000",
                            "font" => "AmericanTypewriter",
                        ),
                        "content" => array(
                            "color" => "CCCCCC",
                            "font" => "Arial",
                        ),
                        "author" => array(
                            "color" => "000000",
                            "font" => "AmericanTypewriter",
                        ),
                        "website" => array(
                            "color" => "CCCCCC",
                            "font" => "Arial-BoldMT",
                        ),
                        "border" => array(
                            "outer" => "999999",
                            "inner" => "999999"
                        ),
                    ),
                    "extra" => "false",
                    "active" => "1",
                    "icon" => $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/overlay.png",
                ),
            )
        );
        
        $all_themes = $AM->search("adv_theme_item", array("active" => 1));
        foreach($all_themes AS $at) {
                
            $img_url = $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/" . $at["cat_name"] . ".png";
            $icon = $WEB_ROOT_PATH . "/images/adv_resources/themes/icons/" . $at["id"] . ".png";
            if(file_exists($icon)) {
                $img_url = $PUBLIC_WEB_HOME_URL . "/res_images/adv_resources/themes/icons/" . $at["id"] . ".png";    
            }
            
            $at["icon"] = $img_url;
            
            // Load Theme Details
            $th = json_decode($at["colors"], true);
            $at["colors"] = $th;
            
            //$adv_themes_sub[$at["cat_name"]][$at["id"]] = $at;
            
            // Image URL Default Process & URL Management
            if(trim($at["colors"]["banner"]["frame"] == "")) {
                $at["colors"]["banner"]["frame"] = $adv_themes_sub[$at["cat_name"]][0]["colors"]["banner"]["frame"];
            }
            if(trim($at["colors"]["bg"]["image"] == "")) {
                $at["colors"]["bg"]["image"] = $adv_themes_sub[$at["cat_name"]][0]["colors"]["bg"]["image"];
            } 
            $at["colors"]["banner"]["frame"] = url_rel2abs($at["colors"]["banner"]["frame"]);
            $at["colors"]["bg"]["image"] = url_rel2abs($at["colors"]["bg"]["image"]);
            
            $adv_themes_sub[$at["cat_name"]][] = $at;
            
        }
        
        for($i=0; $i<count($adv_themes_cats); $i++) {
            if(!$adv_themes_sub[$adv_themes_cats[$i]["cat_name"]]) $adv_themes_sub[$adv_themes_cats[$i]["cat_name"]] = array();
            $thms[] = array(
                "id" => $adv_themes_cats[$i]["id"],
                "type" => $adv_themes_cats[$i]["cat_name"],
                "children" => $adv_themes_sub[$adv_themes_cats[$i]["cat_name"]],
            );
        }
        
        $feed["tpls"] = $adv_cats;
        $feed["themes"] = $thms;
        
        
	} else {
		$feed["error"] = "9";		
	}
} else {
	$feed["error"] = "44";
	
}


$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);


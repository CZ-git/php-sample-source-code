<?php

//----------------------------------------------------------------------------
//geting the data for even and odd row colour to send in the home sub tabs
//----------------------------------------------------------------------------

$sql = "select *
        from app_locations l, apps a
        where l.app_id = a.id and l.app_id = '$app_id'
        order by l.seq";
$res = mysql_query($sql, $conn);
$res1 = mysql_query($sql, $conn);

$qry_colour1 = mysql_fetch_array($res1);

$odd_row_col= $qry_colour1['odd_row_color'];
$even_row_col= $qry_colour1['even_row_color'];

while ($qry = mysql_fetch_array($res)) {
  if (!$add) $add = $qry;
  $addresses[] = array("address_1" => $qry["address_1"],
                       "address_2" => $qry["address_2"],
                       "city" => $qry["city"],
                       "state" => $qry["state"],
                       "zip" => $qry["zip"],
                       "telephone" => $qry["telephone"],
                       "email" => $qry["email"],
                       "website" => $qry["website"],
                       "latitude" => $qry["latitude"],
                       "longitude" => $qry["longitude"]);
}

//----------------------------------------------------------------------------
//geting ready for mapping_tab process. This is for sub tab handling.
//----------------------------------------------------------------------------

$sql = "select t.*,
                        vc.value1_field,
                        vc.value2_field,
                        vc.value3_field
        from app_tabs t
            left join view_controllers vc
            on t.view_controller = vc.name
        where t.app_id = '$app_id'
        and t.is_active > 0
        order by t.seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {  // Let's hope this never happens
    $feed[] = array("tab_label" => "No tabs");
} else {
  while ($qry = mysql_fetch_array($res)) {

    $itemhome = ViewControllerMapping($qry, $data);

     $feed_home[] = $itemhome;
  }
}
 


//----------------------------------------------------------------------------
//geting the sub tab records
//----------------------------------------------------------------------------
if(isset($data[tab_id]) && ($data[tab_id] != '')) {
    $sql_homesub = "SELECT sub.*, a.tab_label, a.view_controller AS actual_view FROM home_sub_tabs as sub LEFT JOIN app_tabs AS a ON sub.link_tab_id = a.id WHERE sub.app_id = '$app_id' and sub.is_active<>'0' AND sub.tab_id = $data[tab_id]  order by sub.seq limit 6";
} else {
    $sql_homesub = "SELECT sub.*, a.tab_label, a.view_controller AS actual_view FROM home_sub_tabs as sub LEFT JOIN app_tabs AS a ON sub.link_tab_id = a.id WHERE sub.app_id = '$app_id' and sub.is_active<>'0' AND sub.tab_id <> 0  order by sub.seq limit 6";
}
  
$res_homesub = mysql_query($sql_homesub, $conn);

if(mysql_num_rows($res_homesub)>0) {
    
    while ($qry_homesub = mysql_fetch_array($res_homesub)) {
        
        $myArray = $feed_home;
        $searchTerm = $qry_homesub["link_tab_id"]; 
        //$qry_homesub["actual_view"];
        
        if (false !== ($pos = array_search2d_byfield("tab_id", $searchTerm, $myArray))) {
           // echo $searchTerm . " found at index " . $pos."----".$feed_home[$pos]['ViewController']."<br>";
           $vcontroller=$feed_home[$pos]['ViewController'];
        }   
         
          //Mike Function ends/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                     
          $sub = array(
                      "tab_id" => $qry_homesub["link_tab_id"],
                      "link_tab_id" => $qry_homesub["tab_id"],  
                      "is_active" => $qry_homesub["is_active"],
                      "ViewController" => $vcontroller, //$qry_homesub["actual_view"],
                      "TabLabelFont" => $qry_homesub["TabLabelFont"],
                      "TabLableTextColor" => $qry_homesub["TabLableTextColor"],
                      "TabLabelText" => $qry_homesub["TabLabelText"],
                      "TabImage" => rawurlencode($qry_homesub["TabImage"]),
                      "OddRowColor" => $odd_row_col,
                      "EvenRowColor" => $even_row_col,
                      "NavigationController" => $qry_homesub["NavigationController"],
                      "LastUpdated" => $qry_homesub["LastUpdated"],
                      "Custom_Icon" => $qry_homesub["Custom_Icon"],
                      "TabLableTextBackgroundColor" => $qry_homesub["TabLableTextBackgroundColor"],
                      "seq" => $qry_homesub["seq"] 
          );
          if (false !== $pos) {
            $sub = array_merge($feed_home[$pos], $sub);
          }
          $home_sub_tabs[] = $sub;
                          
    }

 

}
 
//----------------------------------------------------------------------------
//Retrieve sub tab option, if it is only for iPad or not.
//----------------------------------------------------------------------------
$t = get_app_tab_record($conn, $data["tab_id"]);
$v3 = unserialize($t["value3"]);
if(!is_array($v3)) {
    $v3 = array('ipad' => '0');
}
if(!isset($v3['ipad'])) {
    $v3['ipad'] = '0';
}

//----------------------------------------------------------------------------
//Compose JSON
//----------------------------------------------------------------------------

if ((!$add["app_store_url"])||($add["app_store_url"] == '')) {
    $add["app_store_url"] = "http://www.appstore.com";
}
  
if(mysql_num_rows($res_homesub)>0) {
  
    $this_item = array("name" => $add["name"],
                "telephone" => $add["telephone"],
                "latitude" => $add["latitude"],
                "longitude" => $add["longitude"],
                "app_store_url" => $add["app_store_url"],
                "addresses" => $addresses,
                "only4ipad" => ($v3['ipad']=='1')?'1':'0',
                "home_sub_tabs" => $home_sub_tabs,
                "facebook_api_key" => "124661487570397",
                "twitter_key" => "w7v7fdJfJic6Xk8OmLSM0g",
                "twitter_secret" => "ymoXT5sdZlhppJH0nI3dNl48JubUaQoLtU3w6mAk");
} else {
    $this_item = array("name" => $add["name"],
                "telephone" => $add["telephone"],
                "latitude" => $add["latitude"],
                "longitude" => $add["longitude"],
                "app_store_url" => $add["app_store_url"],
                "addresses" => $addresses,
                "facebook_api_key" => "124661487570397",
                "twitter_key" => "w7v7fdJfJic6Xk8OmLSM0g",
                "twitter_secret" => "ymoXT5sdZlhppJH0nI3dNl48JubUaQoLtU3w6mAk");
}


//-----------------------------------------------------------------------------------------------   
// This is for new design. Here I will retrieve background image as well as sliding images.
//-----------------------------------------------------------------------------------------------
// Retrieve sliding images and background image
//-----------------------------------------------------------------------------------------------

$mobile_bg = "";
$mobile_images = array();

$dir = findUploadDirectory($app_id);

//-----------------------------------
// Retrive for Mobile
//-----------------------------------
// Retrieve sliding images
//-----------------------------------

// check bg set mode
$sql = "SELECT iphone5_bg_set_mode, ipad_bg_set_mode FROM apps WHERE id='$app_id'";
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);
$iphone5_bg_set_mode = intval($qry['iphone5_bg_set_mode']);
$ipad_bg_set_mode = intval($qry['ipad_bg_set_mode']);

// $sql = "SELECT * FROM images_bg WHERE device_type='0' AND detail_type='4' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
$sql = "SELECT * FROM images_bg WHERE device_type='0' AND detail_type='4' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
$res = mysql_query($sql, $conn);
while ($qry = mysql_fetch_array($res)) {
    if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
        $mobile_images[] = array(
            "path" => $dir."/".$qry["name"],
            "url" => $WEB_HOME_URL."/custom_images/".$data["app_code"]."/".$qry["name"],
            "device" => "0", 
            "seq" => $qry["seq"],
        );
    }
}
//------------------------------------
// Retrieve background image
//------------------------------------
// $sql = "SELECT * FROM images_bg WHERE device_type='0' AND detail_type='0' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
$sql = "SELECT * FROM images_bg WHERE device_type='0' AND detail_type='0' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);
if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
    //$mobile_bg = base64_encode(file_get_contents($dir."/".$qry["name"])); 
    $mobile_bg = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/".$qry["name"];
}


//-----------------------------------
// Retrive for iPad
//-----------------------------------
if ($_REQUEST['device']!='' && $_REQUEST['device']=='ipad' && $ipad_bg_set_mode == 0) {
    // Retrieve sliding images
    $ipad_images = array();
    
    $dir = findUploadDirectory($app_id) . "/ipad";
    
    //------------------------------------
    // Retrieve sliding image
    //------------------------------------
    // $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='4' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='4' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $res = mysql_query($sql, $conn);
    
    $mobile_images_trick = array();
    foreach($mobile_images AS $mb) {
        $mobile_images_trick[intval($mb["seq"])] = $mb;
    }

    while ($qry = mysql_fetch_array($res)) {
        if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
            $mobile_images_trick[intval($qry["seq"])] = array(
                "path" => $dir."/".$qry["name"],
                "url" => $WEB_HOME_URL."/custom_images/".$data["app_code"]."/ipad/".$qry["name"],
                "device" => "1", 
                "seq" => $qry["seq"],
            );
        }
    }

    for($i=5; $i>0; $i--) {
        if(isset($mobile_images_trick[$i])) {
            $ipad_images[] = $mobile_images_trick[$i];
        }
    }
    // Sliding images could not be inherited...
    $mobile_images = $ipad_images;

    /*
    if(count($ipad_images) > 0) {
        $mobile_images = $ipad_images;
    }
    */
    
    //------------------------------------
    // Retrieve background image
    //------------------------------------
    // $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='0' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='0' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $res = mysql_query($sql, $conn);
    $qry = mysql_fetch_array($res);
    if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
        //$mobile_bg = base64_encode(file_get_contents($dir."/".$qry["name"]));
        $mobile_bg = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/ipad/".$qry["name"]; 
    }
}


//-----------------------------------
// Retrive for iPhone5
//-----------------------------------
if ($_REQUEST['device']!='' && $_REQUEST['device']=='iphone5' && $iphone5_bg_set_mode == 0) {
    // Retrieve sliding images
    $ipad_images = array();
    
    $dir = findUploadDirectory($app_id) . "/iphone5";

	//Now iphone5 sliding images are disabled due to some confusing of use.
    //------------------------------------
    // Retrieve sliding image
    //------------------------------------
    // $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='4' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $sql = "SELECT * FROM images_bg WHERE device_type='2' AND detail_type='4' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $res = mysql_query($sql, $conn);
    
    $mobile_images_trick = array();
    foreach($mobile_images AS $mb) {
        $mobile_images_trick[intval($mb["seq"])] = $mb;
    }
	
	$iphone5_images = array();

    while ($qry = mysql_fetch_array($res)) {
        if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {

            $mobile_images_trick[intval($qry["seq"])] = array(
                "path" => $dir."/".$qry["name"],
                "url" => $WEB_HOME_URL."/custom_images/".$data["app_code"]."/iphone5/".$qry["name"],
                "device" => "2", 
                "seq" => $qry["seq"],
            );
            
        }
    }

	for($i=5; $i>0; $i--) {
		if(isset($mobile_images_trick[$i])) {
			$iphone5_images[] = $mobile_images_trick[$i];
		}
	}
    
    // Sliding images could not be inherited...
    $mobile_images = $iphone5_images;


    /*
    if(count($ipad_images) > 0) {
        $mobile_images = $ipad_images;
    }
    */
    
    //------------------------------------
    // Retrieve background image
    //------------------------------------
    // $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='0' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $sql = "SELECT * FROM images_bg WHERE device_type='2' AND detail_type='0' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
    $res = mysql_query($sql, $conn);
    $qry = mysql_fetch_array($res);
    if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
        //$mobile_bg = base64_encode(file_get_contents($dir."/".$qry["name"]));
        $mobile_bg = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/iphone5/".$qry["name"]; 
    }
}

//--------------------------------------------------------
// Plant background and sliding images into JSON
//--------------------------------------------------------
$this_item["image"] = $mobile_bg;

if(count($mobile_images) > 0) {
    
    $this_item["manyImages"] = "YES";
    $this_item["imagesInOrder"] = array();
        
    $limit = (count($mobile_images) > 5)? 5:count($mobile_images);
    for($i=0; $i<$limit; $i++) {
        $this_item["imagesInOrder"][] = $mobile_images[$i]["url"];

		//if($_REQUEST['device']=='iphone5') $mobile_images[$i]["device"] = 0; // We just want to take sliding link from iphone image link.
        $lnk_tabs = getSlidingRelatedTab($conn, $app_id, "0", $mobile_images[$i]["seq"], $mobile_images[$i]["device"]);
        
        if (false !== ($pos = array_search2d_byfield("tab_id", $lnk_tabs["link_tab_id"], $feed_home))) {
           $vcontroller=$feed_home[$pos]['ViewController'];
        } else {
            $vcontroller = "";
        } 

        // Additional prcoessing ::: 
        $additional_info = array();

        if($vcontroller == "InfoDetailViewController") {
            // Try to retrieve info Detail ID value...
            $sql_x = "select i.id, i.description from info_categories c, info_items i
                            where c.id = i.info_category_id 
                            and c.tab_id = '" . $lnk_tabs["link_tab_id"] . "'
            order by c.seq, c.id, i.seq, i.id";
            $res_x = mysql_query($sql_x, $conn);
            if (mysql_num_rows($res_x) == 1) {
                $lnk_tabs["link_detail_id"] = mysql_result($res_x, 0, 0);
            }

        } else if($vcontroller == "WebViewController") {
            $sql_x = "select * from web_views
                            where app_id = '$app_id'
                            and tab_id = '" . $lnk_tabs["link_tab_id"] . "'
                            order by seq, url";
            $res_x = mysql_query($sql_x, $conn);
            if (mysql_num_rows($res_x) > 1) {
                    $vcontroller = "WebTierViewController";
            } else if (mysql_num_rows($res_x)) {
                $web_row = mysql_fetch_array($res_x);
                $additional_info["URL"] = $web_row["url"];
                if((strpos($additional_info["URL"], "http://") === false) && (strpos($additional_info["URL"], "https://") === false)) {
                    $additional_info["URL"] = "http://" . $additional_info["URL"];
                }
                
                if($web_row["is_donate"] == "1") {
                    $additional_info["openInSafari"] = "YES";
                } else {
                    $additional_info["openInSafari"] = "NO";
                }
                
            } else {
                $additional_info["URL"] = "http://www.google.com/";
            }
        
            $tab_x = get_app_tab_record($conn, $lnk_tabs["link_tab_id"]);
            if($tab_x["value1"] == "1") {
                $additional_info["showNavigationBar"] = "1";
            } else {
                $additional_info["showNavigationBar"] = "0";
            }

        }
        
        $link_tab_info = array(
            "tab_id" => $lnk_tabs["link_tab_id"]?$lnk_tabs["link_tab_id"]:"0",
            "item_id" => $lnk_tabs["link_detail_id"]?$lnk_tabs["link_detail_id"]:"0",
            "cat_id" => $lnk_tabs["link_cat_id"]?$lnk_tabs["link_cat_id"]:"0",
            "view" => $vcontroller?$vcontroller:"",
        );

        // Validating something...
        if($link_tab_info["tab_id"] == "0") {
            $link_tab_info["item_id"] = "0";
            $link_tab_info["cat_id"] = "0";
            $link_tab_info["view"] = "";
        }

        /*
        if(count($additional_info) > 0) {
            $link_tab_info = array_merge($link_tab_info, $additional_info);
        }
        */

        $this_item["linkedTabs"][] = $link_tab_info;
    }
    
} else {
    $this_item["manyImages"] = "NO";
    $this_item["imagesInOrder"] = array();
}

//--------------------------------------------------------
// Load custom button
//--------------------------------------------------------

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
    
    
    if(strpos($details["inner_button"], "custom") === false) {
        $button_file = "../images/templates/inner_buttons/".$details["inner_button"];
        if (file_exists($button_file) && !is_dir($button_file)) {
            if($data["base64"] == "1") {
                $image = base64_encode(file_get_contents($button_file));
            } else {
                $image = $WEB_HOME_URL."/inner_buttons/".$details["inner_button"];
            }
            $this_item["CustomButton"] = $image;
        } else {
            $this_item["CustomButton"] = "";        
        }
    } else {
        $button_file = findUploadDirectory($app_id) . "/templates/inner_buttons/".$details["inner_button"];
        if (file_exists($button_file) && !is_dir($button_file)) {
            if($data["base64"] == "1") {
                $image = base64_encode(file_get_contents($button_file));
            } else {
                $image = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/templates/inner_buttons/".$details["inner_button"];
            }
            $this_item["CustomButton"] = $image;
        } else {
            $this_item["CustomButton"] = "";        
        }
    }
        
    
} else {
    //------------------------------------------------------------------------------------
    // If this is not for new Design, then get the button from the file system directly
    //------------------------------------------------------------------------------------
    $button_file = findUploadDirectory($app_id) . "/button.png";
    if (file_exists($button_file)) {
        if($data["base64"] == "1") {
            $image = base64_encode(file_get_contents($button_file));
        } else {
            $image = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/button.png";
        }
        $this_item["CustomButton"] = $image;
    } else {
        $this_item["CustomButton"] = "";        
    }
}

// }

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
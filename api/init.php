<?php

/* 
 * init.php
 * Created: 6/28/2013
 * Author: Ray C
 * 
 * Description: settings.php + home.php + tabs.php
 * 
*/
// Ady changes
// 1. change $data["app_name"] to $app_name
// 2. add $a = get_app_record($conn, $app_id); to get the records

include_once "dbconnect.inc";
include_once "app.inc";
include_once "ray_model/app_xtr.php";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

$data = make_data_safe($_GET);

$app = get_app_record($conn, $app_id);

////////////////////////////////////////
// ---------- Functions ---------------
////////////////////////////////////////

function array_search2d($needle, $haystack) {
    for ($i = 0, $l = count($haystack); $i < $l; ++$i) {
        if (in_array($needle, $haystack[$i])) return $i;
    }
    return false;
}

function array_search2d_byfield($field, $needle, $haystack) {
    for ($i = 0, $l = count($haystack); $i < $l; ++$i) {
        if($haystack[$i][$field] == $needle) return $i;
    }
    return false;
}

function ViewControllerMapping($qry, $data) {
        $this_item_home = array("ViewController" => $qry["view_controller"]);
        
        global $conn;
        global $app_id;
        global $PUBLIC_WEB_HOME_URL;

        //Make the restaurant exactly same with Webviews
        if ($qry["view_controller"] == "RestaurantBookingViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "RestaurantBookingViewController";
        }

        if ($qry["view_controller"] == "PDFViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "PDFViewController";
        }

        if ($qry["view_controller"] == "WuFooViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "WuFooViewController";
        }
        
        if ($qry["view_controller"] == "PDFViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "PDFViewController";
        }
        
        if ($qry["view_controller"] == "InfoSectionViewController") {  // This is now redundant for 1-tier; if 2-tier is set it kicks in though
            $sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
            $res2 = mysql_query($sql, $conn);
            if (mysql_num_rows($res2) == 1) {
                $this_item_home["ViewController"] = "InfoItemsViewController";
                $this_item_home["Main_ViewController"] = "InfoSectionViewController";
                $this_item_home["section_id"] = mysql_result($res2, 0, 0);
            }
            else {
                $this_item_home["tab_id"] = $qry["id"];
            }
        } elseif ($qry["view_controller"] == "InfoItemsViewController") {
                        $sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
                        $res2 = mysql_query($sql, $conn);
                        if (mysql_num_rows($res2) == 1) {
                                $this_item_home["section_id"] = mysql_result($res2, 0, 0);
            }
        } elseif ($qry["view_controller"] == "InfoDetailViewController") {
                        $sql = "select i.id, i.description from info_categories c, info_items i
                                where c.id = i.info_category_id 
                                and c.tab_id = '$qry[id]'
                order by c.seq, c.id, i.seq, i.id";
                        $res2 = mysql_query($sql, $conn);
                        if (mysql_num_rows($res2) == 1) {
                                $this_item_home["item_id"] = mysql_result($res2, 0, 0);
                        }
        } elseif ($qry["view_controller"] == "LocationViewController") {
            $sql = "select * from app_locations
                where app_id = '$app_id'";
            $res2 = mysql_query($sql, $conn);
            $count_locations = mysql_num_rows($res2);
            if ($count_locations > 1) {
                $this_item_home["ViewController"] = "LocationListViewController";
                $this_item_home["Main_ViewController"] = "LocationViewController";
            } else if ( $count_locations < 1 ) {
                $this_item_home["ViewController"] = "LocationListViewController";
            } else {
                $res_loc = mysql_fetch_array($res2);
                if ( !$res_loc['address_1'] && !$res_loc['latitude'] && !$res_loc['longitude'] ) {
                    $this_item_home["ViewController"] = "LocationListViewController";
                }
            }
        } elseif ($qry["view_controller"] == "WebViewController") {
            $sql = "select * from web_views
                                where app_id = '$app_id'
                                and tab_id = '$qry[id]'
                                order by seq, url";
            
            $res2 = mysql_query($sql, $conn);
            if (mysql_num_rows($res2) > 1) {
                    $this_item_home["ViewController"] = "WebTierViewController";
                    $this_item_home["Main_ViewController"] = "WebViewController";
                    $this_item_home["tab_id"] = $qry["id"];
            } else if (mysql_num_rows($res2)) {
                $web_row = mysql_fetch_array($res2);
                
                $this_item_home["URL"] = $web_row["url"]; 
                if((strpos($this_item_home["URL"], "http://") === false) && (strpos($this_item_home["URL"], "https://") === false)) {
                    $this_item_home["URL"] = "http://" . $this_item_home["URL"];
                }
                
                if($web_row["is_donate"] == "1") {
                    $this_item_home["openInSafari"] = "YES";
                } else {
                    $this_item_home["openInSafari"] = "NO";
                }
                
            } else {
                $this_item_home["URL"] = "http://www.google.com/";
            }

        } elseif ($qry["view_controller"] == "CustomFormViewController") {
            $sql = "SELECT * FROM form_layout
                                WHERE user_id = '$app_id'
                                AND user_type = '1' 
                                AND tab_id = '$qry[id]'
                                AND is_active = '1' 
                                AND is_removed = '0' 
                                ORDER BY seq";
            $res2 = mysql_query($sql, $conn);
            
            if (mysql_num_rows($res2) > 1) {
                    $this_item_home["ViewController"] = "WebTierViewController";
                    $this_item_home["Main_ViewController"] = "CustomFormViewController";
                    $this_item_home["tab_id"] = $qry["id"];
            } else if (mysql_num_rows($res2)) {
                $web_row = mysql_fetch_array($res2);
                
                if(($PUBLIC_WEB_HOME_URL == "http://www.appsomen.com")||($PUBLIC_WEB_HOME_URL == "http://appsomen.com")) {
                    $this_item_home["URL"] = $PUBLIC_WEB_HOME_URL . "/client/form_build.php?tk=" . $web_row["tk"];
                } else {
                    $this_item_home["URL"] = $PUBLIC_WEB_HOME_URL . "/form_build.php?tk=" . $web_row["tk"];
                }

                $this_item_home["openInSafari"] = "NO";

                $this_item_home["ViewController"] = "WebViewController";
                $this_item_home["Main_ViewController"] = "CustomFormViewController";
                
            } else {
                $this_item_home["URL"] = "http://www.google.com/";
                $this_item_home["ViewController"] = "WebViewController";
                $this_item_home["Main_ViewController"] = "CustomFormViewController";
            }
        } elseif ($qry["view_controller"] == "RSSFeedViewController") {
            $this_item_home["tab_id"] = $qry["id"];
        } else if ($qry["view_controller"] == "GalleryCoverFlowViewController") {
            // Okay, just go ahread.
        } else if ($qry["view_controller"] == "GalleryViewController") {

            // Multiple or Single
            $listsql = "select * from galleries where app_id = '$app_id' and tab_id = '$qry[id]'";
            $listres = mysql_query($listsql, $conn);
            $list_count = mysql_num_rows($listres);
            if ( $list_count > 1 ) {
                $this_item_home["ViewController"] = "GalleryListViewController";
                $this_item_home["Main_ViewController"] = "GalleryViewController";
            } else {
                $gallery_row = mysql_fetch_array($listres);

                $this_item_home["ViewController"] = "GalleryViewController";
                $this_item_home["Main_ViewController"] = "GalleryViewController";

                $gallery_info = $gallery_row['info'];
                if ( $gallery_info ) {
                    $gallery_info = unserialize( $gallery_info );
                    if ( $gallery_info['type'] == '1' ) {
                        $this_item_home["ViewController"] = "GalleryCoverFlowViewController";
                    }
                }
            }
            
            $v2 = unserialize($qry['value2']);
            if(!is_array($v2)) $v2 = array();
            
            
            foreach($v2 AS $k => $v) {
                /*
                $more_this_item_el = array();
                foreach($this_item AS $tk => $tv) {
                    $more_this_item_el[$tk] = $tv;
                }
                */
                if(($v["active"] == "1") && ($k == "flickr")) {
                    if ( $v["disp"] == "0" ) {
                        if ( $v["gtype"] == "0" ) {
                            $this_item_home["ViewController"] = "FlickrPhotoStreamGalleryViewController";
                        } else {
                            $this_item_home["ViewController"] = "FlickrPhotoStreamCoverflowViewController";
                        }
                    } else {
                        $this_item_home["ViewController"] = "FlickrViewController";
                    }

                    $this_item_home["Main_ViewController"] = "GalleryViewController";
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                } else if(($v["active"] == "1") && ($k == "picasa")) {
                    $this_item_home["ViewController"] = "PicasaViewController";
                    $this_item_home["Main_ViewController"] = "GalleryViewController";
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                }
            }
        } else if ($qry["view_controller"] == "GalleryListViewController") {

            $v2 = unserialize($qry['value2']);
            if(!is_array($v2)) $v2 = array();
            
            
            foreach($v2 AS $k => $v) {
                /*
                $more_this_item_el = array();
                foreach($this_item AS $tk => $tv) {
                    $more_this_item_el[$tk] = $tv;
                }
                */
                if(($v["active"] == "1") && ($k == "flickr")) {
                    if ( $v["disp"] == "0" ) {
                        if ( $v["gtype"] == "0" ) {
                            $this_item_home["ViewController"] = "FlickrPhotoStreamGalleryViewController";
                        } else {
                            $this_item_home["ViewController"] = "FlickrPhotoStreamCoverflowViewController";
                        }
                    } else {
                        $this_item_home["ViewController"] = "FlickrViewController";
                    }

                    $this_item_home["Main_ViewController"] = "GalleryViewController";
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                } else if(($v["active"] == "1") && ($k == "picasa")) {
                    $this_item_home["ViewController"] = "PicasaViewController";
                    $this_item_home["Main_ViewController"] = "GalleryViewController";
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                }
            }
        } else if ($qry[view_controller] == "OrderingViewController") {
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "WebViewController";
            $this_item["URL"] = $PUBLIC_WEB_HOME_URL."/html5/orderhtml5/orderloc.php?app_code=$app_name&tab_id=$qry[id]&controller=OrderingViewController";

        } else if ($qry[view_controller] == "MerchandiseViewController") {
            $this_item_home["ViewController"] = "WebViewController";
            $this_item_home["Main_ViewController"] = "WebViewController";
            //$this_item_home["URL"] = $PUBLIC_WEB_HOME_URL."/app_elem/merchandise/orderloc.php?app_code=$app_name&tab_id=$qry[id]";

            // Lets try to find Fetch
            $store = get_row_common($conn, "mst_storeinfo", array(
                                        "app_id = " => "'$app[id]'",
                                        "tab_id = " => "'$qry[id]'",
                                        "enable_store = " => "1",
            ));
            
            $store = $store[0];
            if ( $store && $store['cart_url'] ) {
                $this_item_home["URL"] = $store['cart_url'];
            } else {
                $this_item["URL"] = $PUBLIC_WEB_HOME_URL."/html5/orderhtml5/orderloc.php?app_code=$app_name&tab_id=$qry[id]&controller=MerchandiseViewController"; 
            }
        }
        
        // Add tab_id to everything now, even though not every view controller may support it
        $this_item_home["tab_id"] = $qry["id"];

        return $this_item_home;
}

////////////////////////////////////////////
// ---------- Init Variables --------------
////////////////////////////////////////////

$M_XTR = new AppXtr($conn);
$app_xtr = $M_XTR->retrieve_by_app($app_id);

$SETTINGS = false;
$TABS = false;
$HOME = false;

$MUSIC_BAR_ON_FRONT = "0";
$MUSIC_BAR_ON_TOP = "1";
$RSS_ICON_EXT = "";
$MESSAGE_TAB_ID = 0;
$MUSIC_TAB_ID = 0;

$social_onboarding = 0;
$google_plus_client_id = '';
$instruction_tab = false;
$show_newsfeed = 'NO';

$defaults_settings = array(
    "NavigationBarColor" => "2F6C94",
    "NavigationTextColor" => "FFFFFF",
    "NavigationTextShadowColor" => "F0FFFC",
    "SectionBarColor" => "A1A1A1",
    "SectionBarTextColor" => "FFFFFF",
    "OddRowColor" => "DEEBFF",
    "EvenRowColor" => "FFFFFF",
    "OddRowTextColor" => "000000",
    "EvenRowTextColor" => "000000",
    "FeatureTextColor" => "000000",
    "ButtonTextColor" => "000000",
    "ButtonBgColor" => "FFFFFF",
);

/////////////////////////////////////////////////
// ---------- Tabs --------------
////////////////////////////////////////////////
if ( $is_active != 1 ) {
    // In active app...
    $TABS[] = array("TabLabel" => "Home",
                    "TabImage" => "icon_home.png",
                    "ViewController" => "InactiveViewController",
                    "NavigationController" => "NO",
                    "LastUpdated" => gmdate("Y-m-d H:i:s"));
} else {
    $sql = "SELECT t.*,
                        vc.value1_field,
                        vc.value2_field,
                        vc.value3_field
        FROM app_tabs t
        LEFT JOIN view_controllers vc
            ON t.view_controller = vc.name
        WHERE t.app_id = '$app_id'
            AND t.is_active > 0
            AND (
			view_controller = 'EventsViewController'
			OR t.view_controller = 'InfoDetailViewController'
			OR t.view_controller = 'InfoItemsViewController'
			OR t.view_controller = 'InfoSectionViewController'
			OR t.view_controller = 'LocationViewController'
			OR t.view_controller = 'WebViewController'
			OR t.view_controller = 'MenuViewController'
			OR t.view_controller = 'MailingListViewController'
			OR t.view_controller = 'StatRecorderViewController'	
			OR t.view_controller = 'FanWallViewController'	
			OR t.view_controller = 'GalleryViewController'
			OR t.view_controller = 'YoutubeViewController'	
			OR t.view_controller = 'RSSFeedViewController'
			OR t.view_controller = 'OrderingViewController'
			OR t.view_controller = 'CustomFormViewController'
			OR t.view_controller = 'MerchandiseViewController'
			OR t.view_controller = 'EventsManagerViewController'
			OR t.view_controller = 'FanWallManagerViewController'
			)
        ORDER BY t.seq";
    $res = mysql_query($sql, $conn);

    if ( mysql_num_rows($res) == 0 ) {  
        // No tabs found...
        // Let's hope this never happens
       $TABS[] = array("tab_label" => "No tabs");
    } else {
        
      while ($qry = mysql_fetch_array($res)) {
        
        // No need to check for membership tab
        if(in_array($qry["view_controller"], array("MembershipManageController", "HomeViewController", "GoogleAdsViewController"))) {
            continue;
        } else if ( $qry["view_controller"] == "MessagesViewController" ) {
            $MESSAGE_TAB_ID = $qry["id"];

            // No need to check for message tab if... value1 == hide
            if ( $qry["value1"] == "hide" ) {
                continue;
            }
        } else if ( $qry["view_controller"] == "MusicViewController" ) {
            $MUSIC_TAB_ID = $qry["id"];
        } else if ( $qry["view_controller"] == "NewsViewController" ) {
            $show_newsfeed = intval($qry['value1']) == 1 ? 'YES' : 'NO';
        }
        
        $skip_array = array(
            "tab_icon_new" 
        );
        
        $more_this_item = "";
        
        $this_item = array("TabLabel" => $qry["tab_label"],
                        "TabImage" => $qry["tab_icon"],
                        "ViewController" => $qry["view_controller"],
                        "NavigationController" => $qry["navigation_controller"] ? "YES" : "NO",
                        "LastUpdated" => date("Y-m-d H:i:s", $qry["last_updated"]));
        
        if ($qry["odd_row_color"])
          $this_item["OddRowColor"] = $qry["odd_row_color"];

        if ($qry["even_row_color"])
          $this_item["EvenRowColor"] = $qry["even_row_color"];

        for ($i=1; $i <= 3; $i++) {
            if (($qry["value{$i}"] != "") && ($qry["value{$i}_field"] != "")){
                $this_item[$qry["value{$i}_field"]] = strval($qry["value{$i}"]);
            }
        }
        
        //Make the restaurant exactly same with Webviews
        if ($qry["view_controller"] == "RestaurantBookingViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item["ViewController"] = "WebViewController";
        }

        if ($qry["view_controller"] == "WuFooViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item["ViewController"] = "WebViewController";
        }
        
        if ($qry["view_controller"] == "PDFViewController") {
            $qry["view_controller"] = "WebViewController";  
            $this_item["ViewController"] = "WebViewController";
        }
        
        if ($qry["view_controller"] == "InfoSectionViewController") {  // This is now redundant for 1-tier; if 2-tier is set it kicks in though
            
            $sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
            $res2 = mysql_query($sql, $conn);
            
            if (mysql_num_rows($res2) == 1) {
                    
                // If only 1 category, then make it to info_items tab
                
                $this_item["ViewController"] = "InfoItemsViewController";
                $this_item["section_id"] = mysql_result($res2, 0, 0);
                
            }
            else {
                $this_item["tab_id"] = $qry["id"];
                
                }
                
            }
        elseif ($qry["view_controller"] == "InfoItemsViewController") {
            $sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
            $res2 = mysql_query($sql, $conn);
            
            if (mysql_num_rows($res2) == 1) {
                    
                $item_cat_row = mysql_fetch_array($res2);
                
                $this_item["section_id"] = $item_cat_row["id"];                

                }
            }
        elseif ($qry["view_controller"] == "InfoDetailViewController") {
                
            $sql = "select i.id, i.description, i.info_category_id, c.tab_id from info_categories c, info_items i
                                where c.id = i.info_category_id 
                                and c.tab_id = '$qry[id]'
                order by c.seq, c.id, i.seq, i.id";
            $res2 = mysql_query($sql, $conn);
            
            if (mysql_num_rows($res2) == 1) {
                    
                $item_row = mysql_fetch_array($res2);
                
                $this_item["item_id"] = $item_row["id"];

                }   
            }
        elseif ($qry["view_controller"] == "LocationViewController") {
            $sql = "select * from app_locations
                where app_id = '$app_id'";
            $res2 = mysql_query($sql, $conn);
            $count_locations = mysql_num_rows($res2);
            if ($count_locations > 1) {
                $this_item["ViewController"] = "LocationListViewController";
            } else if ( $count_locations < 1 ) {
                $this_item["ViewController"] = "LocationListViewController";
            } else {
                $res_loc = mysql_fetch_array($res2);
                $this_item["item_id"] = $res_loc['id'];
                if ( !$res_loc['address_1'] && !$res_loc['latitude'] && !$res_loc['longitude'] ) {
                    $this_item["ViewController"] = "LocationListViewController";
                }
            }
        }
        elseif ($qry["view_controller"] == "WebViewController") {
            $sql = "select * from web_views
                                where app_id = '$app_id'
                                and tab_id = '$qry[id]'
                                order by seq, url";
            $res2 = mysql_query($sql, $conn);
            if (mysql_num_rows($res2) > 1) {
                    $this_item["ViewController"] = "WebTierViewController";
                } else if ( mysql_num_rows($res2) ) {
                    $web_row = mysql_fetch_array($res2);
                    $this_item["URL"] = $web_row["url"];
                    $this_item["URL"] = str_replace('&amp;', '&', $this_item["URL"]);
                    if((strpos($this_item["URL"], "http://") === false) && (strpos($this_item["URL"], "https://") === false)) {
                        $this_item["URL"] = "http://" . $this_item["URL"];
                    }
                
                if($web_row["is_donate"] == "1") {
                    $this_item["openInSafari"] = "YES";
                } else {
                    $this_item["openInSafari"] = "NO";
                }
                
            } else {
                    $this_item["ViewController"] = "WebTierViewController";
                    $this_item["URL"] = "http://www.appsomen.com/";
            }

            if($qry["value1"] == "1") {
                $this_item["showNavigationBar"] = "1";
            } else {
                $this_item["showNavigationBar"] = "0";
            }

        }
        elseif ($qry["view_controller"] == "CustomFormViewController") {
            $sql = "SELECT * FROM form_layout
                                WHERE user_id = '$app_id'
                                AND user_type = '1' 
                                AND tab_id = '$qry[id]'
                                AND is_active = '1' 
                                AND is_removed = '0' 
                                ORDER BY seq";
            $res2 = mysql_query($sql, $conn);
            
            if (mysql_num_rows($res2) > 1) {
                    $this_item["ViewController"] = "WebTierViewController";
                    $this_item["tab_id"] = $qry["id"];
            } else if (mysql_num_rows($res2)) {
                $this_item["ViewController"] = "WebViewController";
                $web_row = mysql_fetch_array($res2);
                
                if($PUBLIC_WEB_HOME_URL == "http://www.appsomen.com") {
                    $this_item["URL"] = $PUBLIC_WEB_HOME_URL . "/client/form_build.php?tk=" . $web_row["tk"];
                } else {
                    $this_item["URL"] = $PUBLIC_WEB_HOME_URL . "/form_build.php?tk=" . $web_row["tk"];
                }
                $this_item["openInSafari"] = "NO";
                
            } else {
                $this_item["ViewController"] = "WebViewController";
                $this_item["URL"] = "http://www.google.com/";
            }
        } elseif ($qry["view_controller"] == "RSSFeedViewController") {
            
            $this_item["tab_id"] = $qry["id"];
            
            // We need to get RSS CUSTOM ICON if user has uploaded it
            if($qry["value3"] != "") $RSS_ICON_EXT = $qry["value3"]; 
            
            $weburl = $qry["value1"];
            if (ereg("youtube.com", $weburl)) {
                $this_item["ViewController"] = "YoutubeViewController";
            }
            

        } else if ($qry["view_controller"] == "HomeViewController") {
            // This tab is discontinued...
        } else if ($qry["view_controller"] == "GalleryCoverFlowViewController") {
            // Okay, just go ahread.
        } else if ($qry["view_controller"] == "GalleryViewController") {
            // Multiple or Single
            $listsql = "select * from galleries where app_id = '$app_id' and tab_id = '$qry[id]'";
            $listres = mysql_query($listsql, $conn);
            $list_count = mysql_num_rows($listres);
            if ( $list_count > 1 ) {
                $this_item["ViewController"] = "GalleryListViewController";
            } else {
                $this_item["ViewController"] = "GalleryViewController";

                $gallery_row = mysql_fetch_array($listres);
                $gallery_info = $gallery_row['info'];
                if ( $gallery_info ) {
                    $gallery_info = unserialize( $gallery_info );
                    if ( $gallery_info['type'] == '1' ) {
                        $this_item["ViewController"] = "GalleryCoverFlowViewController";
                    }
                }                
            }

            $v2 = unserialize($qry['value2']);
            if(!is_array($v2)) $v2 = array();
            
            
            foreach($v2 AS $k => $v) {
                if(($v["active"] == "1") && ($k == "flickr")) {
                    if ( $v["disp"] == "0" ) {
                        if ( $v["gtype"] == "0" ) {
                            $this_item["ViewController"] = "FlickrPhotoStreamGalleryViewController";
                        } else {
                            $this_item["ViewController"] = "FlickrPhotoStreamCoverflowViewController";
                        }
                    } else {
                        $this_item["ViewController"] = "FlickrViewController";
                    }
                } else if(($v["active"] == "1") && ($k == "picasa")) {
                    $this_item["ViewController"] = "PicasaViewController";
                } else if(($v["active"] == "1") && ($k == "instagram")) {
                    $this_item["ViewController"] = "InstagramViewController";
                }
            }
            
            
        } else if ($qry["view_controller"] == "GalleryListViewController") {
            $this_item["ViewController"] = "GalleryListViewController";

            $v2 = unserialize($qry['value2']);
            if(!is_array($v2)) $v2 = array();
            
            
            foreach($v2 AS $k => $v) {
                /*
                $more_this_item_el = array();
                foreach($this_item AS $tk => $tv) {
                    $more_this_item_el[$tk] = $tv;
                }
                */
                if(($v["active"] == "1") && ($k == "flickr")) {
                    if ( $v["disp"] == "0" ) {
                        if ( $v["gtype"] == "0" ) {
                            $this_item["ViewController"] = "FlickrPhotoStreamGalleryViewController";
                        } else {
                            $this_item["ViewController"] = "FlickrPhotoStreamCoverflowViewController";
                        }
                    } else {
                        $this_item["ViewController"] = "FlickrViewController";
                    }
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                } else if(($v["active"] == "1") && ($k == "picasa")) {
                    $this_item["ViewController"] = "PicasaViewController";
                    /*
                    $more_this_item_el["ViewController"] = "FlickrViewController";
                    $more_this_item_el["tab_id"] = $qry["id"];
                    $more_this_item[] = $more_this_item_el;
                    */
                } else if(($v["active"] == "1") && ($k == "instagram")) {
                    $this_item["ViewController"] = "InstagramViewController";
            }
                }
            } else if ( $qry[view_controller] == "OrderingViewController" ) {
                $this_item["ViewController"] = "WebViewController";
                $this_item["URL"] = $PUBLIC_WEB_HOME_URL."/html5/orderhtml5/orderloc.php?app_code=$app_name&tab_id=$qry[id]&controller=OrderingViewController";

                if ( $data["device"] ) {
                    $this_item["URL"] .= "&device=".$data["device"];
                }

                if ( $qry["value1"] == "1" ) {
                    $this_item["showNavigationBar"] = "1";
                } else {
                    $this_item["showNavigationBar"] = "0";
                }

            } else if ( $qry[view_controller] == "MerchandiseViewController" ) {
                $this_item["ViewController"] = "WebViewController";

                // Lets try to find Fetch
                $store = get_row_common(
                    $conn, 
                    "mst_storeinfo", 
                    array(
                        "app_id = " => "'$app[id]'",
                        "tab_id = " => "'$qry[id]'",
                        "enable_store = " => "1",
                    )
                );

                $store = $store[0];
                if ( $store && $store['cart_url'] ) {
                    $this_item["URL"] = $store['cart_url'];
                } else {
                    $this_item["URL"] = $PUBLIC_WEB_HOME_URL."/html5/orderhtml5/orderloc.php?app_code=$app_name&tab_id=$qry[id]&controller=MerchandiseViewController"; 
                }

            if($qry["value1"] == "1") {
                $this_item["showNavigationBar"] = "1";
            } else {
                $this_item["showNavigationBar"] = "0";
            }
        } else if ($qry[view_controller] == "MusicViewController") {
            // Music bar will appear on front?
            if($qry["value2"] == '1') $MUSIC_BAR_ON_FRONT = '1';
            // Music bar will sit off tab buttons?
            if($qry["value3"] == '0') $MUSIC_BAR_ON_TOP = '0';
        } else if ($qry[view_controller] == "SocialViewController") {
            $social_onboarding = $qry['value3'];
            $google_plus_client_id = $qry['value2'];
        } else if ($qry[view_controller] == "InstructionViewController") {
            $instruction_tab = true;
            $instruction_tab_location_checked = intval($qry['value12']);
            $instruction_tab_music_checked = intval($qry['value13']);
            $instruction_tab_general_text = $qry['value1'];
            $instruction_tab_location_text = $qry['value2'];
            $instruction_tab_music_text = $qry['value3'];

            continue;
        }
        
        
        // Add tab_id to everything now, even though not every view controller may support it
        $this_item["tab_id"] = $qry["id"];
        // This is for new Appearance features
            // Not set up tab icon
            if ( $qry["tab_icon_new"] == '{TAB_ICON_NEW}' ) {
                $qry["tab_icon_new"] = '(1).png';
            }
        $this_item["TabImage"] = $qry["tab_icon_new"];
        // Process for old app
        if(($qry["tab_icon_new"] == "") && ($qry["tab_icon"] != "")) {
            $this_item["TabImage"] = "classic_".$qry["tab_icon"];   
        }
        
        // If this is preview app, tab icon should be empty.... so that nothing will be loaded on app...
        if($app_id == "19") $this_item["TabImage"] = "";
        
        // Get Tab specified design
        $tabdesign_details = get_app_tab_design($conn, $app_id, $qry[id]);
        if (!is_array($tabdesign_details) || $tabdesign_details["app_id"] != $app_id) {
          $this_item["custom_design"] = "NO";
        } else {
          $this_item["custom_design"] = "YES";
          foreach($passing_design_details_eachtab AS $key_d => $v_d ) {
            if($v_d != "") {
                $vkey = $v_d;
            } else {
                $vkey = $key_d;
            }

            $value = parseDesignValues($vkey, $tabdesign_details[$vkey], $a);
            $this_item[$key_d] = strval($value);
          }
          //$this_item = lastFilterDesignValues($this_item, $a);
        }
        
        
        // Need to process for some tabs
        if ($this_item["ViewController"] == "YoutubeViewController") {
            
            /*
            include_once "ray_model/tab_xtr.php";
            $M_TabXtr = new TabXtr($conn);
            $tab_xtr = $M_TabXtr->retrieve_by_tab($this_item["tab_id"]);
            
            $this_item["note"] = ($tab_xtr["note"])?$tab_xtr["note"]:"";
            */

        }
        
        /*
        // Now look for page colors
        $pcolor = new PageColors($conn, $app_id);
        $page_color = array("000000", "FFFFFF");
        if ($pcolor->Retrieve($this_item["tab_id"])) {
            $page_color = array($pcolor->BGColor(), $pcolor->FGColor());
        }
        if(!isset($this_item["page_color"])) {
            $this_item["page_color"] = $page_color;
        }                
        */
        
        // Now push this into returning data list
        $TABS[] = $this_item;
        
        // Attach more tabs if found... but forgot why this is here :D    
        if(is_array($more_this_item)) {
            foreach($more_this_item AS $el) {
                $feed[] = $el;  
            }
        }
      }
    }
}

/////////////////////////////////////////////////
// ---------- Processing for others --------------
////////////////////////////////////////////////

if($is_active == 1) {
    
    /////////////////////////////////////////////////
    // ---------- Processing for HOME --------------
    ////////////////////////////////////////////////
    
    
    // Init variables ...     
    $addresses = false;
    $home_sub_tabs = false;
    $link_tabs = false;
    //----------------------------------------------------------------------------
    // Try to find app locations
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
    
    $first_address = false;
    while ($qry = mysql_fetch_array($res)) {
      if (!$first_address) $first_address = $qry;
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
    
    
    
    $HOME = array(
        "only4ipad" => ($app_xtr['only4ipad']=='1')?'1':'0',
    );
                
    if($first_address) {
        $fields = array("name", "telephone", "latitude", "longitude");
        foreach($fields AS $f) {
            $HOME[$f] = ($first_address[$f])?$first_address[$f]:"";
        }
    }
    
    if($addresses !== false) {
        $HOME["addresses"] = $addresses;
    }
        
    //----------------------------------------------------------------------------
    //geting the sub tab records
    //----------------------------------------------------------------------------
    // filter correct subtabs - because some apps has old subtabs for old home tab
    $sql_homsub_filter = "SELECT GROUP_CONCAT(r.tab_id SEPARATOR '\',\'') as tab_ids FROM
                            (SELECT h.tab_id FROM home_sub_tabs h
                                LEFT JOIN app_tabs t ON t.id=h.tab_id and t.app_id=h.app_id
                                WHERE h.app_id='$app_id'
                                AND t.view_controller='HomeViewController'
                                GROUP BY h.tab_id) AS r";
    $res_homesub_filter = mysql_query($sql_homsub_filter, $conn);
    $qry_homesub_filter = mysql_fetch_array($res_homesub_filter);
    $tab_ids = $qry_homesub_filter['tab_ids'];
    if(!empty($tab_ids)) $tab_ids = "'".$tab_ids."'";
    
    if(!empty($tab_ids)) {
		$sql_homesub = "SELECT sub.*, a.tab_label, a.view_controller AS actual_view 
							FROM home_sub_tabs as sub 
							LEFT JOIN app_tabs AS a 
								ON sub.link_tab_id = a.id 
							WHERE 
								sub.app_id = '$app_id' 
								AND sub.is_active<>'0' 
								AND sub.tab_id IN ($tab_ids)
							ORDER BY sub.seq limit 6";

		$res_homesub = mysql_query($sql_homesub, $conn);
		if(mysql_num_rows($res_homesub)>0) {
			
			while ($qry_homesub = mysql_fetch_array($res_homesub)) {
				
				$myArray = $feed_home;
				$searchTerm = $qry_homesub["link_tab_id"]; 
				//$qry_homesub["actual_view"];
				$POS = array_search2d_byfield("tab_id", $searchTerm, $TABS);
				if($POS !== false) {
					$sub = array(
							  "tab_id" => $qry_homesub["link_tab_id"],
							  "link_tab_id" => $qry_homesub["tab_id"],  
							  "is_active" => $qry_homesub["is_active"],
							  "is_hide" => $qry_homesub["is_hide"],
							  "ViewController" => $TABS[$POS]['ViewController'],
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
					$sub = array_merge($TABS[$POS], $sub);
					$home_sub_tabs[] = $sub;    
				}
			}
		
		}
	}

    if($home_sub_tabs !== false) {
        $HOME["home_sub_tabs"] = $home_sub_tabs;
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

    $sql = "SELECT * FROM images_bg 
                WHERE 
                    device_type='0' 
                    AND detail_type='4' 
                    AND detail_id='0' 
                    AND app_id='$app_id' 
                    AND is_removed = 0 
                ORDER BY seq DESC";
    $res = mysql_query($sql, $conn);
    while ($qry = mysql_fetch_array($res)) {
        if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
            $mobile_images[] = array(
                "path" => $dir."/".$qry["name"],
                "url" => $WEB_HOME_URL."/custom_images/".$app_name."/".$qry["name"],
                "device" => "0", 
                "seq" => $qry["seq"],
            );
        }
    }

    //------------------------------------
    // Retrieve background image
    //------------------------------------
    $more_data["tab_id"] = "0";
    $more_data["device"] = "mobile";
    
    $mobile_bg = getBackgroundImageValue($conn, $app_id, $more_data, "0", "../../");
    
    //-----------------------------------
    // Retrive for iPad
    //-----------------------------------
    if ($data['device']!='' && $data['device']=='ipad' && $ipad_bg_set_mode == 0) {
        // Retrieve sliding images
        $ipad_images = array();
        
        $dir = findUploadDirectory($app_id) . "/ipad";
        
        //------------------------------------
        // Retrieve sliding image
        //------------------------------------
        $sql = "SELECT * FROM images_bg 
                    WHERE 
                        device_type='1' 
                        AND detail_type='4' 
                        AND detail_id='0' 
                        AND app_id='$app_id' 
                        AND is_removed = 0 
                    ORDER BY seq DESC";
        $res = mysql_query($sql, $conn);
    
        $mobile_images_trick = array();
        /*foreach($mobile_images AS $mb) {
            $mobile_images_trick[intval($mb["seq"])] = $mb;
        }*/

        while ($qry = mysql_fetch_array($res)) {
            if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {
                $mobile_images_trick[intval($qry["seq"])] = array(
                    "path" => $dir."/".$qry["name"],
                    "url" => $WEB_HOME_URL."/custom_images/".$app_name."/ipad/".$qry["name"],
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

        //------------------------------------
        // Retrieve background image
        //------------------------------------
        $more_data["tab_id"] = "0";
        $more_data["device"] = "ipad";
        $mobile_bg = getBackgroundImageValue($conn, $app_id, $more_data, "0", "../../");
        
    }


    //-----------------------------------
    // Retrive for iPhone5
    //-----------------------------------
    if ($data['device']!='' && $data['device']=='iphone5' && $iphone5_bg_set_mode == 0) {
        // Retrieve sliding images
        
        $dir = findUploadDirectory($app_id) . "/iphone5";

        //Now iphone5 sliding images are disabled due to some confusing of use.
        //------------------------------------
        // Retrieve sliding image
        //------------------------------------
        // $sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='4' AND detail_id='$data[tab_id]' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
        $sql = "SELECT * FROM images_bg 
                    WHERE 
                        device_type='2' 
                        AND detail_type='4' 
                        AND detail_id='0' 
                        AND app_id='$app_id' 
                        AND is_removed = 0 
                    ORDER BY seq DESC";
        $res = mysql_query($sql, $conn);
        
        $mobile_images_trick = array();
        /*foreach($mobile_images AS $mb) {
            $mobile_images_trick[intval($mb["seq"])] = $mb;
        }*/
        
        $iphone5_images = array();

        while ($qry = mysql_fetch_array($res)) {
            if(($qry["name"] != "") && file_exists($dir."/".$qry["name"])) {

                $mobile_images_trick[intval($qry["seq"])] = array(
                    "path" => $dir."/".$qry["name"],
                    "url" => $WEB_HOME_URL."/custom_images/".$app_name."/iphone5/".$qry["name"],
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
        
        // Sliding images could not be inherited from mobile sliding images, so simply replace ...
        $mobile_images = $iphone5_images;
        
        //------------------------------------
        // Retrieve background image
        //------------------------------------
        $more_data["tab_id"] = "0";
        $more_data["device"] = "iphone5";
        $mobile_bg = getBackgroundImageValue($conn, $app_id, $more_data, "0", "../../");

    }
    
    //--------------------------------------------------------
    // Plant background and sliding images into JSON
    //--------------------------------------------------------
    
    $HOME["image"] = $mobile_bg;
    
    if(count($mobile_images) > 0) {
        
        $HOME["manyImages"] = "YES";
        $HOME["imagesInOrder"] = array();
            
        $limit = (count($mobile_images) > 5)? 5:count($mobile_images);
        for($i=0; $i<$limit; $i++) {
            $HOME["imagesInOrder"][] = $mobile_images[$i]["url"];

            //if($_REQUEST['device']=='iphone5') $mobile_images[$i]["device"] = 0; // We just want to take sliding link from iphone image link.
            
            
            $lnk_tabs = getSlidingRelatedTab($conn, $app_id, "0", $mobile_images[$i]["seq"], $mobile_images[$i]["device"]);
            $POS = array_search2d_byfield("tab_id", $lnk_tabs["link_tab_id"], $TABS);
            if($POS !== false) {
                
               $vcontroller=$TABS[$POS]['ViewController'];
               $additional_mapping = array();  
               if($vcontroller == "InfoDetailViewController") {
                    $lnk_tabs["link_detail_id"] = $TABS[$POS]["item_id"];
               } else if($vcontroller == "WebViewController") {
                    $additional_mapping = array(
                        "showNavigationBar" => "showNavigationBar", 
                        "URL" => "URL",
                        "openInSafari" => "openInSafari",
                    );
               }
               
               foreach($additional_mapping AS $k => $v) {
                    if(isset($TABS[$POS][$v])) {
                        $lnk_tabs["more"][$k] = $TABS[$POS][$v];    
                    }
               }
               
            } else {
                $vcontroller = "";
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
            $HOME["linkedTabs"][] = $link_tab_info;
        }
        
    } else {
        $HOME["manyImages"] = "NO";
        $HOME["imagesInOrder"] = array();
    }
    
    
    /////////////////////////////////////////////////
    // ---------- Processing for SETTINGS --------------
    ////////////////////////////////////////////////
    
    // New Design is mandatory
    $design_details = get_app_design_details($conn, $app_id);
    
    // Get RSS ICON URL    
    $rss_icon = '';
    $dir = findUploadDirectory($app_id) . "/rc_rss.$RSS_ICON_EXT";
    if (!file_exists($dir)) {
        $rss_icon = $WEB_HOME_URL."/uploads/icons/rss.png";
    } else {
        $rss_icon = $WEB_HOME_URL.'/uploads/images/' . getUploadRelPath(1, $app_id) . '/rc_rss.' . $RSS_ICON_EXT;
    }
    
    // Message Shortcut Position Filtering
    if ( !in_array($app_xtr["home_layout"], array("1", "2")) ) { // Not modern layout
        if(in_array($design_details["btn_layout"], array("0", "2"))) {
            // This is side bar type layout
            if($design_details["btn_layout"] == "2") {
                // Right sidebar design
                $app_xtr["message_icon_pos_h"] = "0";
            } else {
                $app_xtr["message_icon_pos_h"] = "1";
            }
        } else {
            // This is bottom or top layout
            if($design_details["btn_layout"] == "3") {
                $app_xtr["message_icon_pos_v"] = "0";
                $app_xtr["music_icon_pos_v"] = "0";
            } else {
                $app_xtr["message_icon_pos_v"] = "1";
                $app_xtr["music_icon_pos_v"] = "1";
            }
        }
    } else {
        $app_xtr["message_icon_pos_v"] = "0";
        $app_xtr["music_icon_pos_v"] = "0";
    }
    
    // Check for home sliding image settings:
    $ismodernsliding = intval($app["ismodernmobilesliding"]);
    if(strtolower($data["device"]) == "ipad" && $ipad_bg_set_mode == 0) {
        $ismodernsliding = intval($app["ismodernipadsliding"]);
    } else if(strtolower($data["device"]) == "iphone5" && $iphone5_bg_set_mode == 0) {
        $ismodernsliding = intval($app["ismoderniphone5sliding"]);
    }
    
    $sliding_enabled = intval($app["sliding_type_mobile"]);
    if(strtolower($data["device"]) == "ipad" && $ipad_bg_set_mode == 0) {
        $sliding_enabled = intval($app["sliding_type_ipad"]);
    } else if(strtolower($data["device"]) == "iphone5" && $iphone5_bg_set_mode == 0) {
        $sliding_enabled = intval($app["sliding_type_iphone5"]);
    }
    
    // Global Page Background Color    
    if(!$xtr["global_background_color"]) $xtr["global_background_color"] = "FFFFFF";
    
    // Splash Shots for Mobile
    $splash_images = array();
    if (file_exists("../../uploads/splash_shots/$app_id.png")) {
      $splash_images["iphone"] = $WEB_HOME_URL . "/pull_images/" . $app_id . ".png?extra=splash_shots";  
    }
    // Splash Shots for iPhone 5
    if (file_exists("../../uploads/splash_shots/" . $app_id . "_iphone5.png")) {
        $splash_images["iphone5"] = $WEB_HOME_URL . "/pull_images/" . $app_id . "_iphone5.png?extra=splash_shots";
    }
    // Splash Shots for iPad
    if (file_exists("../../uploads/splash_shots_ipad/$app_id.png")) {
       $splash_images["ipad"] = $WEB_HOME_URL . "/pull_images/" . $app_id . ".png?extra=splash_shots_ipad";
    }
    if(!$data["device"]) $data["device"]= "iphone";
    
    $splash_images_url = "";
    if($splash_images[strtolower($data["device"])]) {
        $splash_images_url = $splash_images[strtolower($data["device"])];
    } else {
        $splash_images_url = $splash_images["iphone"];
    }
        
    $SETTINGS = array(
        "AdWhirlID" => ($app["adwhirl_id"])?$app["adwhirl_id"]:"",
        "AppID" => $app["id"],
        "AppName" => ($app["name"])?$app["name"]:"",
        "appCode" => ($app["code"])?$app["code"]:"",
        "AppStoreURL" => ($app["app_store_url"])?$app["app_store_url"]:"",
        "CallButton" =>($app["m_onbtncall"] == '1')?'YES':'NO', 
        "DirectionButton" => ($app["m_onbtndirection"] == '1')?'YES':'NO',  
        "TellFriendButton" =>($app["m_onbtntell"] == '1')?'YES':'NO',
        "ShowNewsFeed" => $show_newsfeed,
        "NewNav" => "yes",
        "ismodernsliding"=> $ismodernsliding,
        "slidingEnabled" => $sliding_enabled,
        
        "is_protected" => ($app_xtr["is_protected"]=="1")?"1":"0",
        "protected_header_color" => $app_xtr["protected_header_color"],
        "message_icon_on" => ($app_xtr["message_icon_on"]=="1")?"1":"0",
        "message_icon_opacity" => $app_xtr["message_icon_opacity"],
        "message_icon_pos_h" => $app_xtr["message_icon_pos_h"],
        "message_icon_pos_v" => $app_xtr["message_icon_pos_v"],
        "message_icon_linked_tab" => $MESSAGE_TAB_ID,
        "music_icon_on" => ($app_xtr["music_icon_on"]=="1")?"1":"0",
        "music_icon_opacity" => $app_xtr["music_icon_opacity"],
        "music_icon_pos_h" => $app_xtr["music_icon_pos_h"],
        "music_icon_pos_v" => $app_xtr["music_icon_pos_v"],
        "multitasking" => $app_xtr["music_multitasking"],
        "music_icon_linked_tab" => $MUSIC_TAB_ID,
        "with_status_bar" => ($app_xtr["with_status_bar"])?"1":"0",
        "global_background_color" => $app_xtr["global_background_color"],
        "home_layout" => $app_xtr["home_layout"],
          
        "MailingListPrompt" => $app["mailing_list_prompt"],
        "UseTextColors" => 1,
              
        "MusicOnFront" => $MUSIC_BAR_ON_FRONT,
        "MusicOnTop" => $MUSIC_BAR_ON_TOP,
        "RSSIcon" => $rss_icon,
        
        "pushing_address" => "198.57.176.205",
        "ga_property_id" => "AP-38357823-1",
        "ga_property_id_android" => "AP-38357823-3",
        "facebook_api_key" => "124661487570397",
        "consumer_key" => "ibeMh2JAmmQw09B1nfap5Q",
        "consumer_secret" => "dkomjgXm50XtNmWDn0FhJJpswGvdfIPqfYwfxqMar38",
        "google_plus_client_id" => $google_plus_client_id,
        "ba_google_plus_client_id" => "692676472427-3f2jisnlplks8q2a11a902gmu377aiov.apps.googleusercontent.com",
        
        "splash_image" => ($splash_images_url)?$splash_images_url:"",
        
        "moreTabBackgroundForiPad" => "",
        "moreTabBackgroundForiPhone" => "",

        "social_onboarding" => $social_onboarding,
        
        "ads" => array(
            "show_ads" => "",
            "ads_type" => "",
            "admob_pub_id" => "",
            "dfp_unit_id" => ""
        ),
        "timezone" => -6
    );

    if ( $instruction_tab ) {
        $SETTINGS["instruction_tab"] = array(
            "general_text" => $instruction_tab_general_text,
            "location_checked" => $instruction_tab_location_checked,
            "location_text" => $instruction_tab_location_text,
            "music_checked" => $instruction_tab_music_checked,
            "music_text" => $instruction_tab_music_text
        );
    }

    if($app["app_store_id"])
        $SETTINGS["AppStoreID"] = $app["app_store_id"];
    if($app["android_url"])
        $SETTINGS["PlayStoreURL"] = $app["android_url"];
    if($app["html5_url"])
        $SETTINGS["HTML5URL"] = $app["html5_url"];
    
    $sql = "select show_ads from apps_xtr where app_id='".$app["id"]."'";
    $res = mysql_query($sql, $conn);
    $resrow = mysql_fetch_array($res);
    $SETTINGS["ads"]["show_ads"] = intval($resrow["show_ads"]);
    
    $sql = "select value1, value2 from app_tabs where app_id = '" . $app["id"] . "' and view_controller = 'GoogleAdsViewController' order by id desc limit 0, 1";
    $res = mysql_query($sql, $conn);
    if ( mysql_num_rows($res) > 0 ) {
        $resrow = mysql_fetch_array($res);
        if ( $resrow['value1'] ) {
            $SETTINGS["ads"]["ads_type"] = "admob";
            $sql2 = "select adwhirl_id from apps where id = '" . $app["id"] . "'";
            $res2 = mysql_query($sql2, $conn);
            if ( mysql_num_rows($res2) > 0 ) {
                $SETTINGS["ads"]["admob_pub_id"] = mysql_result($res2, 0, 0);
            }
        } else {
            $value2 = $resrow['value2'];
            if ( $value2 != '' ) {
                $value2 = json_decode($value2, true);

                if ( $value2['enabled'] ) {
                    $SETTINGS["ads"]["ads_type"] = "dfp";
                }

                if ( $value2['google_ads_id'] ) {
                    $SETTINGS["ads"]["dfp_unit_id"] = $value2['google_ads_id'];
                }
            }
        }
    }
        
    foreach($passing_design_details AS $key_d => $v_d ) {
        if($v_d != "") {
            $vkey = $v_d;
        } else {
            $vkey = $key_d;
        }
        $value = parseDesignValues($vkey, $design_details[$vkey], $app);
        if(intval($app_xtr["home_layout"]) > 0 && $key_d == "rows") {
            $SETTINGS[$key_d] = 1;
        } else {
            if($value != "") {
                $SETTINGS[$key_d] = strval($value);
            } else if(isset($defaults_settings[$key_d])){
                $SETTINGS[$key_d] = strval($defaults[$key_d]);
            } else {
                $SETTINGS[$key_d] = "";
            }
        }
    }
    $tmp_settings = $SETTINGS;
    //$SETTINGS = lastFilterDesignValues($SETTINGS, $app);
    $SETTINGS["moreTab"] = array();
    
    if($SETTINGS["moreButtonNavigation"] == "YES") {
        $sql = "SELECT * FROM app_more_tabs WHERE app_id=".$app_id;
        $res = mysql_query($sql, $conn);
        if(mysql_num_rows($res) > 0) { // in case of more button info is already set
            $t = mysql_fetch_array($res);
            $t[id] = 0; // trick for more tab buttons setting - to get correct tab template design
        } else { // in case of more button is firstly edited
            $t = array(
                "id" => 0,
                "app_id" => $auth_user["id"],
                "tab_icon" => $SETTINGS["tab_icon"],
                "tab_icon_new" => "(190).png",
                "tab_label" => "More"
            );
        }
        $more_detail_data['TabLabel'] = $t['tab_label'];
        $more_detail_data['TabImage'] = $t['tab_icon_new'];
        $more_detail_data['NavigationController'] = "YES";

        $sql = "SELECT * FROM template_detail td 
            LEFT JOIN template_tab AS tt ON td.id = tt.detail_id
            WHERE tt.app_id='$app_id' AND tt.tab_id=0";
        $res = mysql_query($sql, $conn);
        if(mysql_num_rows($res) > 0) { // Means there is custom design for more button
            $more_detail_data["custom_design"] = "YES";
            $tmp_more_detail_data = mysql_fetch_array($res);
            foreach($passing_design_details_eachtab AS $key_d => $v_d ) {
                if($v_d != "") {
                    $vkey = $v_d;
                } else {
                    $vkey = $key_d;
                }
                $value = parseDesignValues($vkey, $tmp_more_detail_data[$vkey], $app);
                $more_detail_data[$key_d] = strval($value);
                //$more_detail_data = lastFilterDesignValues($more_detail_data, $app);
            }
        } else {
            $more_detail_data["custom_design"] = "NO";
        }
        
        $SETTINGS["moreTab"] = $more_detail_data;
        
        $more_data["tab_id"] = "0";
        $more_data["device"] = "mobile";
        if($data["device"] == "iphone5") $more_data["device"] = "iphone5";
        $iphonebg = getBackgroundImageValue($conn, $app_id, $more_data, "51", "../../");
        
        $SETTINGS["moreTabBackgroundForiPhone"] = $iphonebg;
        
        $more_data["tab_id"] = "0";
        $more_data["device"] = "ipad";
        $ipadbg = getBackgroundImageValue($conn, $app_id, $more_data, "51", "../../");
        
        $SETTINGS["moreTabBackgroundForiPad"] = $ipadbg;

        // Set button rows to 1 on the home screen
        $SETTINGS["rows"] = "1";
	}

    // button navigation is top on the home screen
    if ( $SETTINGS["premium_navigation_position"] == "1" ) {
        $SETTINGS["rows"] = "1";
    }
    if( in_array($app_xtr["home_layout"], array("1", "2"))) { // in case of modern layout
        $SETTINGS["is_background"] = 1;
    }
}

$json = json_encode(
    array(
        array(
            "home" => $HOME,
            "settings" => $SETTINGS,
            "tabs" => $TABS,
        )
    )
);

$json = str_replace(':null', ':""', $json);

header('Content-Type: application/json');
header("Content-encoding: gzip");
echo gzencode($json);
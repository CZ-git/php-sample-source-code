<?php

/* 
 * global_searcg.php
 * Created: 3/17/2014
 * Author: Kelton
 * 
 * 
*/

include_once "dbconnect.inc";
include_once "app.inc";
include_once "common_functions.php";

include_once "ray_model/app_xtr.php";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

$app = get_app_record($conn, $app_id);
////////////////////////////////////////////
// ---------- Init Variables --------------
////////////////////////////////////////////
// request parameters : app_code, keywords
$data = make_data_safe($_REQUEST);

$ITEMS = array();

$sql = "SELECT t.*,
                    vc.value1_field,
                    vc.value2_field,
                    vc.value3_field
    FROM app_tabs t
    LEFT JOIN view_controllers vc
        ON t.view_controller = vc.name
    WHERE t.app_id = '$app_id'
        AND t.is_active > 0
        AND t.tab_label <> ''
    ORDER BY t.seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {  
    $ITEMS[] = array();
} else {
    $tab_items = array();
    $keywords = isset($data['keywords']) ? $data['keywords'] : "";
    
    if(!empty($keywords)) {
        while ($qry = mysql_fetch_array($res)) {
            // No need to check for membership tab
            if(in_array($qry["view_controller"], array("MembershipManageController", "HomeViewController", "GoogleAdsViewController"))) {
                continue;
            }
            
            // for info items
            if ($qry["view_controller"] == "InfoItemsViewController" || $qry["view_controller"] == "InfoSectionViewController") {
                $tab_id = $qry['id'];
                $section_name = "Info";
                $tmp_tab_items = array();
                $cur_tab_items = array();
                
                /*$sql = "SELECT i.*, c.section as cat_section, c.name as cat_name FROM info_categories c, info_items i
                            WHERE c.id = i.info_category_id 
                                    AND c.tab_id = '$tab_id' AND i.section like '%$keywords%'";
                $res2 = mysql_query($sql, $conn);
                
                while($item_qry = mysql_fetch_array($res2)) {
                    if($qry["view_controller"] == "InfoItemsViewController") $section_id = "";
                    else $section_id = $item_qry['info_category_id'];
                    
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "section_id" => $section_id,
                        "thumbnail" => $item_qry['img_thumb'],
                        "content" => $item_qry['section']
                    );
                }*/
                                
                $sql = "SELECT i.*, c.section as cat_section, c.name as cat_name FROM info_categories c, info_items i
                        WHERE c.id = i.info_category_id AND c.tab_id = '$tab_id' AND i.name like '%$keywords%' AND c.is_active = '1' AND i.is_active = '1'";
                $res2 = mysql_query($sql, $conn);
                
                while($item_qry = mysql_fetch_array($res2)) {
                    if($qry["view_controller"] == "InfoItemsViewController") $section_id = "";
                    else $section_id = $item_qry['info_category_id'];

                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "section_id" => $section_id,
                        "thumbnail" => $item_qry['img_thumb'],
                        "content" => $item_qry['name']
                    );
                }

                $sql = "SELECT i.*, c.section as cat_section, c.name as cat_name FROM info_categories c, info_items i
                        WHERE c.id = i.info_category_id AND c.tab_id = '$tab_id' AND c.is_active = '1' AND i.is_active = '1'";
                $res2 = mysql_query($sql, $conn);
                
                while($item_qry = mysql_fetch_array($res2)) {
                    $description = $item_qry['description'];
                    if(strpos($description, "<body") !== false) {
                        preg_match_all ("/<body([^`]*?)>([^`]*?)<\/body>/", $description, $matches);
                        $description = $matches[2][0];

                        //$description = str_replace("\n", "", $description);
                        //$description = str_replace("\r", "", $description);
                        //$description = str_replace("\t", "", $description);
                    }
                    if(strpos(strtolower($description), $keywords) !== false) {
                        if($qry["view_controller"] == "InfoItemsViewController") $section_id = "";
                        else $section_id = $item_qry['info_category_id'];

                        $tmp_tab_items[] = array(
                            "id" => $item_qry['id'],
                            "section_id" => $section_id,
                            "thumbnail" => $item_qry['img_thumb'],
                            "content" => $item_qry['name']
                        );
                    }
                }   
                foreach($tmp_tab_items as $item) {
                    $img_file = findUploadDirectory($app_id, "tier") . "/$item[thumbnail]";
                    if ( $item['thumbnail'] && file_exists($img_file) ) {
                        $product_image = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/'.$item['thumbnail'].'?width=50&height=50&extra=tier';
                    } else {
                        $img_file = findUploadDirectory($app_id, "tier") . "/$item[id].jpg";
                        if (file_exists($img_file)) {
                            $product_image = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/'.$item[id].'.jpg?width=50&height=50&extra=tier';
                        } else {
                            $product_image = $WEB_HOME_URL."/images/theme_editor/no button.png";
                        }
                    }
                    
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => $item['section_id'],
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $product_image,
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry["view_controller"] == "InfoDetailViewController") {
                $tab_id = $qry['id'];
                $section_name = "Info";
                $tmp_tab_items = array();
                $cur_tab_items = array();
                
                $sql = "SELECT i.*, c.section as cat_section, c.name as cat_name FROM info_categories c, info_items i
                        WHERE c.id = i.info_category_id AND c.tab_id = '$tab_id' AND c.is_active = '1' AND i.is_active = '1'";
                $res2 = mysql_query($sql, $conn);
                
                while($item_qry = mysql_fetch_array($res2)) {
                    $description = strip_tags($item_qry['description']);
                    /*if(strpos($description, "<body") !== false) {
                        preg_match_all ("/<body([^`]*?)>([^`]*?)<\/body>/", $description, $matches);
                        $description = strip_tags($matches[2][0]);

                        //$description = str_replace("\n", "", $description);
                        //$description = str_replace("\r", "", $description);
                        //$description = str_replace("\t", "", $description);
                    }*/
                    if(strpos(strtolower($description), $keywords) !== false) {
                        $tmp_tab_items[] = array(
                            "id" => $item_qry['id'],
                            "section_id" => "",
                            "content" => strip_tags($description)
                        );
                    }
                }   
                foreach($tmp_tab_items as $item) {
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => "",
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry["view_controller"] == "LocationViewController") { // for locations
                $tab_id = $qry['id'];
                $section_name = "Location";

                $cur_tab_items = array();
                $tmp_tab_items = array();
                
                // search by address
                $sql = "SELECT * FROM app_locations WHERE app_id='$app_id'";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $address = array();
                    if(!empty($item_qry['address_1']))
                        $address[] = $item_qry['address_1'];
                    if(!empty($item_qry['address_2']))
                        $address[] = $item_qry['address_2'];
                    if(!empty($item_qry['city']))
                        $address[] = $item_qry['city'];
                    if(!empty($item_qry['state']))
                        $address[] = $item_qry['state'];
                    if(!empty($item_qry['zip']))
                        $address[] = $item_qry['zip'];
                    $tmp_address = strtolower(implode(" ", $address));
                    $address = implode(", ", $address);
                    
                    // check address with keyword
                    $keywords = strtolower(str_replace(",", "", $keywords));
                    if(!empty($tmp_address) && strpos($tmp_address, $keywords) !== false) {
                        $tmp_tab_items[] = array(
                            "id" => $item_qry['id'],
                            "thumbnail" => "",
                            "content" => $address
                        );
                    }
                }
                
                foreach($tmp_tab_items as $item) {
                    $img_file = findUploadDirectory($app_id) . "/location/$item[id].jpg";

                    if (file_exists($img_file)) {
                        $img_url = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/location/$item[id].jpg";
                    } else {
                        $img_url = $WEB_HOME_URL."/uploads/icons/contact-tab.png";
                    }

                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $img_url,
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "AroundUsViewController") { // around us tab locations
                $tab_id = $qry['id'];
                $section_name = "Location";
                $tmp_tab_items = array();
                $cur_tab_items = array();
                
                // search by address
                $sql = "SELECT * FROM pois WHERE app_id='$app_id' AND tab_id='$tab_id'";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $address = array();
                    if(!empty($item_qry['name']))
                        $address[] = $item_qry['name'];
                    if(!empty($item_qry['address_1']))
                        $address[] = $item_qry['address_1'];
                    if(!empty($item_qry['address_2']))
                        $address[] = $item_qry['address_2'];
                    if(!empty($item_qry['city']))
                        $address[] = $item_qry['city'];
                    if(!empty($item_qry['state']))
                        $address[] = $item_qry['state'];
                    if(!empty($item_qry['zip']))
                        $address[] = $item_qry['zip'];
                    $tmp_address = strtolower(implode(" ", $address));
                    $address = implode(", ", $address);
                    
                    // check address with keyword
                    $keywords = strtolower(str_replace(",", "", $keywords));   
                    if(!empty($tmp_address) && strpos($tmp_address, $keywords) !== false) {
                        $tmp_tab_items[] = array(
                            "id" => $item_qry['id'],
                            "thumbnail" => "",
                            "content" => $address
                        );
                    }
                }
                
                foreach($tmp_tab_items as $item) {
                    $img_file = findUploadDirectory($app_id) . "/aroundus/$item[id].jpg";

                    if (file_exists($img_file)) {
                        $img_url = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/aroundus/$item[id].jpg";
                    } else {
                        $img_url = $WEB_HOME_URL."/uploads/icons/aroundus.png";
                    }
                    
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $img_url,
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "RealEstateViewController") { // real estate tab locations
                $tab_id = $qry['id'];
                $section_name = "Location";
                $tmp_tab_items = array();
                $cur_tab_items = array();
                
                // search by address
                $sql = "SELECT * FROM realestate_main WHERE app_id='$app_id' AND tab_id='$tab_id'";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $address = array();
                    if(!empty($item_qry['address_1']))
                        $address[] = $item_qry['address_1'];
                    if(!empty($item_qry['address_2']))
                        $address[] = $item_qry['address_2'];
                    if(!empty($item_qry['city']))
                        $address[] = $item_qry['city'];
                    if(!empty($item_qry['state']))
                        $address[] = $item_qry['state'];
                    if(!empty($item_qry['zip']))
                        $address[] = $item_qry['zip'];
                    $tmp_address = strtolower(implode(" ", $address));
                    $address = implode(", ", $address);
                    
                    // check address with keyword
                    $keywords = strtolower(str_replace(",", "", $keywords));
                    if(!empty($tmp_address) && strpos($tmp_address, $keywords) !== false) {
                        $tmp_tab_items[] = array(
                            "id" => $item_qry['rm_id'],
                            "thumbnail" => "",
                            "content" => $address
                        );
                    }
                }
                
                foreach($tmp_tab_items as $item) {
                    $img_file = findUploadDirectory($app_id) . "/realestate/$item[id]/thumb.jpg";

                    if (file_exists($img_file)) {
                        $img_url = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/realestate/$item[id]/thumb.jpg?width=80&height=60";
                        //$img_url = $WEB_HOME_URL."/custom_images/".$data["app_code"]."/realestate/$item[id]/thumb.jpg";
                    } else {
                        $img_url = $WEB_HOME_URL."/images/theme_editor/no button.png";;
                    }
                    
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $img_url,
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "EventsViewController" || $qry['view_controller'] == "EventsManagerViewController") { // event, event v2 tab
                $tab_id = $qry['id'];
                $section_name = "Event";
                
                $cur_tab_items = array();
                $tmp_tab_items = array();
                
                // search by name
                $sql = "SELECT * FROM events WHERE app_id='$app_id' AND tab_id='$tab_id' AND name like '%$keywords%' AND isactive=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "thumbnail" => "",
                        "content" => $item_qry['name']
                    );
                }
                // search by address
                $sql = "SELECT * FROM events WHERE app_id='$app_id' AND tab_id='$tab_id' AND isactive=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $address = array();
                    if(!empty($item_qry['address_1']))
                        $address[] = $item_qry['address_1'];
                    if(!empty($item_qry['address_2']))
                        $address[] = $item_qry['address_2'];
                    if(!empty($item_qry['city']))
                        $address[] = $item_qry['city'];
                    if(!empty($item_qry['state']))
                        $address[] = $item_qry['state'];
                    if(!empty($item_qry['zip']))
                        $address[] = $item_qry['zip'];
                    $tmp_address = strtolower(implode(" ", $address));
                    $address = implode(", ", $address);
                    
                    // check address with keyword
                    $keywords = strtolower(str_replace(",", "", $keywords)); 
                    if(!empty($tmp_address) && strpos($tmp_address, $keywords) !== false) {
                        $tmp_tab_items[] = array(
                            "id" => $item_qry['id'],
                            "thumbnail" => "",
                            "content" => $item_qry['name']
                        );
                    }
                }
                
                foreach($tmp_tab_items as $item) {
                    
                    // Get thumbnail
                    $event_thumb = '';
                    // Event v2
                    if ( $qry['view_controller'] == "EventsManagerViewController" ) {
                        if ( $data['device'] == 'ipad' ) {
                            $header_file = findUploadDirectory($app_id) . "/events/" . $item['id'] . "/header_ipad.jpg";
                            if(file_exists($header_file)) {
                                $event_thumb = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/events/'.$item['id'].'/header_ipad.jpg';
                            }
                        } else {
                            $header_file = findUploadDirectory($app_id) . "/events/" . $item['id'] . "/header.jpg";
                            if(file_exists($header_file)) {
                                $event_thumb = $WEB_HOME_URL."/custom_images/".$data['app_code'].'/events/'.$item['id'].'/header.jpg';
                            }
                        }
                    // Event v1
                    } else {
                        $image_file = findUploadDirectory($app_id, "events") . "/" . $item['id'] . ".jpg";
                        if ( file_exists($image_file) ) {
                            $event_thumb = $WEB_HOME_URL."/custom_images/".$data['app_code']."/" . $item['id'] . ".jpg?extra=events";
                        }
                    }                    
                    
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $event_thumb,
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "GalleryViewController" || $qry['view_controller'] == "GalleryListViewController") { // Photos
                $tab_id = $qry['id'];
                $section_name = "Photo";

                $cur_tab_items = array();
                $tmp_tab_items = array();

                $keywords = strtolower($keywords);
                $sql = "SELECT * FROM gallery_images WHERE app_id='$app_id' AND tab_id='$tab_id'";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    // filter list item with keywords
                    $description = strip_tags($item_qry['info']);
                    /*if(strpos($description, "<body") !== false) {
                        preg_match_all ("/<body([^`]*?)>([^`]*?)<\/body>/", $description, $matches);
                        $description = $matches[2][0];

                        //$description = str_replace("\n", "", $description);
                        //$description = str_replace("\r", "", $description);
                        //$description = str_replace("\t", "", $description);
                    }*/
                    if(strpos($description, $keywords) !== false) {
                        
                        $filename = $item_qry['id'];
                        if ( $item_qry['ext'] )
                            $filename .= '.' . $item_qry['ext'];
                        
                        $gal_image = '';
                        $dir = findUploadDirectory($app_id);                                                                       
                        if ( file_exists($dir . "/gallery/" . $filename) ) {
                            $gal_image = $WEB_HOME_URL . '/gallery_thumbnails/' . $filename . '?width=100&height=100';
                        }                        
                        
                        if ( $gal_image ) {
                            $tmp_tab_items[] = array(
                                "id" => $item_qry['id'],
                                "section_id" => $item_qry['list_id'],
                                "content" => $description,
                                "thumbnail" => $gal_image
                            );
                        }
                    }
                }                
                
                foreach($tmp_tab_items as $item) {
                    // get thumbnail
                    
                    $galsql = "select * from galleries where id = '$item[section_id]' and app_id = '$app_id'";
                    $galres = mysql_query($galsql, $conn);
                    $galinfo = mysql_fetch_array($galres);
                    
                    /*
                    $filename = $galinfo['id'];
                    if ( $galinfo['ext'] )
                        $filename .= '.' . $galinfo['ext'];
                    
                    $gal_image = '';
                    $dir = findUploadDirectory($app_id, "gallery_list");                                                                       
                    if ( file_exists($dir."/".$filename) ) {
                        $gal_image = $WEB_HOME_URL.'/custom_images/'.$data['app_code'].'/'.$filename.'?width=50&extra=gallery_list';
                    }
                    */
                    
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => $item['section_id'],
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $item['thumbnail'],
                        "content" => $item['content']
                    );                    
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "MusicViewController") { // Musics
                $tab_id = $qry['id'];
                $section_name = "Music";
                
                $cur_tab_items = array();
                $tmp_tab_items = array();
                // first search from artist
                $sql = "SELECT * FROM music_detail WHERE app_id='$app_id' AND artist like '%$keywords%' AND is_active=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "album_art" => $item_qry['album_art'],
                        "content" => $item_qry['title'],
                        "track" => $item_qry['track'],
                        "is_for_android" => $item_qry['is_for_android']
                    );
                }
                // second search from title
                $sql = "SELECT * FROM music_detail WHERE app_id='$app_id' AND title like '%$keywords%' AND is_active=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "album_art" => $item_qry['album_art'],
                        "content" => $item_qry['title'],
                        "track" => $item_qry['track'],
                        "is_for_android" => $item_qry['is_for_android']
                    );
                }
                // third search from album
                $sql = "SELECT * FROM music_detail WHERE app_id='$app_id' AND album like '%$keywords%' AND is_active=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "album_art" => $item_qry['album_art'],
                        "content" => $item_qry['title'],
                        "track" => $item_qry['track'],
                        "is_for_android" => $item_qry['is_for_android']
                    );
                }
                
                foreach($tmp_tab_items as $item) {
                    if ( $data['device'] == 'android' ) {
                        if( !ereg("api.7digital.com", $item["track"]) ) {
                            if ( intval($item['is_for_android']) ) {
                                $filenames = split( '[.]', strtolower( $item["track"] ) );
                                if($filenames[count($filenames) - 1] != 'mp3') {
                                    if ( !preg_match("/stream/i", $item["track"]) )
                                        continue;
                                }
                            } else {
                                continue;
                            }
                        }
                    } else {
                        if( ereg("api.7digital.com", $item["track"]) ) {
                            continue;
                        }
                    }
                    
                    if(preg_match("/^http:\/\/(.*)$/", $item["album_art"]) || preg_match("/^http:\/\/(.*)$/", $item["album_art"])) {
                        $album_art = $item["album_art"];    
                    } else {
                        $dir = findUploadDirectory($app_id)."/album_art/".$item["album_art"];
                        if(file_exists($dir) && !is_dir($dir)) {
                            $album_art = $WEB_HOME_URL . '/custom_images/'.$data[app_code].'/album_art/'.$item["album_art"];
                        } else {
                            $album_art = "";
                        }
                    }

                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $album_art,
                        "content" => $item['content']
                    );
                }
                
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);
                
                // remove duplicated items
                $cur_tab_items = UniqueTabItems($cur_tab_items);
                
                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "CouponsViewController") { // GPS coupons - For special
                $tab_id = $qry['id'];
                $section_name = "Special";

                $cur_tab_items = array();
                $sql = "SELECT * FROM coupons WHERE app_id='$app_id' AND name like '%$keywords%' AND is_active=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    if ( $item_qry["end_date"] ) {
                        if ( gmdate("Y-m-d") > gmdate("Y-m-d", $item_qry["end_date"]) ) {
                            continue;
                        }
                    }
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item_qry['id'],
                        "section" => $section_name,
                        "thumbnail" => "",
                        "content" => $item_qry['name']
                    );
                }
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);

                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "QRCouponViewController") { // QR coupons - For special
                $tab_id = $qry['id'];
                $section_name = "Special";

                $cur_tab_items = array();
                $sql = "SELECT * FROM qr_coupons WHERE app_id='$app_id' AND name like '%$keywords%' AND is_active=1";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    if ( $item_qry["end_date"] ) {
                        if ( gmdate("Y-m-d") > gmdate("Y-m-d", $item_qry["end_date"]) ) {
                            continue;
                        }
                    }
                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item_qry['id'],
                        "section" => $section_name,
                        "thumbnail" => "",
                        "content" => $item_qry['name']
                    );
                }
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);

                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "LoyaltyTabViewController") { // Loyalties - For special
                $tab_id = $qry['id'];
                $section_name = "Special";
                
                $cur_tab_items = array();
                $tmp_tab_items = array();
                
                $sql = "SELECT * FROM loyalty WHERE app_id='$app_id' AND reward_text like '%$keywords%'";
                $res2 = mysql_query($sql, $conn);
                while($item_qry = mysql_fetch_array($res2)) {
                    $tmp_tab_items[] = array(
                        "id" => $item_qry['id'],
                        "thumbnail" => $item_qry['img_thumb'],
                        "content" => $item_qry['reward_text']
                    );
                }
                
                foreach($tmp_tab_items as $item) {
                    $img_file = findUploadDirectory($app_id) . "/loyalty/$item[thumbnail]";
                    $img_url = "/custom_images/".$data["app_code"]."/loyalty/$item[thumbnail]";

                    if ( $item['thumbnail'] && file_exists($img_file) ) {
                        $thumb = $WEB_HOME_URL . $img_url . '?width=50&height=50';
                        //$thumb = $WEB_HOME_URL . $img_url;
                    } else {
                        if ( file_exists( findUploadDirectory($app_id) . "/loyalty/$item[id].jpg" ) ) {
                            $img_url = "/custom_images/".$data["app_code"]."/loyalty/$item[id].jpg";
                            $thumb = $WEB_HOME_URL . $img_url . '?width=50&height=50';
                            //$thumb = $WEB_HOME_URL . $img_url;
                        } else {
                            $thumb = $WEB_HOME_URL . "/images/theme_editor/no button.png";
                        }
                    }

                    $cur_tab_items[] = array(
                        "tab_id" => $tab_id,
                        "section_id" => "",
                        "item_id" => $item['id'],
                        "section" => $section_name,
                        "thumbnail" => $thumb,
                        "content" => $item['content']
                    );
                }
                // order list item by similarity
                $cur_tab_items = OrderArraybySilimarity($cur_tab_items, $keywords, "content", 1);

                if(is_array($cur_tab_items) && count($cur_tab_items) > 0)
                    $ITEMS = array_merge($ITEMS, $cur_tab_items);
                    
            } elseif ($qry['view_controller'] == "NewsViewController") { // News tab - news for Google, Twitter, Facebook
                $tab_id = $qry['id'];
                $section_name = "News";
                $keywords = strtolower($keywords);
                
                // search from google
                $news = simplexml_load_file('https://news.google.com/news/feeds?q='.$keywords.'&output=rss&scoring=n&num=20');
                $google_feeds = array();

                $i = 0;
                foreach ($news->channel->item as $item) {
                    preg_match('@src="([^"]+)"@', $item->description, $match);
                    $parts = explode('<font size="-1">', $item->description);

                    $thumbnail = isset($match[1]) ? $match[1] : "";
                    if(strpos($thumbnail, "http") !== 0 && !empty($thumbnail)) {
                        $thumbnail = "http:".$thumbnail;
                    }
                    
                    $google_feeds[$i]['tab_id'] = $tab_id;
                    $google_feeds[$i]['section'] = $section_name;
                    $google_feeds[$i]['news_type'] = "google";
                    $google_feeds[$i]['title'] = (string)$item->title;
                    $google_feeds[$i]['content'] = strip_tags($parts[2]);
                    $google_feeds[$i]['thumbnail'] = $thumbnail;
                    $google_feeds[$i]['pubdate'] = (string) $item->pubDate;
                    $link = (string)$item->link;
                    $pos = strpos($link, "url=");
                    if($pos !== false) {
                        $link = substr($link, $pos+4);
                    }
                    $google_feeds[$i]['link'] = $link;
                    //$feeds[$i]['site_title'] = strip_tags($parts[1]);
                    $i++;
                }
                if(is_array($google_feeds) && count($google_feeds) > 0)
                    $ITEMS = array_merge($ITEMS, $google_feeds);
                
                // search from twitter
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/oauth2/token');
                curl_setopt($ch,CURLOPT_POST, true);
                $postdata = array();
                $postdata['grant_type'] = "client_credentials";
                curl_setopt($ch,CURLOPT_POSTFIELDS, $postdata);
                $consumerKey = "qolUbg4SfHasYhv6BJQZ8A";
                $consumerSecret = "8tnPC3Ho3kmWn3v43N0w5humVLoHqsBlFeeUeOuY0";
                curl_setopt($ch,CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false); // for local
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); // for local
                $result = curl_exec($ch);
                curl_close($ch);
                //print_r($result);
                $result = json_decode($result);
                // show the result, including the bearer token (or you could parse it and stick it in a DB)       
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL, 'https://api.twitter.com/1.1/search/tweets.json?q='.$keywords.'&result_type=recent&app=20');
                $bearer = $result->access_token; 
                curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: Bearer ' . $bearer));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false); // for local
                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); // for local
                $result1 = curl_exec($ch);
                curl_close($ch);
                $tweets = json_decode($result1);                

                $i = 0;
                $twitter_feeds = array();
                foreach($tweets->statuses as $info) {
                    $twitter_feeds[$i]['tab_id'] = $tab_id;
                    $twitter_feeds[$i]['section'] = $section_name;
                    $twitter_feeds[$i]['news_type'] = "twitter";
                    $twitter_feeds[$i]['pubdate'] = (string) $info->created_at;
                    //$twitter_feeds[$i]['link'] = (string) $info->source;
                    
                    $twitter_feeds[$i]['user_id'] = (string) $info->user->id;
                    $twitter_feeds[$i]['user_name'] = (string) $info->user->name;
                    $twitter_feeds[$i]['user_screenname'] = (string) $info->user->screen_name;
                    $twitter_feeds[$i]['user_profile'] = (string) $info->user->profile_image_url;
                    $twitter_feeds[$i]['content'] = (string)$info->text;
                    $i++;
                }
                if(is_array($twitter_feeds) && count($twitter_feeds) > 0)
                    $ITEMS = array_merge($ITEMS, $twitter_feeds);
                // search from facebook
                
            }
        }
    }
}

$json = json_encode($ITEMS);
header("Content-encoding: gzip");
echo gzencode($json);

/**
* Re-order array by similaraity value between array item and keyword indicated as field & keyword param 
* 
* @param array $array       : array to be ordered
* @param string $keywords   : keyword to check similarity
* @param string $field      : field to adapt similarity algorithm
* @param int $order         : order type : 1 => DESC, 0 => ASC
*/
function OrderArraybySilimarity($array, $keywords, $field, $order) {
    $sortindexes = array();
    $order_type = ($order == 1) ? SORT_DESC : SORT_ASC;
    
    foreach($array as $key=>$item) {
        $similarity = similar_text($item[$field], $keywords);
        //$similarity = string_compare($item[$field], $keywords);
        $sortindexes[$key] = $similarity;
        //$array[$key]['similarity'] = $similarity; // for test
    }
    array_multisort($sortindexes, $order_type, $array);
    return $array;
}

/** 
* Unique array
* 
* @param array $array
*/
function UniqueTabItems($array) {
    // first construct index item array
    $sub_array = array();
    foreach($array as $key=>$value) {
        $sub_array[$key] = $value['item_id'];
    }
    foreach($sub_array as $key=>$item) {
        $dupes = array_keys($sub_array, $item);
        unset($dupes[0]);
        foreach($dupes as $rmv) {
            unset($array[$rmv]);
        }            
    }
    return $array;    
}

function string_compare($str_a, $str_b) {
    $length = strlen($str_a);
    $length_b = strlen($str_b);

    $i = 0;
    $segmentcount = 0;
    $segmentsinfo = array();
    $segment = '';
    while ($i < $length) {
        $char = substr($str_a, $i, 1);
        if (strpos($str_b, $char) !== FALSE) {               
            $segment = $segment.$char;
            if (strpos($str_b, $segment) !== FALSE) {
                $segmentpos_a = $i - strlen($segment) + 1;
                $segmentpos_b = strpos($str_b, $segment);
                $positiondiff = abs($segmentpos_a - $segmentpos_b);
                $posfactor = ($length - $positiondiff) / $length_b; // <-- ?
                $lengthfactor = strlen($segment)/$length;
                $segmentsinfo[$segmentcount] = array( 'segment' => $segment, 'score' => ($posfactor * $lengthfactor));
            } else {
                $segment = '';
                $i--;
                $segmentcount++;
            } 
        } else {
            $segment = '';
            $segmentcount++;
        }
        $i++;
    }   

    // PHP 5.3 lambda in array_map      
    $totalscore = array_sum(array_map(score, $segmentsinfo));
    return $totalscore;     
}

function score($v) {
    return $v['score'];
}
?>
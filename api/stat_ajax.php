<?php

include_once("dbconnect.inc");
include_once("geo_info.php");

include_once("common_functions.php");
include_once("app_functions.php");
include_once("ray_model/model.php");

$feed = array(
    "error" => "0",
    "data" => false,
);

$data = make_data_safe($_REQUEST);

$error_found = false;

if(!check_token($conn, $data["id"], $data["tk"])) {
  $feed["error"] = "9";
  $error_found = true;
}

$data["app_id"] = $data["id"];

if(!$error_found) {
    include_once("../../whitelabel/google_app_analytics.php");
    if (!$google_client->getAccessToken()) {
        $feed["error"] = "2";
        $error_found = true;
    }
}

function get_item_label($conn, $tab_view, $item_id, $APP_ID) {
    $mapper = array(
        "MenuViewController" => array(
            "menu_items", "name", ""
        ),
        "CouponsViewController" => array(
            "coupons", "name", ""
        ),
        "LocationViewController" => array(
            "app_locations", "address_1", "website"
        ),
        "EventsViewController" => array(
            "events", "name", ""
        ),
        "EventsManagerViewController" => array(
            "events", "name", ""
        ),
        "WebViewController" => array(
            "web_views", "name", "url"
        ),
        "WuFooViewController" => array(
            "web_views", "name", "url"
        ),
        "PDFViewController" => array(
            "web_views", "name", "url"
        ),
        "InfoSectionViewController" => array(
            "info_items", "name", ""
        ),
        "InfoItemsViewController" => array(
            "info_items", "name", ""
        ),
        "InfoDetailViewController" => array(
            "info_items", "name", ""
        ),
        "QRCouponViewController" => array(
            "qr_coupons", "name", ""
        ),
        "GalleryViewController" => array(
            "gallery_images", "ext", ""
        ),
        "GalleryListViewController" => array(
            "gallery_images", "ext", ""
        ),
        "AroundUsViewController" => array(
            "pois", "name", ""
        ),
        "LoyaltyTabViewController" => array(
            "loyalty", "reward_text", ""
        ),
        "CallUsViewController" => array(
            "callus", "title", "phone"
        ),
        "DirectionViewController" => array(
            "direction", "title", "address_1"
        ),
        "MusicViewController" => array(
            "music_detail", "title", "track"
        ),
        "ReservationViewController" => array(
            "service_item", "service_name", ""
        ),
        "CustomFormViewController" => array(
            "form_layout", "form_title", ""
        ),
    );
    
    if(key_exists($tab_view, $mapper)) {
        
        $M = new Model($conn);
        $item_raw = $M->search($mapper[$tab_view][0], array("id" => $item_id), "first");
        
        $result = array("", $item_id);
        
        if($item_raw) {
            
            $result[0] = $item_raw[$mapper[$tab_view][1]];

            if(isset($item_raw[$mapper[$tab_view][2]])) {
                $result[1] = $item_raw[$mapper[$tab_view][2]];    
            } else {
                $result[1] = "";
            }
            
            if($tab_view == "GalleryViewController") {
                $file_name = $item_id . "." . $item_raw["ext"];
                
                $result[0] = $file_name;
                $result[1] = '<img src="/gallery_thumbnails/' . $file_name . '?&amp;width=100" width="100" height="100" alt="Image 1" border="0" style="padding: 5px;">';
            } else if($tab_view == "MusicViewController") {
                $track = "";
                if(preg_match("/^http:\/\/(.*)$/", $item_raw["track"]) || preg_match("/^http:\/\/(.*)$/", $item_raw["track"])) {
                    $track = $item_raw["track"];
                } else {
                    $dir = findUploadDirectory($APP_ID, "tracks") . "/" . $item_raw["track"];
                    if(file_exists($dir) && !is_dir($dir)) {
                        $track = $WEB_HOME_URL."/uploads/tracks/" . getUploadRelPath(1, $APP_ID) . "/" . $item_raw["track"];
                    }
                }
                $result[1] = '<a href="' . $track . '" target="_blank">Preview Track</a>';
            }  else if($tab_view == "CustomFormViewController") {
                $base_url = $WEB_HOME_URL;
                if ( $base_url == "http://www.appsomen.com" ) {
                    $base_url  = "http://www.appsomen.com/client";
                }
                $form_url = "/form_build.php?tk=".$item_raw["tk"];
                
                $result[1] = '<a href="' . $form_url . '" target="_blank">Preview Form</a>';
            }    
        }
        
        return $result;
        
    } else {
        return array("", $item_id);
    }
}

if(!$error_found) {

    $app = get_app_record($conn, $data["app_id"]);
    $APP_ID = $app["id"];

    $REPORT_TYPE = $data['type']; 
    
    if(!$data["start"]) {
        if($REPORT_TYPE == "1") {
            // If no start/end date specified, then should try for last 6 months
            $end = gmdate("Y-m-d");
            $start = date("Y-m-", strtotime('-6 month')) . "01";    
        } else if($REPORT_TYPE == "2") {
            $end = gmdate("Y-m-d");
            $start = gmdate("Y") . "-01-01";
        } else {
            // If no start/end date specified, then should try for last 30 days
            $end = gmdate("Y-m-d");
            $start = date("Y-m-d", strtotime('-6 day'));
        }
    } else {
        if($REPORT_TYPE == "1") {
            $start = date("Y-m-", strtotime($data["start"])) . "01";
            $end = date("Y-m-d", strtotime(date("Y-m-d", strtotime($end . '+1 month')) . "-1 day"));    
        } else if($REPORT_TYPE == "2") {
            $start = gmdate("Y") . "-01-01";
            $end = date("Y", strtotime($data["end"])) . "-12-31";
        } else {
            $start = date("Y-m-d", strtotime($data["start"]));
            $end = date("Y-m-d", strtotime($data["end"]));
        }
    }
    
    // Store parameters into session
    if($REPORT_TYPE == "0") {
        $_SESSION["stat_opt"] = json_encode(array(
            "start" => $start,
            "end" => $end,
            "type" => $REPORT_TYPE, 
        ));    
    }
    
    // Should compose date array
    // This array will be used in some cases - case-2, case-4
    $helper_dateset = array();
    
    $t_f = $start;
    while($t_f <= $end) {
        $date_label = $t_f;
        
        if($REPORT_TYPE == "2") {
            $date_label = "Year " . date("Y", strtotime($t_f));     
        } else if($REPORT_TYPE == "1") {
            $date_label = date("Y-m", strtotime($t_f));
        }
        
        $helper_dateset[$date_label] = array(
            "ios" => array(0,0,0,0),
            "android" => array(0,0,0,0),
            "html5" => array(0,0,0,0),
        );
        
        if($REPORT_TYPE == "2") {
            $t_f = date("Y-m-d", strtotime($t_f . " + 1 year"));    
        } else if($REPORT_TYPE == "1") {
            $t_f = date("Y-m-d", strtotime($t_f . " + 1 month"));
        } else {
            $t_f = date("Y-m-d", strtotime($t_f . " + 1 day"));
        }
    }
    
    // Google analytics Property ID and profile ID
    $analytics = new Google_AnalyticsService($google_client);
    $ga_webproperty_id = "AP-38357823-1";
    $ga_profile_id = "68908318";
    $ga_webproperty_id_html5 = "AP-40842086-1";
    $ga_profile_id_html5 = "72312815";
    
    // action should be specified
    if($data["action"] == "1") {
        // -------------------------------------------------
        // Aanalytics Dashboard...
        // start, end date should specified
        // start/end should be in format xxxx-xx-xx
        // -------------------------------------------------
        
        // -------------------------------------
        // Get Visitor's Stat Information
        // -------------------------------------
        
        // ------------------------------
        // iOS/Android Analytics - Visitor's Stat
        // ------------------------------
        $optParams = array(
            'dimensions' => 'ga:userType, ga:operatingSystem, ga:country',
            'filters' => 'ga:dimension1==' . $APP_ID,
        );

        if ( $data['apptype'] ) {
            if ( $data['apptype'] == 1 ) { // Live Apps
                $optParams['filters'] .= ';ga:dimension4==live';
            } else if ( $data['apptype'] == 2 ) { // Preview Apps
                $optParams['filters'] .= ';ga:dimension4==appsomen,ga:dimension4==previewapp11';
            }
        }
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
            'ga:users'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        
        $v_data = array(
            "new" => array(0, 0, 0),
            "all" => array(0, 0, 0),
        );
        
        $c_data = array();
        
        foreach ($stats as $st) {
            $vist_type = strtolower($st[0]); 
            $ops = strtolower($st[1]);
            $state = trim($st[2]);
            $st_val = intval($st[3]);
            
            if($vist_type == "new visitor") {
                if($ops == "ios") {
                    $v_data["new"][0] += $st_val;        
                } else if($ops == "android") {
                    $v_data["new"][1] += $st_val;
                }
            }
            
            if($state != "") {
                if(!isset($c_data[$state])) {
                    $c_data[$state] = array(0, 0, 0);
                }
            }
            
            if($ops == "ios") {
                $v_data["all"][0] += $st_val;
                if($state != "") {
                    $c_data[$state][0] += $st_val;
                }        
            } else if($ops == "android") {
                $v_data["all"][1] += $st_val;
                if($state != "") {
                    $c_data[$state][1] += $st_val;
                }   
                }
        }
        
        // ------------------------------
        // HTML5 Analytics - Visitor's Stat
        // ------------------------------
        
        // Get Visitor's Stat Information
        $optParams_html5 = array(
            'dimensions' => 'ga:userType, ga:country',
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
            'ga:users'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        foreach ($stats_html5 as $st) {
            $vist_type = strtolower($st[0]); 
            $state = trim($st[1]);
            $st_val = intval($st[2]);
            
            if($vist_type == "new visitor") {
                $v_data["new"][2] += $st_val;
            }
            
            if($state != "") {
                if(!isset($c_data[$state])) {
                    $c_data[$state] = array(0, 0, 0);
                }
            }
            
            $v_data["all"][2] += $st_val;
            if($state != "") {
                $c_data[$state][2] += $st_val;
            }
        }
        
        // --------------------------------------------------------------------------
        // Filtering Country Data, so that it could be easily accepted by View Part
        // --------------------------------------------------------------------------
        $c_data_ios = array();
        $c_data_android = array();
        $c_data_html5 = array();
        foreach($c_data AS $k => $v) {
            if($v[0] > 0) {
                $c_data_ios[] = array($k, $v[0]);
            }
            
            if($v[1] > 0) {
                $c_data_android[] = array($k, $v[1]);
            }
            
            if($v[2] > 0) {
                $c_data_html5[] = array($k, $v[2]);
            }
        }
        
        // -------------------------------------
        // Get Tab Stat
        // -------------------------------------
        $M = new Model($conn);
        $tabs_raw = $M->search("app_tabs", array("app_id" => $APP_ID, "is_active" => 1), "all", " ORDER BY seq");
        
        $tabs = false;
        $t_data_helper = false;
        $t_data_seq = false;
        
        foreach($tabs_raw AS $t) {
            if(!empty($t["tab_label"])) {
                $tabs[$t["id"]] = $t;
                $t_data_helper[$t["id"]] = array(0,0,0,$t["tab_label"]);
                $t_data_seq[] = $t["id"];
            }
        }
        
        $t_data = array();
        
        // ------------------------------
        // iOS/Android Analytics - Tab Stat
        // ------------------------------
        $optParams = array(
            'dimensions' => 'ga:eventCategory, ga:operatingSystem',
          'sort' => '-ga:totalEvents',
          'filters' => 'ga:dimension1==' . $APP_ID . ';ga:eventAction==0;ga:eventLabel==0',
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        
        foreach($stats AS $st) {
            $tab_id = strtolower($st[0]); 
            $ops = strtolower($st[1]);
            $st_val = intval($st[2]);
            
            if($tabs[$tab_id]) {
                
                if($ops == "ios") {
                    $t_data_helper[$tab_id][0] = $st_val;        
                } else if($ops == "android") {
                    $t_data_helper[$tab_id][1] = $st_val;
                }
            }
        }
        
        // ------------------------------
        // HTML5 Analytics - Tab Stat
        // ------------------------------
        
        $optParams_html5 = array(
            'dimensions' => 'ga:eventCategory',
            'sort' => '-ga:totalEvents',
            'filters' => 'ga:dimension1==' . $APP_ID . ';ga:eventAction==0;ga:eventLabel==0',
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        
        foreach($stats_html5 AS $st) {
            $tab_id = strtolower($st[0]); 
            $st_val = intval($st[1]);
            
            if($tabs[$tab_id]) {
                $t_data_helper[$tab_id][2] = $st_val;
            }
        }
        
        // ------------------------------
        // Collecting All Stat data
        // ------------------------------
        foreach($t_data_seq AS $seq) {
            $t_data[] = $t_data_helper[$seq];
        }
        
        $feed["data"] = array(
            "visits" => $v_data,
            "tabs" => $t_data,
            "geo" => array(
                "ios" => $c_data_ios,
                "android" => $c_data_android,
                "html5" => $c_data_html5,
            )
        );
        
    } else if($data["action"] == "2") {
        
        // -------------------------------------------------
        // Aanalytics about Users ...
        // start, end date should specified
        // start/end should be in format xxxx-xx-xx
        // -------------------------------------------------
        
        $v_data = array(
            "ios" => array(),
            "android" => array(),
            "html5" => array(),
        );
        
        $date_dimension = ", ga:date";
        if($REPORT_TYPE == "2") {
            $date_dimension = ", ga:year";
        } else if($REPORT_TYPE == "1") {
            $date_dimension = ", ga:year, ga:month";
        }
        
        // ----------------------------------------------------
        // Get Visitor's Stat Information by Date
        // ----------------------------------------------------
        // ------------------------------
        // iOS/Android Analytics - User Stat
        // ------------------------------
        
        $optParams = array(
            'dimensions' => 'ga:userType, ga:operatingSystem' . $date_dimension,
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
            'ga:users'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        
        foreach ($stats as $st) {
            $vist_type = strtolower($st[0]); 
            $ops = strtolower($st[1]);
            $st_val = intval($st[3]);
            
            if ( $REPORT_TYPE == "1" ) {
                $st_date = trim($st[2]) . "-" . trim($st[3]);
            $st_val = intval($st[4]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[2]);
            } else {
                $st_date_raw = trim($st[2]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                if($vist_type == "new visitor") {
                    if($ops == "ios") {
                        $helper_dateset[$st_date]["ios"][0] += $st_val;         
                    } else if($ops == "android") {
                        $helper_dateset[$st_date]["android"][0] += $st_val;
                    }
                }

                    if($ops == "ios") {
                        $helper_dateset[$st_date]["ios"][1] += $st_val;         
                    } else if($ops == "android") {
                        $helper_dateset[$st_date]["android"][1] += $st_val;
                }
            }
        }

        // ------------------------------
        // HTML5 Analytics - User Stat
        // ------------------------------
        $optParams_html5 = array(
            'dimensions' => 'ga:userType' . $date_dimension,
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
            'ga:users'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        
        foreach ($stats_html5 as $st) {
            $vist_type = strtolower($st[0]); 
            $st_val = intval($st[2]);
            
            if($REPORT_TYPE == "1") {
                $st_date = trim($st[1]) . "-" . trim($st[2]);
                $st_val = intval($st[3]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[1]);
            } else {
                $st_date_raw = trim($st[1]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                if($vist_type == "new visitor") {
                    $helper_dateset[$st_date]["html5"][0] += $st_val;
                }
                $helper_dateset[$st_date]["html5"][1] += $st_val;
            }
        }

        // -----------------------------------------
        // Get Visits Stat Information by Date
        // -----------------------------------------
        
        $region = array(
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            ),
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            ),
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            )
        );
        
        $c_data = array();
        
        // ------------------------------
        // iOS/Android Analytics - Visit Stat
        // ------------------------------
        
        $optParams = array(
            'dimensions' => 'ga:city, ga:operatingSystem, ga:country, ga:subContinent' . $date_dimension,
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
            'ga:users'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        
        foreach ($stats as $st) {
            
            $country = $st[0]; 
            $ops = strtolower($st[1]);
            $country_name = $st[2];
            $sub_continent_name = $st[3];
            $st_val = intval($st[5]);
            
            if ( $REPORT_TYPE == "1" ) {
                $st_date = trim($st[4]) . "-" . trim($st[5]);
            $st_val = intval($st[6]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[4]);
            } else {
                $st_date_raw = trim($st[4]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                if($ops == "ios") {
                    $helper_dateset[$st_date]["ios"][2] += $st_val;         
                } else if($ops == "android") {
                    $helper_dateset[$st_date]["android"][2] += $st_val;
                }
            }
            
            if($country != "") {
                if(!isset($c_data[$country])) {
                    $c_data[$country] = array(0, 0, 0);
                }
            }
            
            // Save by Country
            if($ops == "ios") {
                $c_data[$country][0] = intval($c_data[$country][0]) + $st_val;
                
                if($region[0]["sub_continent"][1] == "") {
                    $region[0]["sub_continent"][1] = $sub_continent_name;
                } else {
                    if($region[0]["sub_continent"][1] != $sub_continent_name) {
                       $region[0]["sub_continent"][0] = false; 
                    }
                }

                if($region[0]["country"][1] == "") {
                    $region[0]["country"][1] = $country_name;
                } else {
                    if($region[0]["country"][1] != $country_name) {
                       $region[0]["country"][0] = false; 
                    }
                }
            } else if($ops == "android") {
                $c_data[$country][1] = intval($c_data[$country][1]) + $st_val;
                
                if($region[1]["sub_continent"][1] == "") {
                    $region[1]["sub_continent"][1] = $sub_continent_name;
                } else {
                    if($region[1]["sub_continent"][1] != $sub_continent_name) {
                       $region[1]["sub_continent"][0] = false; 
                    }
                }

                if($region[1]["country"][1] == "") {
                    $region[1]["country"][1] = $country_name;
                } else {
                    if($region[1]["country"][1] != $country_name) {
                       $region[1]["country"][0] = false; 
                    }
                }
            }
        }

        // ------------------------------
        // HTML5 Analytics - Visit Stat
        // ------------------------------
        
        $optParams_html5 = array(
            'dimensions' => 'ga:city, ga:continent, ga:country, ga:subContinent' . $date_dimension,
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
            'ga:users'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        
        foreach ($stats_html5 as $st) {
            
            $country = $st[0]; 
            $continent_name = $st[1];
            $country_name = $st[2];
            $sub_continent_name = $st[3];
            $st_val = intval($st[5]);
            
            if ( $REPORT_TYPE == "1" ) {
                $st_date = trim($st[4]) . "-" . trim($st[5]);
            $st_val = intval($st[6]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[4]);
            } else {
                $st_date_raw = trim($st[4]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                $helper_dateset[$st_date]["html5"][2] += $st_val;
            }
            
            if($country != "") {
                if(!isset($c_data[$country])) {
                    $c_data[$country] = array(0, 0, 0);
                }
            }
            
            // Save by Country
            $c_data[$country][2] = intval($c_data[$country][2]) + $st_val;
            
            if($region[2]["continent"][1] == "") {
                $region[2]["continent"][1] = $continent_name;
            } else {
                if($region[2]["continent"][1] != $continent_name) {
                   $region[2]["continent"][0] = false; 
                }
            }
            
            if($region[2]["sub_continent"][1] == "") {
                $region[2]["sub_continent"][1] = $sub_continent_name;
            } else {
                if($region[2]["sub_continent"][1] != $sub_continent_name) {
                   $region[2]["sub_continent"][0] = false; 
                }
            }

            if($region[2]["country"][1] == "") {
                $region[2]["country"][1] = $country_name;
            } else {
                if($region[2]["country"][1] != $country_name) {
                   $region[2]["country"][0] = false; 
                }
            }
        }

        // ----------------------------------------
        // Collecting Data
        // ----------------------------------------
        
        //-----------------------------------------
        // Filtering Date based stat data
        //-----------------------------------------
        foreach($helper_dateset AS $d => $v) {
            $v_data["ios"][] = array($d, $v["ios"][0], $v["ios"][1], $v["ios"][2]);
            $v_data["android"][] = array($d, $v["android"][0], $v["android"][1], $v["android"][2]);
            $v_data["html5"][] = array($d, $v["html5"][0], $v["html5"][1], $v["html5"][2]);
        }
        
        // --------------------------------------------------------
        // Filtering Country Data, so that it could be easily accepted by View Part
        // --------------------------------------------------------
        $c_data_ios = array();
        $c_data_android = array();
        $c_data_html5 = array();
        foreach($c_data AS $k => $v) {
            if($v[0] > 0) {
                $c_data_ios[] = array($k, $v[0]);
            }
            
            if($v[1] > 0) {
                $c_data_android[] = array($k, $v[1]);
            }
            
            if($v[2] > 0) {
                $c_data_html5[] = array($k, $v[2]);
            }
        }
        
        $region_name = array("", "", "");
        for($i=0; $i<3; $i++) {
            if($region[$i]["country"][0] && isset($COUNTRY_CODE_LIST[strtoupper($region[$i]["country"][1])])) {
                $region_name[$i] = $COUNTRY_CODE_LIST[strtoupper($region[$i]["country"][1])];
            } else if($region[$i]["sub_continent"][0] && isset($CONTIENT_CODE_GEO[$region[$i]["sub_continent"][1]])) {
                $region_name[$i] = $CONTIENT_CODE_GEO[$region[$i]["sub_continent"][1]];
            } else if($region[$i]["continent"][0] && isset($CONTIENT_CODE_GEO[$region[$i]["continent"][1]])) {
                $region_name[$i] = $CONTIENT_CODE_GEO[$region[$i]["continent"][1]];
            } 
        }
        
        $feed["data"] = array(
            "visits" => $v_data,
            "geo" => array(
                "ios" => $c_data_ios,
                "android" => $c_data_android,
                "html5" => $c_data_html5,
                "region" => $region_name,
            )
        );
        
    } else if($data["action"] == "3") {
        
        // -------------------------------------------------
        // Aanalytics Tab Over View...
        // start, end date should specified
        // start/end should be in format xxxx-xx-xx
        // -------------------------------------------------
        
        // ---------------------------------
        // Get Tab Stat
        // ---------------------------------
        $M = new Model($conn);
        $tabs_raw = $M->search("app_tabs", array("app_id" => $APP_ID, "is_active" => 1), "all", " ORDER BY seq");
        
        $tabs = false;
        $t_data_helper = false;
        $t_data_seq = false;
        $t_home_tab_id = 0;
        
        foreach($tabs_raw AS $t) {
            if(!empty($t["tab_label"])) {
                $tabs[$t["id"]] = $t;
                if ( $t['view_controller'] == 'HomeViewController' ) {
                    $t_home_tab_id = $t['id'];
                }
                $t_data_helper[$t["id"]] = array($t["tab_label"],0,0,0, $t["id"]);
                $t_data_seq[] = $t["id"];
            }
        }
        
        $t_data = array();
        $top_data = array(
            "tabs" => array(
                array(),
                array(),
                array(),
            ),
            "items" => array(
                array(),
                array(),
                array(),
            )
        );
        $top_data_limit = 5;
        
        // ------------------------------
        // iOS/Android Analytics - Tab Stat
        // ------------------------------
        $optParams = array(
            'dimensions' => 'ga:eventCategory, ga:operatingSystem',
          'sort' => '-ga:totalEvents',
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        foreach($stats AS $st) {
            $tab_id = strtolower($st[0]); 
            $ops = strtolower($st[1]);
            $st_val = intval($st[2]);
            
            if ( $tab_id == '0' ) {
                if($ops == "ios") {
                    $t_data_helper[$t_home_tab_id][1] = $st_val;
            
                    if ( count($top_data["tabs"][0]) < $top_data_limit ) {
                        $top_data["tabs"][0][] = array(
                            $tabs[$t_home_tab_id]["tab_label"], $st_val
                        );
                    }
                } else if ( $ops == "android" ) {
                    $t_data_helper[$t_home_tab_id][2] = $st_val;
                
                    if ( count($top_data["tabs"][1]) < $top_data_limit ) {
                        $top_data["tabs"][1][] = array(
                            $tabs[$t_home_tab_id]["tab_label"], $st_val
                        );
                    }
                }
            } else if ( $tabs[$tab_id] ) {
                if($ops == "ios") {
                    $t_data_helper[$tab_id][1] = $st_val;
                    
                    if(count($top_data["tabs"][0]) < $top_data_limit) {
                        $top_data["tabs"][0][] = array(
                            $tabs[$tab_id]["tab_label"], $st_val
                        );
                    }   
                } else if($ops == "android") {
                    $t_data_helper[$tab_id][2] = $st_val;
                    
                    if(count($top_data["tabs"][1]) < $top_data_limit) {
                        $top_data["tabs"][1][] = array(
                            $tabs[$tab_id]["tab_label"], $st_val
                        );
                    }   
                    }   
            }
        }
        
        // ------------------------------
        // HTML5 Analytics - Tab Stat
        // ------------------------------
        $optParams_html5 = array(
            'dimensions' => 'ga:eventCategory',
          'sort' => '-ga:totalEvents',
            'filters' => 'ga:dimension1==' . $APP_ID,
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        foreach($stats_html5 AS $st) {
            $tab_id = strtolower($st[0]); 
            $st_val = intval($st[1]);
            
            if($tabs[$tab_id]) {
                $t_data_helper[$tab_id][3] = $st_val;
                
                if(count($top_data["tabs"][2]) < $top_data_limit) {
                    $top_data["tabs"][2][] = array(
                        $tabs[$tab_id]["tab_label"], $st_val
                    );
                } 
            }
        }
        
        foreach($t_data_seq AS $seq) {
            $t_data[] = $t_data_helper[$seq];
        }
        
        // ----------------------------
        // Get Item Stat
        // ----------------------------
        
        // ------------------------------
        // iOS Analytics - Top Item
        // ------------------------------
        $optParams = array(
            'dimensions' => 'ga:eventCategory, ga:eventAction',
          'sort' => '-ga:totalEvents',
          'filters' => 'ga:operatingSystem==iOS;ga:dimension1==' . $APP_ID . ';ga:eventAction!=0',
          'max-results' => $top_data_limit,
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        foreach($stats AS $st) {
            $tab_id = $st[0]; 
            $item_id = $st[1];
            $st_val = intval($st[2]);
            
            if($tabs[$tab_id]) {
                $label_info = get_item_label($conn, $tabs[$tab_id]["view_controller"], $item_id, $APP_ID);
                if(count($top_data["items"][0]) < $top_data_limit) {
                    $top_data["items"][0][] = array(
                        $label_info[0], 
                        $st_val,
                        $label_info[1],
                    );
                }
            }
        }

        // ------------------------------
        // Android Analytics - Top Item
        // ------------------------------
        $optParams = array(
            'dimensions' => 'ga:eventCategory, ga:eventAction',
          'sort' => '-ga:totalEvents',
          'filters' => 'ga:operatingSystem==Android;ga:dimension1==' . $APP_ID . ';ga:eventAction!=0',
          'max-results' => $top_data_limit,
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        foreach($stats AS $st) {
            $tab_id = $st[0]; 
            $item_id = $st[1];
            $st_val = intval($st[2]);
            
            if($tabs[$tab_id]) {
                $label_info = get_item_label($conn, $tabs[$tab_id]["view_controller"], $item_id, $APP_ID);
                if(count($top_data["items"][1]) < $top_data_limit) {
                    $top_data["items"][1][] = array(
                        $label_info[0], 
                        $st_val,
                        $label_info[1],
                    );
                }   
            }
        }
        
        // ------------------------------
        // HTML5 - Top Item
        // ------------------------------
        $optParams_html5 = array(
            'dimensions' => 'ga:eventCategory, ga:eventAction',
          'sort' => '-ga:totalEvents',
          'filters' => 'ga:dimension1==' . $APP_ID . ';ga:eventAction!=0',
          'max-results' => $top_data_limit,
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        foreach($stats_html5 AS $st) {
            $tab_id = $st[0]; 
            $item_id = $st[1];
            $st_val = intval($st[2]);
            
            if($tabs[$tab_id]) {
                $label_info = get_item_label($conn, $tabs[$tab_id]["view_controller"], $item_id, $APP_ID);
                if(count($top_data["items"][2]) < $top_data_limit) {
                    $top_data["items"][2][] = array(
                        $label_info[0], 
                        $st_val,
                        $label_info[1],
                    );
                }   
            }
        }
        
        // ------------------------------
        // Collecting Data
        // ------------------------------
        $feed["data"] = array(
            "overview" => $t_data,
            "top" => $top_data,
        );
        
    } else if($data["action"] == "4") {
        
        // -------------------------------------------------
        // Aanalytics about Detailed Tab ...
        // start, end date should specified
        // start/end should be in format xxxx-xx-xx
        // -------------------------------------------------
        
        // --------------------------------------------
        // Get Tab Stat Information by Date
        // --------------------------------------------
        
        $date_dimension = ", ga:date";
        if($REPORT_TYPE == "2") {
            $date_dimension = ", ga:year";
        } else if($REPORT_TYPE == "1") {
            $date_dimension = ", ga:year, ga:month";
        }
        
        $c_data = array();
        $v_data = array(
            "ios" => array(),
            "android" => array(),
            "html5" => array(),
        );
        
        $region = array(
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            ),
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            ),
            array(
                "continent" => array(
                    true, ''
                ),
                "sub_continent" => array(
                    true, ''
                ),
                "country" => array(
                    true, ''
                )
            )
        );
        
        // ------------------------------
        // iOS/Android Analytics - Tab Stat
        // ------------------------------
        
        $optParams = array(
            'dimensions' => 'ga:operatingSystem, ga:city' . $date_dimension,
          'sort' => 'ga:date',
            'filters' => 'ga:dimension1==' . $APP_ID . ';ga:eventCategory==' . $data["tab"] . ';ga:eventAction==0',
        );
        
        $results = $analytics->data_ga->get(
           'ga:' . $ga_profile_id,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams
        );
        
        $stats = get_result_array($results);
        foreach ($stats as $st) {
            $ops = strtolower($st[0]);
            $country = $st[1];
            $st_val = intval($st[3]);
            
            if($REPORT_TYPE == "1") {
                $st_date = trim($st[2]) . "-" . trim($st[3]);
                $st_val = intval($st[4]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[2]);
            } else {
                $st_date_raw = trim($st[2]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                if($ops == "ios") {
                    $helper_dateset[$st_date]["ios"][0] += $st_val;         
                } else if($ops == "android") {
                    $helper_dateset[$st_date]["android"][0] += $st_val;
            }
            }
            
            if($state != "") {
                if(!isset($c_data[$state])) {
                    $c_data[$state] = array(0, 0, 0);
                }
            }
            
            // Save by Country
            if($ops == "ios") {
                $c_data[$country][0] = intval($c_data[$country][0]) + $st_val;
            } else if($ops == "android") {
                $c_data[$country][1] = intval($c_data[$country][1]) + $st_val;
                    }
                }
        
        // ------------------------------
        // HTML5 Analytics - Tab Stat
        // ------------------------------
        
        $optParams_html5 = array(
            'dimensions' => 'ga:city' . $date_dimension,
          'sort' => 'ga:date',
            'filters' => 'ga:dimension1==' . $APP_ID . ';ga:eventCategory==' . $data["tab"] . ';ga:eventAction==0',
        );
        
        $results_html5 = $analytics->data_ga->get(
           'ga:' . $ga_profile_id_html5,
           $start,
           $end,
           'ga:totalEvents'
           ,$optParams_html5
        );
        
        $stats_html5 = get_result_array($results_html5);
        foreach ($stats_html5 as $st) {
            $country = $st[0];
            $st_val = intval($st[2]);
            
            if($REPORT_TYPE == "1") {
                $st_date = trim($st[1]) . "-" . trim($st[2]);
                $st_val = intval($st[3]);
            } else if($REPORT_TYPE == "2") {
                $st_date = "Year " . trim($st[1]);
            } else {
                $st_date_raw = trim($st[1]);
                $st_date = $st_date_raw[0].$st_date_raw[1].$st_date_raw[2].$st_date_raw[3]."-".$st_date_raw[4].$st_date_raw[5]."-".$st_date_raw[6].$st_date_raw[7];
            }
            
            if(isset($helper_dateset[$st_date])) {
                $helper_dateset[$st_date]["html5"][0] += $st_val;
            }
            
            if($state != "") {
                if(!isset($c_data[$state])) {
                    $c_data[$state] = array(0, 0, 0);
                }
            }
            
            // Save by Country
            $c_data[$country][2] = intval($c_data[$country][2]) + $st_val;
        }
        
        // -------------------------------------------------
        // Filtering Date based stat data
        // -------------------------------------------------
        foreach($helper_dateset AS $d => $v) {
            $v_data["ios"][] = array($d, $v["ios"][0]);
            $v_data["android"][] = array($d, $v["android"][0]);
            $v_data["html5"][] = array($d, $v["html5"][0]);
        }
        
        // ------------------------------------------------------------------------------
        // Filtering Country Data, so that it could be easily accepted by View Part
        // ------------------------------------------------------------------------------
        $c_data_ios = array();
        $c_data_android = array();
        $c_data_html5 = array();
        
        foreach($c_data AS $k => $v) {
            if($v[0] > 0) {
                $c_data_ios[] = array($k, $v[0]);
            }
            
            if($v[1] > 0) {
                $c_data_android[] = array($k, $v[1]);
            }
            
            if($v[2] > 0) {
                $c_data_html5[] = array($k, $v[2]);
            }
        }
        
        $region_name = array("", "", "");
        
        $M = new Model($conn);
        $item_raw = $M->search("app_tabs", array("id" => $data["tab"]), "first");
        
        $feed["data"] = array(
            "visits" => $v_data,
            "geo" => array(
                "ios" => $c_data_ios,
                "android" => $c_data_android,
                "html5" => $c_data_html5,
                "region" => $region_name,
            ),
            "tab_name" => $item_raw["tab_label"]?$item_raw["tab_label"]:"unknown",
        );
    }     
}

$json = json_encode($feed);

header('Content-type: application/json');
header("Content-encoding: gzip");
echo gzencode($json);
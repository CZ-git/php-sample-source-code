<?php

include_once "dbconnect.inc";
include_once "app.inc";
require_once "common_functions.php";
require_once "ray_model/devices.php";

$data = make_data_safe($_REQUEST);

$firstLoad = 0;
$current_time = date("Y-m-d H:i:s");

// $app_name = ereg_replace("[^[:alnum:]]", "", $data["appCode"]);
// Not necessary as APNS are basically broken in the master app
// The user agent should always be enough to identify the app
if($app_id == '178172') {
    $debug_normal = 0;
    $enabled_push = 1;
} else {
    $debug_normal = 0;
    $enabled_push = 1;
}


if($debug_normal) {
    $push_ids = array();
    $to = "support@appsomen.com";
    $subject = "Geo-fencing & Actual Until Feature Test";

    $content = "Current Time : ".$current_time . "<br/>";
    
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: support@appsomen.com' . "\r\n";
}


$devToken = ereg_replace("[^[:alnum:]]", "", $data["devToken"]);

$M = new Devices($conn);
if($devToken != "") {
    // get device info before update or add
    $device_table = "devices";
    $deviceflag = "send_to_iphone";
    if($data["device"]) {
        $device_table .= "_android";
        $deviceflag = "send_to_android";
    }
    $sql = "SELECT * FROM $device_table WHERE app_id='$app_id' AND devToken='$devToken'";
    $res = mysql_query($sql, $conn);

    // for debug
    if($debug_normal)
        $content .= $sql . "<br/>";
        
    if(mysql_num_rows($res) > 0) {
        $qry = mysql_fetch_array($res);
        $prev_lat = $qry['latitude'];
        $prev_long = $qry['longitude'];
        $last_update = $qry['updated_on'];

        // for debug
        if($debug_normal)
            $content .= "Already existed - ".$prev_lat."   ".$prev_long."   ".$last_update."<br/>";

    } else {
        $firstLoad = 1;

        // for debug
        if($debug_normal)
            $content .= "First Use<br/>";
    }
    
    // get push message details which is active now (geo-fencing push messages with actual until feature)
    $push_messages = array();
    $more_info = array();

    $sql = "SELECT p.app_id, p.created, p.message, d.* FROM push_details d
            LEFT JOIN push_notifications p ON d.push_id=p.id
            WHERE d.push_type=1 AND p.app_id='$app_id' AND d.duration_mode=1 AND d.active_until > '$current_time'";
    // for debug
    if($debug_normal)
        $content .= $sql . "<br/>";

    $res = mysql_query($sql, $conn);
    while($qry = mysql_fetch_array($res)) {
        $more_info = $qry;
        unset($more_info['app_id']);
        unset($more_info['created']);
        
        // get push message location detail
        if (isset($more_info[$deviceflag]) && intval($more_info[$deviceflag]) == 1){
            if(intval($qry['loc_type']) == 0) { // in case push to all users
                if($firstLoad) { // in case the first time user login - that is new user
                    $push_messages[] = $more_info;
                    // for debug
                    if($debug_normal) $push_ids[] = $more_info['push_id'];
                }
            } else { // push with specific location 
                if(intval($qry['geofence']) == 0) { // push with circle location mode
                    if($firstLoad) { // in case the first time user login - that is new user
                        $push_messages[] = $more_info;

                        // for debug
                        if($debug_normal) $push_ids[] = $more_info['push_id'];
                    }
                } else { // push with geo-fence 
                    $paths = explode(' ', $qry["paths"]);
                    if ( $paths ) {
                        $points = array();
                        foreach( $paths as $pt ) {
                            $pt = str_replace( array('(', ')'), array('', ''), $pt);
                            $pt = explode(',', $pt);
                            $points[] = $pt;
                        }
                        
                        if($firstLoad) {
                            $push_messages[] = $more_info;
                            
                            // for debug
                            if($debug_normal) $push_ids[] = $more_info['push_id'];
                        } else {
                            if ( !isContainCoordinate($points, array($prev_lat, $prev_long)) ) {
                                $push_messages[] = $more_info;
                                
                                // for debug
                                if($debug_normal) $push_ids[] = $more_info['push_id'];
                            }
                        }
                    }                    
                }
            }
        }
    }            

    // for debug
    if($debug_normal) {
        $content .= "<br/>Selected Push messages<br/>";
        $content .= implode("<br/>", $push_ids) . "<br/>";
    }    
    
    $updatedon = date("Y-m-d H:i:s");
    
    if($data["device"]) {
        $M->switch_table("android");
    }

    $M->add_device($app_id, $devToken, $updatedon, $data["latitude"], $data["longitude"] );
    
    $result = array('result' => 'success');
    
    if($debug_normal) {
        $content .= "<br/>Pushed message list<br/>";
    }    

    // Check geo-fencing
    if($enabled_push) {
        if ( $data["latitude"] && $data["longitude"] ) {
            foreach($push_messages as $more_info) {
                if(intval($more_info['loc_type']) == 0) { // in case push to all users
                    if(!$firstLoad) { // in case current user isn't new user
                        continue; // ignore current push
                    }
                } else { // push with specific location
                    if(intval($more_info['geofence']) == 0) { // push with circle location mode
                        if($firstLoad) { // in case current user is new user
                            // check if current user location is in push location
                            $latitude = $more_info["latitude"];
                            $longitude = $more_info["longitude"];
                            $radius = $more_info["radius"];
                            if(intval($more_info["distance_type"]) == 1) { // miles, so convert miles to km
                                $radius = $radius * 1.60934;
                            }

                            $distance = (((acos(sin(($latitude*pi()/180)) *  sin(($data['latitude']*pi()/180))+cos(($latitude*pi()/180)) *  cos(($data['latitude']*pi()/180)) * cos((($longitude-$data['longitude']) *pi()/180))))*180/pi())*60*1.1515*1.609344);
                            
                            if($radius < $distance) { // current user ins't in current push's location. User is out of location
                                // so ignore current push
                                continue;
                            }
                        } else { // in case current user is not new user
                            continue; // ignore this push
                        }
                    } else { // push with geo-fence
                        $paths = explode(' ', $more_info["paths"]);
                        if ( $paths ) {
                            $points = array();
                            foreach( $paths as $pt ) {
                                $pt = str_replace( array('(', ')'), array('', ''), $pt);
                                $pt = explode(',', $pt);
                                $points[] = $pt;
                            }
                            
                            if ( !isContainCoordinate($points, array($data["latitude"], $data["longitude"])) ) { // case current user location is not in push fence area
                                // in appropriate push for current user, so ignore it.
                                continue;
                            }
                        } else { // invalid push, so ignore it
                            continue;
                        }
                    }
                }
                
                // for debug
                if($debug_normal) {
                    $content .= $more_info['push_id'] . "<br/>";
                }    
                 
                if($deviceflag == "send_to_iphone") { // send push to iphone & ipad
                    require_once 'ApnsPHP/Autoload.php';
                    try {
                    
                        if ( $data['dev'] == '1' )
                            $apnsCert = "/home/bizapps/public_html/uploads/certs/".$app_id."/development.pem";
                        else
                            $apnsCert = "/home/bizapps/public_html/uploads/certs/".$app_id."/production.pem";
                            
                        $rootCert = "/home/bizapps/public_html/uploads/certs/entrust_root_certification_authority.pem";
                        
                        if ( $data['dev'] == '1' ) {
                            $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX);                    
                        } else {
                            $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION);                    
                        }
                        
                        // Set the Provider Certification file
                        $push->setProviderCertificationFile($apnsCert);
                        
                        // Set the Root Certificate Autority to verify the Apple remote peer
                        $push->setRootCertificationAuthority($rootCert);

                        // Connect to the Apple Push Notification Service
                        $is_apns_connected = $push->connect();            
                        
                        $msg = $more_info["message"];
                        $msg = trim(str_replace('\n', "\n", str_replace("\r\n", "\n", str_replace('\r\n', "\r\n", $msg))));
                        $message = new ApnsPHP_Message($devToken);
                        $message->setBadge(1);
                        $message->setText($msg);
                        $message->setSound();
                        $message->setCustomProperty('id', '122600');
						$message->setCustomProperty('date', strtotime(gmdate("Y-m-d H:i:s")));
                        if($more_info["rich_type"] != "0") {
                            $message->setCustomProperty('mid', intval($more_info["push_id"]));
                        }
                        $message->setExpiry(30);
                        $push->add($message);
                        $push->send();
                    } catch (ApnsPHP_Exception $e) { // connection error
                        $result["push_error"] = "2";
                    } catch (ApnsPHP_Init_Exception $e) { // initiation error
                        $result["push_error"] = "3";
                    } catch (ApnsPHP_Certificate_Exception $e) { // invalid certification error
                        $result["push_error"] = "4";
                    } catch (ApnsPHP_RootCertificate_Exception $e) { // invalid root certification error
                        $result["push_error"] = "5";
                    } catch (ApnsPHP_Message_Exception $e) { // failed composing message - from ApnsPHP_Message class
                        $result["push_error"] = "6";
                    } catch (ApnsPHP_Push_Exception $e) { // failed sending push notification -  from ApnsPHP_Push class
                        $result["push_error"] = "7";
                    } catch (Exception $e) { // other error
                        $result["push_error"] = "8";
                    }
                    
                    $push->disconnect();
                    $aErrorQueue = $push->getErrors();
                    
                } else { // send push to android phone
                    $wrong_android_url = "0";
                    $android_sku = "";
                    
                    $a = get_app_record($conn, $app_id);
                    if($a["android_url"] != "") {
                        if(preg_match("/(=)(.*)(layout)/", $a["android_url"], $matches)) {
                            $android_sku = substr($matches[0], 1);
                        } else {
                            $report["android"] = "1"; // Wrong Android URL
                        }
                    }
                    
                    // Sending through CURL
                    if($android_sku != "") {
                        $ch = curl_init();
                        $strHeaders = array(
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                            'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.202 Safari/535.1'
                        );
                    
                        try {
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $strHeaders);
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            
                            $post_data = "login=wychka&pwd=wychka_pwd&package_name=".urlencode($android_sku)."&msg=".urlencode($msg);     
                            
                            // Now lets see if this is version 2 message or not.
                            if($more_info && is_array($more_info) && (intval($more_info["push_id"]) > 0) && (($more_info["loc_type"] != "0") || ($more_info["rich_type"] != "0"))) {
                                $post_data .= "&version=2&id=" . intval($more_info["push_id"]);
                                $post_data .= "&deviceTokens=".$devToken;
                            }

                            curl_setopt($ch, CURLOPT_URL, "http://198.57.176.205:8080/notification/BroadcastSender");                   
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);                

                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            
                            //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                            //curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    
                            $result = curl_exec($ch);
                            curl_close($ch);
                    
                        } catch( Exception $e) {
                            $report["android"] = "4"; // Something went wrong in Network on Server
                        } 
                    }                            
                }
            }
        }
    }
    
    $feed = array( $result );
        
} else {
    $feed = array(
        array('result' => 'failed')
    );
}

// for debug
if($debug_normal) 
    mail($to, $subject, $content, $headers);

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
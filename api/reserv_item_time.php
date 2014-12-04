<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";
include_once "ray_model/reserv.php";

$data = make_data_safe($_GET);
/*---------------------------------------------------------------
Expected params:
app_code, tab_id, service_id, user_id, date, day 
---------------------------------------------------------------*/

$m = get_service_item($conn, $data["id"]);

$available_times = array();

if($m == false) {
    // No service found...    
} else {
    $a = get_app_record($conn, $app_id);
    $main_info = get_center_information($conn, $app_id, $data["tab_id"]);
    
    $sql = "SELECT id FROM app_users WHERE  
              email = '$data[user_id]' AND 
              app_id = '$app_id'";
    $res = mysql_query($sql, $conn);
    $data["user_login_id"] = $data["user_id"];
    $data["user_id"] = intval(mysql_result($res, 0, 0)); 
              
    $open_time = array("from" => 420, "to" => 1020);
    //-------------------------------------------------------------------
    // Retrieve service time
    //-------------------------------------------------------------------
    
    // Now open-time and close-time are multiple.
    $serv_time = array();
    
    if($m["is_available"] == "1") {
        $open_times_set = get_center_time($conn, $main_info["id"]);
        foreach($open_times_set AS $tm) {
            if(strtoupper($tm["day"]) == strtoupper($data["day"])) {

                $serv_time[] = array(
                    "from" => intval($tm["open_time"]), 
                    "to" => intval($tm["close_time"])
                );
                
                /*
                $open_time["from"] = intval($tm["open_time"]);
                $open_time["to"] = intval($tm["close_time"]);
                */
                
                // Get More time
                $more_time = json_decode($tm["more_time"]);
                if($more_time && is_array($more_time) && count($more_time) > 0) {
                    foreach($more_time AS $mt) {
                        $serv_time[] = array(
                            "from" => intval($mt[0]), 
                            "to" => intval($mt[1])
                        );
                    }
                }

                /*if(intval($m["max_service"]) == 0) {
                    $serv_time = array(
                        array(
                            "from" => 0, 
                            "to" => 1440
                        )
                    );
                }*/
                
                break;
            }
        }
    } else if($m["is_available"] == "2") {
        $open_times = get_service_time($conn, $m["id"], $main_info["id"]);

        foreach($open_times AS $tm) {
            if(strtoupper($tm["day"]) == strtoupper($data["day"])) {

                $serv_time[] = array(
                    "from" => intval($tm["open_time"]), 
                    "to" => intval($tm["close_time"])
                );

                /*
                $open_time["from"] = intval($tm["open_time"]);
                $open_time["to"] = intval($tm["close_time"]);
                */
                
                // Get More time
                $more_time = json_decode($tm["more_time"]);
                if($more_time && is_array($more_time) && count($more_time) > 0) {
                    foreach($more_time AS $mt) {
                        $serv_time[] = array(
                            "from" => intval($mt[0]), 
                            "to" => intval($mt[1])
                        );
                    }
                }
                
                /*if(intval($m["max_service"]) == 0) {
                    $serv_time = array(
                        array(
                            "from" => 0, 
                            "to" => 1440
                        )
                    );
                }*/

                break;
            }
        }
    } else {
        $serv_time[] = array("from" => 0, "to" => 0);
    }
    
    /*
    echo "open_time";
    print_r($open_time);
    */

    $dur = intval($m["duration"]);
    if($dur == "0") $dur = 100000;
    
    //--------------------------------------------------------------------
    //Retrieve un-available hours
    //--------------------------------------------------------------------
    $ban_hours = array();
    //--------------------------------------------------------------------
    //Retrieve already orderd hours for this uer
    //--------------------------------------------------------------------
    $whr_array = array(
        "user_id" => $data[user_id], 
        "app_id" => $app_id,
        "tab_id" => $data[tab_id],
        "item_id" => $data[id],
        "order_state" => '0',
        "date" => $data[date],
    );
    $orders = get_service_orders($conn, $whr_array);
    
    foreach($orders AS $odr) {
        $ban_hours[] = array("from" => intval($odr["time_from"]), "to" => intval($odr["time_to"]));
    }

    foreach($serv_time AS $open_time) {
        $loop_from = $open_time["from"];
        $loop_to = $open_time["to"];
        if($loop_to - $dur < $loop_from) {
            $loop_to = $loop_from + $dur; 
        }

        for($i = $loop_from; $i < $loop_to - $dur + 1; $i = $i + $dur) {
            
            $is_banned = false;
            foreach($ban_hours AS $bh) {
                if((($i >= $bh["from"]) && ($i < $bh["to"])) || (($i+$dur > $bh["from"]) && ($i+$dur <= $bh["to"]))) {
                    $is_banned = true;
                    break;
                }
            }            
            
            if ( $is_banned == false ) {
                $orders_item = array();
                if(intval($m["max_service"]) > 0) {
                    $whr_array = array(
                        "app_id" => $app_id,
                        "tab_id" => $data[tab_id],
                        "order_state" => '0',
                        "time_from" => $i,
                        "time_to" => $i+$dur,
                        "item_id" => $data[id],
                        "date" => $data[date],
                    );
                    $orders = get_service_orders($conn, $whr_array);
                    if ( count($orders) >= intval($m["max_service"]) ) {
                        $is_banned = true;
                    }
                }
            }
            
            if($is_banned == false) {
                $available_times[] = array(
                    "from" => $i,
                    "to" => ($i + $dur > $open_time["to"]) ?$open_time["to"]:($i + $dur)
                );
            }
        }

        if ( $i < $loop_to ) {
            $available_times[] = array(
                "from" => $i,
                "to" => $loop_to
            );
        }
    }
}

/*
echo "available_time";
print_r($available_times);
exit;
*/

$json = json_encode($available_times);
header("Content-encoding: gzip");
echo gzencode($json);

?>
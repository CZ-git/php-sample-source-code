<?php
include_once "dbconnect.inc";
include_once "app.inc";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

include_once "common_functions.php";
        
include_once "ray_model/common.php";
include_once "ray_model/reserv.php";

$data = make_data_safe($_REQUEST);


$currency = get_currency_list($conn);
$currency_dic = array();
foreach ($currency AS $c) {
    $currency_dic[strtoupper($c["currency_code"])] = $c["currency_sign"]; 
}

$main_info = get_center_information($conn, $app_id, $data["tab_id"]);

$template_from_db = 0;
$mail_array = get_app_reservconfirmationemail($conn, $app_id, $data["tab_id"]);
if(is_array($mail_array)) {
    $template_from_db = 1;
}

$feed = array();
$item = "";
if(($app_id == "0") || ($app_id == "19")) {
    // Seems no app_code specified. Just return error message.
    $item["error"] = "4";
} else {
    if($data["action"] == "1") {

        // Insert Reserve Order
        include_once "appuser_action_checktoken.php";
        
        if(!$data[f]) {
            $data[f] = $app_user["contact_first_name"];
        }
        if(!$data[l]) {
            $data[l] = $app_user["contact_last_name"];
        }
        if(!$data[c]) {
            $data[c] = $app_user["contact_phone"];
        }
        if (!$data[created_time]) {
            $data[created_time] = time();
        }

        $service_sql = "SELECT * FROM service_item WHERE id = '$data[i]'";
        $service_res = mysql_query($service_sql, $conn);
        $service = mysql_fetch_array($service_res);
        if ( !$service['id'] ) {
            $item["error"] = "4";
        } else {
            // Check if ordered already
            $whr_array = array(
                "app_id" => $app_id,
                "tab_id" => $data[tab_id],
                "item_id" => $data[i],
                "user_id" => $app_user[id],
                "order_state" => '0',
                "time_from" => $data[tf],
                "time_to" => $data[tt],
                "date" => $data[d],
            );
            $orders = get_service_orders($conn, $whr_array);

            if ( $orders ) {
                $item["error"] = "3";
            } else {
                $max_service = intval( $service['max_service'] );
                if ( $max_service > 0 ) {
                    $whr_array = array(
                        "app_id" => $app_id,
                        "tab_id" => $data[tab_id],
                        "item_id" => $data[i],
                        "order_state" => '0',
                        "time_from" => $data[tf],
                        "time_to" => $data[tt],
                        "date" => $data[d],
                    );
                    $orders = get_service_orders($conn, $whr_array);

                    if ( count($orders) >= $max_service ) {
                        $item["error"] = "5";
                    }
                }
            }
        }

        if ( !$item["error"] ) {    
            $sql = " SET
                        user_id = '$app_user[id]',
                        first_name = '$data[f]',
                        last_name = '$data[l]',
                        address1 = '$data[a1]',
                        address2 = '$data[a2]',
                        country = '$data[cr]',
                        city = '$data[ct]',
                        zipcode = '$data[z]',
                        state = '$data[s]',
                        company = '$data[wc]',
                        fax = '$data[fx]',
                        type = '0',
                        phone = '$data[c]'
                ";
            $sql = "INSERT INTO app_users_address ".$sql;
            mysql_query($sql, $conn);
            $billing_address_id = mysql_insert_id($conn);
            
            $sql = "INSERT INTO service_order SET
                        user_id = '$app_user[id]',
                        date = '$data[d]',
                        time_from = '$data[tf]',
                        time_to = '$data[tt]',
                        item_id = '$data[i]',
                        paid_amount = '$data[pa]',
                        total_amount = '$service[cost]',
                        service_name = '$data[sn]',
                        order_state = '0',
                        note = '$data[n]',
                        app_id = '$app_id',
                        tab_id = '$data[tab_id]',
                        transaction_id = '$data[ti]',
                        checkout_method = '$data[cm]',
                        loc_id = '$data[loc_id]',
                        placed_on = '".gmdate("Y-m-d H:i:s", $data[created_time])."',
                        timezone = '$data[timezone]',
                        billing_address_id = '$billing_address_id',
                        currency = '$main_info[currency]'
                ";
            mysql_query($sql, $conn);
            $ORDER_ID = mysql_insert_id($conn);
            
            if($ORDER_ID > 0) {
                $item["error"] = "0";
                
                $sql = "SELECT * FROM service_order WHERE user_id = '$app_user[id]' AND app_id = '$app_id' AND tab_id = '$data[tab_id]' AND id = '$ORDER_ID'";
                $order = mysql_fetch_array(mysql_query($sql, $conn));
                
                $location_email_to = "";

                $loc_sql = "select email from tab_locations where id=" . $order["loc_id"];
                $loc_res = mysql_query($loc_sql, $conn);
                $loc_result = mysql_fetch_array($loc_res);
                if (isset($loc_result["email"]) && $loc_result["email"] != "" && $loc_result["email"] != null){
                    $location_email_to = $loc_result["email"];
                }

                //-------------------------------------------------------------------------
                // Alright, it seems making an order is done well.
                // Now let's fire an email to client, as well as service provider...
                //-------------------------------------------------------------------------
                
                include_once "common_functions.php";
                
                $a = get_app_record($conn, $app_id);
                $main_info = get_center_information($conn, $app_id, $data["tab_id"]);
                $location_set = get_tab_location($conn, $order["loc_id"], "id");
                $location = $location_set[0];            
                
                include_once "publish_elem_parent.php";
                $mail_template_path = str_replace("reseller/", "", $mail_template_path);
                if($mail_template_path == "/home/bizapps/public_html/") $mail_template_path = "/home/bizapps/public_html/client/";
                
                $item_sql = "SELECT * FROM service_item WHERE id = " . $order["item_id"] . " LIMIT 1";
                $item = mysql_fetch_array(mysql_query($item_sql, $conn), MYSQL_ASSOC);
                $item['price'] = $order['paid_amount'];
                $item['address_1'] = $location['address_1'];
                $item['address_2'] = $location['address_2'];
                $item['city'] = $location['city'];
                $item['state'] = $location['state'];
                $item['zip'] = $location['zip'];
                
                $payment_gate_way = array(                    
                    "0" => "Cash",
                    "1" => "Paypal",
                    "2" => "Google Checkout",
                    "3" => "Authorize.Net"
                );
                //-------------------------------------------------------------------------
                // First, send to Center Owner =>
                //-------------------------------------------------------------------------
                if($template_from_db == 0) { // get template from file
                if($main_info["admin_email"] != "" || $location_email_to != "") { // Only if admin email is set...
                    
                    $mail_body = array(
                            "subject" => "A user has made a reservation.",
                        "match_key" => array(
                            "{APP_USER_NAME}",
                            "{APP_USER_EMAIL}",    
                            "{APP_USER_PHONE}",    
                            "{APPNAME}",
                            "{CURRENCY}",
                            "{SERVICE_NAME}",
                            "{SERVICE_COST}", 
                            "{SERVICE_DATE}", 
                            "{SERVICE_TIME_FROM}",
                            "{SERVICE_TIME_TO}", 
                            "{SERVICE_MADE_ON}", 
                            "{SERVICE_PAID}",
                            "{PAID_VIA}",
                            "{LOCATION_ADDRESS_1}",
                            "{LOCATION_ADDRESS_2}",
                            "{LOCATION_STATE}",
                            "{LOCATION_ZIP}",
                            "{LOCATION_CITY}",
                        ),
                        "html" => array(
                                        $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                        $app_user["email"], 
                                        $app_user["contact_phone"], 
                                        $a["name"],
                                        $main_info["currency_sign"],
                                        str_replace("'", "&rsquo;", $order["service_name"]),
                                        number_format(doubleval($item["cost"])),
                                        $order["date"],
                                        minutes_to_time($order["time_from"]),
                                        minutes_to_time($order["time_to"]),
                                        //tz_convert_by_offset($location["timezone_value"]),
                                        //tz_convert_by_offset($order["timezone"], date("Y-m-d H:i:s", $order["created_time"] - 3600), "Y-m-d h:i:s A"),
                                        date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                        number_format(doubleval($order["paid_amount"]),2),
                                        $payment_gate_way[intval($order["checkout_method"])],
                                        $location["address_1"],
                                        $location["address_2"],
                                        $location["state"],
                                        $location["zip"],
                                        $location["city"],
                                    ),
                        "text" => array(
                                        $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                        $app_user["email"], 
                                        $app_user["contact_phone"], 
                                        $a["name"],
                                        $main_info["currency_sign"],
                                        str_replace("'", "&rsquo;", $order["service_name"]),
                                        number_format(doubleval($item["cost"])),
                                        $order["date"],
                                        minutes_to_time($order["time_from"]),
                                        minutes_to_time($order["time_to"]),
                                        //tz_convert_by_offset($location["timezone_value"]),
                                        //tz_convert_by_offset($order["timezone"], date("Y-m-d H:i:s", $order["created_time"] - 3600), "Y-m-d h:i:s A"),
                                        date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                        number_format(doubleval($order["paid_amount"]),2),
                                        $payment_gate_way[intval($order["checkout_method"])],
                                        $location["address_1"],
                                        $location["address_2"],
                                        $location["state"],
                                        $location["zip"],
                                        $location["city"],
                                    ),
                        "extra_text" => array(
                                        "pre" => "",
                                        "last" => "",
                                    ),
                        "extra_html" => array(
                                        "pre" => "",
                                        "last" => "",
                                    ),
                        "tpl" => "app_reserv_order_4client",
                        "tpl_path" => $mail_template_path."emails/app_emails/", 
                    );
                    if ($main_info["admin_email"] != ""){
                        send_email($main_info["admin_email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                    }
                    if ($location_email_to != ""){
                        send_email($location_email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                    }
                }
                
                //-------------------------------------------------------------------------
                // And then , send to app user =>
                //-------------------------------------------------------------------------
                $mail_body = array(
                    "subject" => "You have made a reservation successfully.",
                    "match_key" => array(
                        "{APP_USER_NAME}",
                        "{APP_USER_EMAIL}",  
                        "{APP_USER_PHONE}",    
                        "{APPNAME}",
                        "{CURRENCY}",
                        "{SERVICE_NAME}",
                        "{SERVICE_COST}", 
                        "{SERVICE_DATE}", 
                        "{SERVICE_TIME_FROM}",
                        "{SERVICE_TIME_TO}", 
                        "{SERVICE_MADE_ON}", 
                        "{SERVICE_PAID}",
                        "{PAID_VIA}",
                        "{LOCATION_ADDRESS_1}",
                        "{LOCATION_ADDRESS_2}",
                        "{LOCATION_STATE}",
                        "{LOCATION_ZIP}",
                        "{LOCATION_CITY}",
                    ),
                    "html" => array(
                                    $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                    $app_user["email"], 
                                    $app_user["contact_phone"], 
                                    $a["name"],
                                    $main_info["currency_sign"],
                                    str_replace("'", "&rsquo;", $order["service_name"]),
                                    number_format(doubleval($item["cost"])),
                                    $order["date"],
                                    minutes_to_time($order["time_from"]),
                                    minutes_to_time($order["time_to"]),
                                    //tz_convert_by_offset($location["timezone_value"]),
                                    //tz_convert_by_offset($order["timezone"], date("Y-m-d H:i:s", $order["created_time"] - 3600), "Y-m-d h:i:s A"),
                                    date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                    number_format(doubleval($order["paid_amount"]),2),
                                    $payment_gate_way[intval($order["checkout_method"])],
                                    $location["address_1"],
                                    $location["address_2"],
                                    $location["state"],
                                    $location["zip"],
                                    $location["city"],
                                ),
                    "text" => array(
                                    $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                    $app_user["email"], 
                                    $app_user["contact_phone"], 
                                    $a["name"],
                                    $main_info["currency_sign"],
                                    str_replace("'", "&rsquo;", $order["service_name"]),
                                    number_format(doubleval($item["cost"])),
                                    $order["date"],
                                    minutes_to_time($order["time_from"]),
                                    minutes_to_time($order["time_to"]),
                                    //tz_convert_by_offset($location["timezone_value"]),
                                    //tz_convert_by_offset($data[timezone], date("Y-m-d H:i:s", $data[created_time] - 3600), "Y-m-d h:i:s A"),
                                    date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                    number_format(doubleval($order["paid_amount"]),2),
                                    $payment_gate_way[intval($order["checkout_method"])],
                                    $location["address_1"],
                                    $location["address_2"],
                                    $location["state"],
                                    $location["zip"],
                                    $location["city"],
                                ),
                    "extra_text" => array(
                                    "pre" => "",
                                    "last" => "",
                                ),
                    "extra_html" => array(
                                    "pre" => "",
                                    "last" => "",
                                ),
                    "tpl" => "app_reserv_order_4appuser",
                    "tpl_path" => $mail_template_path."emails/app_emails/", 
                );
                send_email($app_user["email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                
                // And then... let's try to print...
                $text = compose_file_by_template(
                        $mail_template_path."emails/app_emails/app_reserv_order_print.txt",
                        array(
                            "{APP_USER_NAME}",
                            "{APP_USER_EMAIL}",    
                                "{APP_USER_PHONE}",
                            "{APPNAME}",
                            "{CURRENCY}",
                            "{SERVICE_NAME}",
                            "{SERVICE_COST}", 
                            "{SERVICE_DATE}", 
                            "{SERVICE_TIME_FROM}",
                            "{SERVICE_TIME_TO}", 
                            "{SERVICE_MADE_ON}", 
                            "{SERVICE_PAID}",
                            "{PAID_VIA}",
                            "{LOCATION_ADDRESS_1}",
                            "{LOCATION_ADDRESS_2}",
                            "{LOCATION_STATE}",
                            "{LOCATION_ZIP}",
                            "{LOCATION_CITY}",
                        ),
                        array(
                            $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                            $app_user["email"], 
                                $app_user["contact_phone"], 
                            $a["name"],
                            $main_info["currency_sign"],
                            str_replace("'", "&rsquo;", $order["service_name"]),
                            number_format(doubleval($item["cost"])),
                            $order["date"],
                            minutes_to_time($order["time_from"]),
                            minutes_to_time($order["time_to"]),
                            //tz_convert_by_offset($location["timezone_value"]),
                            //tz_convert_by_offset($data[timezone], date("Y-m-d H:i:s", $data[created_time] - 3600), "Y-m-d h:i:s A"),
                            date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                            number_format(doubleval($order["paid_amount"]),2),
                            $payment_gate_way[intval($order["checkout_method"])],
                            $location["address_1"],
                            $location["address_2"],
                            $location["state"],
                            $location["zip"],
                            $location["city"],
                        )
                    );
                
                
                $tmp_path_text = "/home/bizapps/public_html/uploads/tmp/reserv_".md5($ORDER_ID).".txt";
                $fh = fopen($tmp_path_text, 'w');
                fwrite($fh, $text);
                fclose($fh);
                
                $tmp_path_html = "/home/bizapps/public_html/uploads/tmp/reserv_".md5($ORDER_ID).".html";
                $fh = fopen($tmp_path_html, 'w');
                fwrite($fh, nl2br($text));
                fclose($fh);
                
                $loc_id = 0;
                if (isset($order["loc_id"]) && $order["loc_id"] > 0){
                    $loc_id = $order["loc_id"];
                }
                do_printing($conn, $app_id, $order["tab_id"], array("e_printer" => $tmp_path_text, "google_printer" => "http://mobilefoodprinter.com/uploads/tmp/reserv_".md5($ORDER_ID).".html"));
                
                unlink($tmp_path_text);
                //unlink($tmp_path_html);
                } else { // get templates from database
                    $payment_gate_way = array(                    
                        "0" => $mail_array['checkout_method_cash'],
                        "1" => $mail_array['checkout_method_paypal'],
                        "2" => "Google Checkout",
                        "3" => "Authorize.Net"
                    );
                    if($main_info["admin_email"] != "" || $location_email_to != "") { // Only if admin email is set...
                        $print_data = "";
                        $print_data_html = $mail_array['client_order_message'];
                        $print_data_html = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_data_html);
                        $print_data_html = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_data_html);
                        $print_data_html = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_data_html);
                        $print_data_html = str_replace("{APPNAME}", $a["name"], $print_data_html);
                        $print_data_html = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_data_html);
                        $print_data_html = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_DATE}", $order["date"], $print_data_html);
                        $print_data_html = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_data_html);
                        $print_data_html = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_STATE}", $location["state"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ZIP}", $location["zip"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_CITY}", $location["city"], $print_data_html);
                        
                        $mail_body = array(
                            "subject" => $mail_array["client_order_subject"],
                            "match_key" => array(),
                            "html" => array(),
                            "text" => array(),
                            "extra_text" => array(
                                            "pre" => $print_data,
                                            "last" => "",
                                        ),
                            "extra_html" => array(
                                            "pre" => $print_data_html,
                                            "last" => "",
                                        ),
                            "tpl" => "XXX_XXX",
                            "tpl_path" => $mail_template_path."emails/app_emails/", 
                        );
                        
                        if ($main_info["admin_email"] != ""){
                            send_email($main_info["admin_email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                        if ($location_email_to != ""){
                            send_email($location_email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                    }

                    $print_data = "";
                    $print_data_html = $mail_array['appuser_order_message'];
                    $print_data_html = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_data_html);
                    $print_data_html = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_data_html);
                    $print_data_html = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_data_html);
                    $print_data_html = str_replace("{APPNAME}", $a["name"], $print_data_html);
                    $print_data_html = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_data_html);
                    $print_data_html = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_DATE}", $order["date"], $print_data_html);
                    $print_data_html = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_data_html);
                    $print_data_html = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_STATE}", $location["state"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ZIP}", $location["zip"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_CITY}", $location["city"], $print_data_html);
                    $mail_body = array(
                        "subject" => $mail_array["appuser_order_subject"],
                        "match_key" => array(),
                        "html" => array(),
                        "text" => array(),
                        "extra_text" => array(
                                        "pre" => $print_data,
                                        "last" => "",
                                    ),
                        "extra_html" => array(
                                        "pre" => $print_data_html,
                                        "last" => "",
                                    ),
                        "tpl" => "XXX_XXX",
                        "tpl_path" => $mail_template_path."emails/app_emails/", 
                    );
                    send_email($app_user["email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");

                    // And then... let's try to print...
                    $print_text = $mail_array["order_print_txt"];
                    $print_text = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_text);
                    $print_text = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_text);
                    $print_text = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_text);
                    $print_text = str_replace("{APPNAME}", $a["name"], $print_text);
                    $print_text = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_text);
                    $print_text = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_text);
                    $print_text = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_text);
                    $print_text = str_replace("{SERVICE_DATE}", $order["date"], $print_text);
                    $print_text = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_text);
                    $print_text = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_text);
                    $print_text = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_text);
                    $print_text = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_text);
                    $print_text = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_text);
                    $print_text = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_text);
                    $print_text = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_text);
                    $print_text = str_replace("{LOCATION_STATE}", $location["state"], $print_text);
                    $print_text = str_replace("{LOCATION_ZIP}", $location["zip"], $print_text);
                    $print_text = str_replace("{LOCATION_CITY}", $location["city"], $print_text);                    
                    
                    $print_html = $mail_array["order_print_html"];
                    $print_html = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_html);
                    $print_html = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_html);
                    $print_html = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_html);
                    $print_html = str_replace("{APPNAME}", $a["name"], $print_html);
                    $print_html = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_html);
                    $print_html = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_html);
                    $print_html = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_html);
                    $print_html = str_replace("{SERVICE_DATE}", $order["date"], $print_html);
                    $print_html = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_html);
                    $print_html = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_html);
                    $print_html = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_html);
                    $print_html = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_html);
                    $print_html = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_html);
                    $print_html = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_html);
                    $print_html = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_html);
                    $print_html = str_replace("{LOCATION_STATE}", $location["state"], $print_html);
                    $print_html = str_replace("{LOCATION_ZIP}", $location["zip"], $print_html);
                    $print_html = str_replace("{LOCATION_CITY}", $location["city"], $print_html);                    
                    
                    $tmp_path_text = "/home/bizapps/public_html/uploads/tmp/reserv_".md5($ORDER_ID).".txt";
                    $fh = fopen($tmp_path_text, 'w');
                    fwrite($fh, $print_text);
                    fclose($fh);
                
                    $tmp_path_html = "/home/bizapps/public_html/uploads/tmp/reserv_".md5($ORDER_ID).".html";
                    $fh = fopen($tmp_path_html, 'w');
                    fwrite($fh, nl2br($print_html));
                    fclose($fh);
                    
                    $loc_id = 0;
                    if (isset($order["loc_id"]) && $order["loc_id"] > 0){
                        $loc_id = $order["loc_id"];
                    }
                    do_printing($conn, $app_id, $order["tab_id"], array("e_printer" => $tmp_path_text, "google_printer" => "http://mobilefoodprinter.com/uploads/tmp/reserv_".md5($ORDER_ID).".html"));
                    
                    unlink($tmp_path_text);                    
                }
            } else {
                $item["error"] = "4";
            }
        }
    } else if($data["action"] == "4") { 
        // Cancel Reserve Order
        include_once "appuser_action_checktoken.php";
        
        if($data["id"]) {
            $item["error"] = "0";
            
            //-------------------------------------------------------------------------
            // Alright, it seems cancelling an order is done well.
            // Now let's fire an email to client, as well as service provider...
            //-------------------------------------------------------------------------
            
            include_once "common_functions.php";
            
            $a = get_app_record($conn, $app_id);
            $main_info = get_center_information($conn, $app_id, $data["tab_id"]);
            
            $sql = "SELECT * FROM service_order 
                    WHERE
                        user_id = '$app_user[id]'
                        AND app_id = '$app_id'
                        AND tab_id = '$data[tab_id]'
                        AND id = '$data[id]'
                ";
            $order = mysql_fetch_array(mysql_query($sql, $conn));
            if ( !$order ) {
                $item["error"] = "9";
            } else {
                $item_sql = "SELECT * FROM service_item WHERE id = " . $order[item_id] . " LIMIT 1";
                $item = mysql_fetch_array(mysql_query($item_sql, $conn), MYSQL_ASSOC);
                
                $location_set = get_tab_location($conn, $order[loc_id], "id");
                $location = $location_set[0];
                $location_email_to = "";

                $item['address_1'] = $location['address_1'];
                $item['address_2'] = $location['address_2'];
                $item['city'] = $location['city'];
                $item['state'] = $location['state'];
                $item['zip'] = $location['zip'];

                $loc_sql = "select email from tab_locations where id=" . $order[loc_id];
                $loc_res = mysql_query($loc_sql, $conn);
                $loc_result = mysql_fetch_array($loc_res);
                if (isset($loc_result["email"]) && $loc_result["email"] != "" && $loc_result["email"] != null){
                    $location_email_to = $loc_result["email"];
                }            
                include_once "publish_elem_parent.php";
                $mail_template_path = str_replace("reseller/", "", $mail_template_path);
                if($mail_template_path == "/home/bizapps/public_html/") $mail_template_path = "/home/bizapps/public_html/client/";
                
                $payment_gate_way = array(
                    "0" => "Cash",
                    "1" => "Paypal",
                    "2" => "Google Checkout",
                    "3" => "Authorize.Net",
                );
                //-------------------------------------------------------------------------
                // First, send to Center Owner =>
                //-------------------------------------------------------------------------
                if($template_from_db == 0) { // get template from file
                    if($main_info["admin_email"] != "" || $location_email_to != "") { // Only if admin email is set...
                        
                        $mail_body = array(
                                "subject" => "A user has cancelled a reservation.",
                            "match_key" => array(
                                "{APP_USER_NAME}",
                                "{APP_USER_EMAIL}",    
                                    "{APP_USER_PHONE}",
                                "{APPNAME}",
                                "{CURRENCY}",
                                "{SERVICE_NAME}",
                                "{SERVICE_COST}", 
                                "{SERVICE_DATE}", 
                                "{SERVICE_TIME_FROM}",
                                "{SERVICE_TIME_TO}", 
                                "{SERVICE_MADE_ON}", 
                                "{SERVICE_PAID}",
                                "{PAID_VIA}",
                                "{LOCATION_ADDRESS_1}",
                                "{LOCATION_ADDRESS_2}",
                                "{LOCATION_STATE}",
                                "{LOCATION_ZIP}",
                                "{LOCATION_CITY}",
                            ),
                            "html" => array(
                                            $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                            $app_user["email"], 
                                                $app_user["contact_phone"], 
                                            $a["name"],
                                            $main_info["currency_sign"],
                                            str_replace("'", "&rsquo;", $order['service_name']),
                                            number_format(doubleval($item["cost"])),
                                            $order[date],
                                            minutes_to_time($order[time_from]),
                                            minutes_to_time($order[time_to]),
                                            //$order[placed_on],
                                            date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                            number_format(doubleval($order[paid_amount]),2),
                                            $payment_gate_way[$order[checkout_method]],
                                            $location["address_1"],
                                            $location["address_2"],
                                            $location["state"],
                                            $location["zip"],
                                            $location["city"],
                                        ),
                            "text" => array(
                                            $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                            $app_user["email"], 
                                                $app_user["contact_phone"], 
                                            $a["name"],
                                            $main_info["currency_sign"],
                                            str_replace("'", "&rsquo;", $order['service_name']),
                                            number_format(doubleval($item["cost"])),
                                            $order[date],
                                            minutes_to_time($order[time_from]),
                                            minutes_to_time($order[time_to]),
                                            //$order[placed_on],
                                            date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                            number_format(doubleval($order[paid_amount]),2),
                                            $payment_gate_way[$order[checkout_method]],
                                            $location["address_1"],
                                            $location["address_2"],
                                            $location["state"],
                                            $location["zip"],
                                            $location["city"],
                                        ),
                            "extra_text" => array(
                                            "pre" => "",
                                            "last" => "",
                                        ),
                            "extra_html" => array(
                                            "pre" => "",
                                            "last" => "",
                                        ),
                            "tpl" => "app_reserv_cancel_4client",
                            "tpl_path" => $mail_template_path."emails/app_emails/", 
                        );
                        if ($main_info["admin_email"] != ""){
                            send_email($main_info["admin_email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                        
                        if ($location_email_to != ""){
                            send_email($location_email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                        
                    }
                    
                    //-------------------------------------------------------------------------
                    // And then , send to app user =>
                    //-------------------------------------------------------------------------
                    $mail_body = array(
                        "subject" => "You have cancelled a reservation successfully.",
                        "match_key" => array(
                            "{APP_USER_NAME}",
                            "{APP_USER_EMAIL}",  
                                "{APP_USER_PHONE}",
                            "{APPNAME}",
                            "{CURRENCY}",
                            "{SERVICE_NAME}",
                            "{SERVICE_COST}", 
                            "{SERVICE_DATE}", 
                            "{SERVICE_TIME_FROM}",
                            "{SERVICE_TIME_TO}", 
                            "{SERVICE_MADE_ON}", 
                            "{SERVICE_PAID}",
                            "{PAID_VIA}",
                            "{LOCATION_ADDRESS_1}",
                            "{LOCATION_ADDRESS_2}",
                            "{LOCATION_STATE}",
                            "{LOCATION_ZIP}",
                            "{LOCATION_CITY}",
                        ),
                        "html" => array(
                                        $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                        $app_user["email"], 
                                            $app_user["contact_phone"], 
                                        $a["name"],
                                        $main_info["currency_sign"],
                                        str_replace("'", "&rsquo;", $order['service_name']),
                                        number_format(doubleval($item["cost"])),
                                        $order[date],
                                        minutes_to_time($order[time_from]),
                                        minutes_to_time($order[time_to]),
                                        //$order[placed_on],
                                        date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                        number_format(doubleval($order[paid_amount]),2),
                                        $payment_gate_way[$order[checkout_method]],
                                        $location["address_1"],
                                        $location["address_2"],
                                        $location["state"],
                                        $location["zip"],
                                        $location["city"],
                                    ),
                        "text" => array(
                                        $app_user["contact_first_name"] . " " . $app_user["contact_last_name"],
                                        $app_user["email"], 
                                            $app_user["contact_phone"], 
                                        $a["name"],
                                        $main_info["currency_sign"],
                                        str_replace("'", "&rsquo;", $order['service_name']),
                                        number_format(doubleval($item["cost"])),
                                        $order[date],
                                        minutes_to_time($order[time_from]),
                                        minutes_to_time($order[time_to]),
                                        //$order[placed_on],
                                        date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)),
                                        number_format(doubleval($order[paid_amount]),2),
                                        $payment_gate_way[$order[checkout_method]],
                                        $location["address_1"],
                                        $location["address_2"],
                                        $location["state"],
                                        $location["zip"],
                                        $location["city"],
                                    ),
                        "extra_text" => array(
                                        "pre" => "",
                                        "last" => "",
                                    ),
                        "extra_html" => array(
                                        "pre" => "",
                                        "last" => "",
                                    ),
                        "tpl" => "app_reserv_cancel_4appuser",
                        "tpl_path" => $mail_template_path."emails/app_emails/", 
                    );
                    send_email($app_user["email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");

                    $sql = "UPDATE service_order SET order_state = 2 
                            WHERE
                                user_id = '$app_user[id]'
                                AND app_id = '$app_id'
                                AND tab_id = '$data[tab_id]'
                                AND id = '$data[id]'
                        ";
                    mysql_query($sql, $conn);
                } else { // get templates from database
                    $payment_gate_way = array(                    
                        "0" => $mail_array['checkout_method_cash'],
                        "1" => $mail_array['checkout_method_paypal'],
                        "2" => "Google Checkout",
                        "3" => "Authorize.Net"
                    );
                    if($main_info["admin_email"] != "" || $location_email_to != "") { // Only if admin email is set...
                        $print_data = "";
                        $print_data_html = $mail_array['appuser_cancel_message'];
                        $print_data_html = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_data_html);
                        $print_data_html = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_data_html);
                        $print_data_html = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_data_html);
                        $print_data_html = str_replace("{APPNAME}", $a["name"], $print_data_html);
                        $print_data_html = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_data_html);
                        $print_data_html = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_DATE}", $order["date"], $print_data_html);
                        $print_data_html = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_data_html);
                        $print_data_html = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_data_html);
                        $print_data_html = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_STATE}", $location["state"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_ZIP}", $location["zip"], $print_data_html);
                        $print_data_html = str_replace("{LOCATION_CITY}", $location["city"], $print_data_html);
                        
                        $mail_body = array(
                            "subject" => $mail_array["appuser_cancel_subject"],
                            "match_key" => array(),
                            "html" => array(),
                            "text" => array(),
                            "extra_text" => array(
                                            "pre" => $print_data,
                                            "last" => "",
                                        ),
                            "extra_html" => array(
                                            "pre" => $print_data_html,
                                            "last" => "",
                                        ),
                            "tpl" => "XXX_XXX",
                            "tpl_path" => $mail_template_path."emails/app_emails/", 
                        );
                        
                        if ($main_info["admin_email"] != ""){
                            send_email($main_info["admin_email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                        if ($location_email_to != ""){
                            send_email($location_email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                        }
                    }

                    $print_data = "";
                    $print_data_html = $mail_array['client_cancel_message'];
                    $print_data_html = str_replace("{APP_USER_NAME}", $app_user["contact_first_name"] . " " . $app_user["contact_last_name"], $print_data_html);
                    $print_data_html = str_replace("{APP_USER_EMAIL}", $app_user["email"], $print_data_html);
                    $print_data_html = str_replace("{APP_USER_PHONE}", $app_user["contact_phone"], $print_data_html);
                    $print_data_html = str_replace("{APPNAME}", $a["name"], $print_data_html);
                    $print_data_html = str_replace("{CURRENCY}", $main_info["currency_sign"], $print_data_html);
                    $print_data_html = str_replace("{SERVICE_NAME}", str_replace("'", "&rsquo;", $order["service_name"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_COST}", number_format(doubleval($item["cost"])), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_DATE}", $order["date"], $print_data_html);
                    $print_data_html = str_replace("{SERVICE_TIME_FROM}", minutes_to_time($order["time_from"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_TIME_TO}", minutes_to_time($order["time_to"]), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_MADE_ON}", date("Y-m-d h:i:s A", strtotime($order["placed_on"]) + ($order["timezone"] * 3600)), $print_data_html);
                    $print_data_html = str_replace("{SERVICE_PAID}", number_format(doubleval($order["paid_amount"]),2), $print_data_html);
                    $print_data_html = str_replace("{PAID_VIA}", $payment_gate_way[intval($order["checkout_method"])], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ADDRESS_1}", $location["address_1"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ADDRESS_2}", $location["address_2"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_STATE}", $location["state"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_ZIP}", $location["zip"], $print_data_html);
                    $print_data_html = str_replace("{LOCATION_CITY}", $location["city"], $print_data_html);
                    
                    $mail_body = array(
                        "subject" => $mail_array["client_cancel_subject"],
                        "match_key" => array(),
                        "html" => array(),
                        "text" => array(),
                        "extra_text" => array(
                                        "pre" => $print_data,
                                        "last" => "",
                                    ),
                        "extra_html" => array(
                                        "pre" => $print_data_html,
                                        "last" => "",
                                    ),
                        "tpl" => "XXX_XXX",
                        "tpl_path" => $mail_template_path."emails/app_emails/", 
                    );
                    send_email($app_user["email"], $mail_body, "$parent_partner[name] <$parent_partner[support_email]>");
                    
                    $sql = "UPDATE service_order SET order_state = 2 
                            WHERE
                                user_id = '$app_user[id]'
                                AND app_id = '$app_id'
                                AND tab_id = '$data[tab_id]'
                                AND id = '$data[id]'
                        ";
                    mysql_query($sql, $conn);                    
                }
            }
            
        } else {
            $item["error"] = "9";
        }
        
    } else if($data["action"] == "2") { 
        // Retrieve Reserve Order
        include_once "appuser_action_checktoken.php";
        
        $whr = '';
        $whr_rule = array(
            'order_state' => "o.order_state = '$$$'",
        );
        
        foreach($whr_rule AS $key => $value) {
            if(isset($data[$key]) && ($data[$key] != '')) {
                $whr .= " AND ".str_replace('$$$', $data[$key], $value);
            }
        }

        if ( $data['since'] ) {
            $data_since = split(' ', $data['since']);
            $data['since'] = trim($data_since[0]);
            
            if ( $data_since[1] ) {
                $data_since_times = split(':', trim($data_since[1]));
                $data_since_time = $data_since_times[0] * 60 + $data_since_times[1];

                $whr .= " AND ((o.date = '" . $data['since'] . "' and o.time_from >= '" . $data_since_time . "') or (o.date > '" . $data['since'] . "'))";
            } else {
                $whr .= " AND o.date >= '" . $data['since'] . "'";
            }
        }

        if ( $data['end_date'] ) {
            $data_end_date = split(' ', $data['end_date']);
            $data['end_date'] = trim($data_end_date[0]);
            
            if ( $data_end_date[1] ) {
                $data_end_date_times = split(':', trim($data_end_date[1]));
                $data_end_date_time = $data_end_date_times[0] * 60 + $data_end_date_times[1];

                $whr .= " AND ((o.date = '" . $data['end_date'] . "' and o.time_to <= '" . $data_end_date_time . "') or (o.date < '" . $data['end_date'] . "'))";
            } else {
                $whr .= " AND o.date <= '" . $data['end_date'] . "'";
            }
        }

        if ( $data['i'] ) {
            $whr .= " AND i.id = '" . $data['i'] . "'";
        }
        
        $sql = "SELECT o.*, 
                a.address1 AS billing_address1, a.address2 AS billing_address2, a.city AS billing_city, a.state AS billing_state, a.zipcode AS billing_zip, a.country AS billing_country, a.phone AS billing_phone, 
                i.cost, i.duration, i.image_url, i.service_name AS real_service_name, i.reservfee_type, i.reservfee_cost FROM service_order AS o 
                LEFT JOIN service_item AS i ON o.item_id = i.id
                LEFT JOIN app_users_address a ON o.billing_address_id = a.id
                WHERE 
                    o.user_id = '$app_user[id]' AND
                    o.app_id = '$app_id' AND
                    o.tab_id = '$data[tab_id]' AND 
                    i.id <> '' AND
                    i.id IS NOT NULL $whr
                ORDER BY o.date ASC, o.time_from ASC
            ";

        $res = mysql_query($sql, $conn);
        $items = array();
        while($qry = mysql_fetch_array($res)) {
            $indv = array();
            
            if($qry["currency"] == "") {
                if($main_info["currency"] == "") {
                    $qry["currency"] = "USD";
                } else {
                    $qry["currency"] = $main_info["currency"];   
                }
            }
            $qry["currency_sign"] = (isset($currency_dic[strtoupper($qry["currency"])]))?$currency_dic[strtoupper($qry["currency"])]:$qry["currency"];
            
            foreach($qry AS $ck => $cv) {
                  if("".intval($ck) == $ck) continue;
                  $indv[$ck] = $cv;
              }
              
              // Check URL
            $album_art = "";
            if(preg_match("/^http:\/\/(.*)$/", $qry["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qry["image_url"])) {
                $album_art = $qry["image_url"];    
            } else {
                $dir = findUploadDirectory($app_id) . "/reserv/" . $qry["image_url"];
                if(file_exists($dir) && !is_dir($dir)) {
                    $album_art = $WEB_HOME_URL.'/custom_images/'.$data[app_code].'/reserv/'.$qry["image_url"].'?width=100&height=100';
                }
            }
            $indv["thumbnail"] = $album_art; 

            $location_set = get_tab_location($conn, $qry[loc_id], "id");
            $location = $location_set[0];            
            $indv['location_address_1'] = $location['address_1'];
            $indv['location_address_2'] = $location['address_2'];
            $indv['location_city'] = $location['city'];
            $indv['location_state'] = $location['state'];
            $indv['location_zip'] = $location['zip'];
            
            $items[] = $indv;
        }
        
        $item["orders"] = $items;
    }
}



$feed[] = $item;

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);

?>
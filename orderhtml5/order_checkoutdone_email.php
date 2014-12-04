<?php
		
		$a = get_app_record($conn, $app_id);
		
		include_once($WEB_ROOT_PATH."/reseller/publish_elem_parent.php");

		include_once("common_functions.php");

		$main_info = get_restaurant_information($conn, $app_id, $data["tab_id"]);

		$email_to = $main_info["admin_email"];
		$location_email_to = "";
		$order_sql = "select loc_id from orders where order_str='" . $data[orderstr] . "' and loc_id > 0 limit 1";
		$order_res = mysql_query($order_sql, $conn);
		$order_result = mysql_fetch_array($order_res);
		$mail_body_template = "SELECT * FROM `orders_email` where tab_id=".$data[tab_id];
		$mail_res = mysql_query($mail_body_template, $conn);
		$mail_array = mysql_fetch_array($mail_res);
		if (count($order_result) > 0){
			$loc_sql = "select email from tab_locations where id=" . $order_result["loc_id"];
			$loc_res = mysql_query($loc_sql, $conn);
			$loc_result = mysql_fetch_array($loc_res);
			if (isset($loc_result["email"]) && $loc_result["email"] != "" && $loc_result["email"] != null){
				$location_email_to = $loc_result["email"];
			}
		}
		if($email_to != "" || $location_email_to != "") {

			$print_data = file_get_contents_curl($WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]");
			$print_data_html = file_get_contents_curl($WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]&type=html");
			if($mail_array[subject] == "") {$mail_array[subject]="New order has been placed.";}
			$mail_body = array(
				"subject" => $mail_array[subject],
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
			$email_cc = $_SESSION['orderemail'];
			
			if ($email_to){
				send_email($email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>", $email_cc_1);
			}
			if ($location_email_to){
				send_email($location_email_to, $mail_body, "$parent_partner[name] <$parent_partner[support_email]>", $email_cc);
			}
		}


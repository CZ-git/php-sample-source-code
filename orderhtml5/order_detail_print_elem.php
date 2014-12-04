<?php
	
	include_once("common_functions.php");

	// Output format
	if(!($data["type"])) $data["type"] = "txt";
	
	$details_text = '';
	$details_html = '';
	
	$whr .= " AND o.order_type IN (0,1,2, 3) AND o.order_str = '".$data['orderstr']."' ORDER BY o.order_type DESC";
	$orders = get_order_list($conn, $app_id, $data[tab_id], $whr);
	$mail_body_template = "SELECT * FROM `orders_email` where tab_id=".$data[tab_id];
	$mail_res = mysql_query($mail_body_template, $conn);
	$mail_array = mysql_fetch_array($mail_res);
	//print_r($mail_array);
	$details_html = '<table cellspacing="1" cellpadding="1" style="background: #ccc;"><tr><th>Details</th><th>Qty</th><th>Placed On</th><th>Special Notes</th></tr>';
	$details_text = "";
	
	$total = 0;
	$total_currency = array();
	
	$timezone_value = "";
	
	// For now users are asked to choose location before making any orders, so duplicating ordered place for every item is out of point.
	// So go on here. for now only for text format
	$ordered_place = array(
		"html" => "",
		"text" => "",
	);
	
	$current_currency = array(
		"code" => "",
		"sign" => "",
	);
	//$coun1=0;
	
	foreach($orders["data"] AS $order) {
		$template= $mail_array[ordered_items_tpl];
		//$coun1++;
		if(($current_currency["code"] == "") && ($order["currency"] != "")) $current_currency["code"] = $order["currency"];
		if(($current_currency["sign"] == "") && ($order["currency_sign"] != "")) $current_currency["sign"] = $order["currency_sign"];

		$sub_total = 0;
		
		$details_html .= '<tr><td style="background: #eee; padding: 10px;">';
		if($details_text != "") $details_text .= "\n";
		
		$ord_detail = safe_unserialize($order["order_detail"]);
		//$template='';
		if(is_array($ord_detail)) {
			 //print_r($ord_detail);
					//Template ORDER LIST for Order
				
			//	$coun2=0;
			 foreach($ord_detail AS $od) {	
				//$coun2++;
				
				// Empty Currency Sign Processing...
				if($od["currency"] == "") $od["currency"] = $current_currency["sign"];
				// PDF does not accept RUB, etc....
				if($data["type"] == "pdf") $od["currency"] = $current_currency["code"];
			 	$details_text .= "\n".$od["name"]."(".$od["currency"].number_format($od["cost"],2).")";
				$details_text_new .= "\n".$od["name"]." - ";
			 	$details_html .= $od["name"]."(".$od["currency"].number_format($od["cost"],2).")".'<br>';
			 	$total_currency[$od["currency"]] = doubleval($total_currency[$od["currency"]]) + doubleval($od["cost"]) * doubleval($order["quantity"]);
				$details_currency_new += $od["cost"]; 
				//echo "Add cost ".$details_currency_new;
			 	$sub_total += doubleval($od["cost"]) * doubleval($order["quantity"]) ;
				//echo "couninenr >".$coun2;
				
			 }
				$template = str_replace("{ORDER_NAME}", $details_text_new, $template);
				$template = str_replace("{CURRENCY}", $od["currency"], $template);
				$template = str_replace("{COST}", number_format($details_currency_new,2), $template);
				$template = str_replace("{QUANTITY}", $order["quantity"], $template);
				$template = str_replace("{ORDER_ADDRESS}", nl2br(compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode])), $template);
				$template = str_replace("{ORDER_NOTE}", nl2br(str_replace('\r\n', "\n", $order["order_note"])), $template);
				$ordered_items_tpl .=$template;
				//echo "couninenr >".$coun2;
				unset($details_currency_new);
				unset($details_text_new);
		}
				
				
				//echo "counout >".$coun1;
				//echo "couninner >".$coun2;
		// Specify Qty
		$details_text .= "\n"."Quantity : ".$order["quantity"];
		$details_html .= '<td style="background: #eee; padding: 10px;">' . $order["quantity"] . '</td>';
		
		if($order["order_type"] == "0") {
			$details_html .= '</td><td style="background: #eee; padding: 10px;"></td>';
			$details_html .= '<td style="background: #eee; padding: 10px;"></td></tr>'; 
		} else {
			$details_html .= '</td><td style="background: #eee; padding: 10px;">'.nl2br(compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode])).'</td>';
			$details_html .= '<td style="background: #eee; padding: 10px;">'.nl2br(str_replace('\r\n', "\n", $order["order_note"])).'</td></tr>';
			
			if(str_replace("\n", "", $order["order_note"]) != "") {
				$details_text .= "\n"."Special Notes:";
				$details_text .= "\n".$order["order_note"];
			}
			
			/*
			$details_text .= "\n"."Order Placed On:";
			$details_text .= "\n".compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode]);
			*/
			
			if($ordered_place["text"] == "") {
				$ordered_place["text"] = compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode]);
				$ordered_place["html"] = nl2br(compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode]));
			}
		}
		
		$total += $sub_total;

	}	
	$details_html .= '</table>';
	
	//-----------------------------------
	// Get total string value
	//-----------------------------------
	$total_string = "";
	//echo $ordered_items_tpl;
	$message_email = $mail_array[message];
	$message_email = str_replace("{ORDEREDITEMS_LIST}", $ordered_items_tpl, $message_email);
	foreach($total_currency AS $c => $v) {
		if($total_string != "") $total_string .= " + ";
		$total_string .= $c . number_format($v, 2);
	}
	$message_email = str_replace("{ORDER_TOTAL}", $total_string, $message_email);
	$order = $orders["data"][0];
	
	$delivery_address_text = "\n". compose_address_string($order[d_first_name], $order[d_last_name], $order[d_phone], $order[d_email], $order[d_address1], $order[d_address2], $order[d_city], $order[d_state], $order[d_zipcode]);
	$delivery_address_html = nl2br(compose_address_string($order[d_first_name], $order[d_last_name], $order[d_phone], $order[d_email], $order[d_address1], $order[d_address2], $order[d_city], $order[d_state], $order[d_zipcode]));
		
	if($order["order_type"] == "1") {
		$order_type = "Delivery";
		
		$delivery_address_html = "<p><b>Ordered for</b>: Delivery</p><p><b>Delivery To:</b></p>".$delivery_address_html;
		$delivery_address_text = "\n"."Ordered for: Delivery"."\n".$delivery_address_text;
		$message_email = str_replace("{ORDER_TYPE}", $mail_array[order_type_delivery], $message_email);
		
		
	} else if ($order["order_type"] == "2") {
		$order_type = "Takeout";
		
		$delivery_address_html = "<p><b>Ordered for</b>: Takeout</p><p><b>Contact Information:</b></p>".$delivery_address_html;
		$delivery_address_text = "\n"."Ordered for: Takeout".$delivery_address_text;
		$message_email = str_replace("{ORDER_TYPE}",  $mail_array[order_type_takeout], $message_email);
	} else if ($order["order_type"] == "3") {
		$order_type = "Dine-in";
		
		$delivery_address_html = "<p><b>Ordered for</b>: Dine-in</p><p><b>Contact Information:</b></p>".$delivery_address_html;
		$delivery_address_text = "\n"."Ordered for: Dine-in".$delivery_address_text;
		$message_email = str_replace("{ORDER_TYPE}",  $mail_array[order_type_dine], $message_email);
	} else {
		$order_type = "";
		
		$delivery_address_html = "<p><b>Contact Information</b></p>".$delivery_address_html;
		$delivery_address_text = "\n"."Contact Information"."\n".$delivery_address_text;
		$message_email = str_replace("{ORDER_TYPE}",  $order_type, $message_email);
	}
	
	
	if($message_email!="")
	{
	$paid_on = array(
		"1" => $mail_array[checkout_method_paypal],
		"2" => "via Google Checkout",
		"3" => "via Authorize.net",
		"4" => $mail_array[checkout_method_cash],
	);
	}
	else
	{
	$paid_on = array(
		"1" => "via Paypal",
		"2" => "via Google Checkout",
		"3" => "via Authorize.net",
		"4" => "Cash",
	);
	}
	//echo "Anwar";
	//print_r($paid_on);
	$paid_details_text = "";
	$paid_details_html = "";
	if(intval($order["checkout_method"]) > 0) {
			$paid_details_html = "<p><b>Paid ".$paid_on[$order["checkout_method"]]."</b></p>";
			$paid_details_text = "PAID: ".$paid_on[$order["checkout_method"]]."\n";
	}
	$message_email = str_replace("{CHECKOUT_METHOD}",  $paid_on[$order["checkout_method"]], $message_email);
	//print_r($data);
	//print_r($order);
	$locs = get_tab_location($conn, $order["loc_id"], 'id');
	//print_r($locs);
	date_default_timezone_set('GMT');
	$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
	$object = json_decode(file_get_contents($jsonObject));
	$ordertimesec = time() + ($locs[0]["timezone_value"] * 60 * 60)+$object->dstOffset;
	$orderhour = date("G",$ordertimesec);
	$ordermin = date("i",$ordertimesec);
	$ordersec = date("s",$ordertimesec);
	$paid_details_html .= "<p><b>Order Created on ".$orderhour.":".$ordermin.":".$ordersec."</b></p>";
	$paid_details_text .= "CREATED ON\n".date('Y-m-d H:i:s',$ordertimesec)."\n";
	$message_email = str_replace("{ORDER_TIME}", date('Y-m-d H:i:s',$ordertimesec), $message_email);
	$message_email = str_replace("{ORDER_ADDRESS}", nl2br(compose_address_string("", "", "", "", $order[l_address1], $order[l_address2], $order[l_city], $order[l_state], $order[l_zipcode])), $message_email);
	$message_email = str_replace("{DELIVERY_ADDRESS}", nl2br(compose_address_string($order[d_first_name], $order[d_last_name], $order[d_phone], $order[d_email], $order[d_address1], $order[d_address2], $order[d_city], $order[d_state], $order[d_zipcode])), $message_email);
	
	/*
	$paid_details_html .= "<p><b>Order Created on ".tz_convert_by_offset($order["timezone_value"], $order["placed_on"])."</b></p>";
	$paid_details_text .= "CREATED ON\n".tz_convert_by_offset($order["timezone_value"], $order["placed_on"])."\n";
	*/
	
	if($data["type"] == "html") {
	
		if (count($mail_array) > 0){
		echo $message_email;
		}
		else
		{
		echo $details_html;
		echo "<p><b>Total ---------- ".$total_string."</b></p>";
		echo $paid_details_html;
		echo $delivery_address_html;
		}
		
	} else if(($data["type"] == "txt") || ($data["type"] == "simple_html") || ($data["type"] == "pdf")) {
		
		$echo_txt = str_replace("&#1089;&#1086;&#1084;", "сом", $details_text);
		$echo_txt .= "\n"."\n";
		$echo_txt .= "TOTAL: ".str_replace("&#1089;&#1086;&#1084;", "сом", $total_string);
		$echo_txt .= "\n";
		$echo_txt .= "---------------------------";
		$echo_txt .= $delivery_address_text;
		
		$echo_txt .= "\n";
		$echo_txt .= "---------------------------";
		$echo_txt .= "\n";
		
		$echo_txt .= $paid_details_text;
		if($ordered_place["text"] != "") {
			$echo_txt .= "PLACED ON" . "\n" . $ordered_place["text"] . "\n";
		}
		
		if($data["type"] == "simple_html") {
			echo nl2br($echo_txt);
		} else if($data["type"] == "pdf") {
			
			$echo_txt = iconv('UTF-8', 'windows-1252', $echo_txt);

			include_once($WEB_ROOT_PATH."/reseller/fpdf17/fpdf.php");

			$pdf = new FPDF("P", "mm", array(80, 297));	
			$pdf->SetMargins(5, 5);
			$pdf->AddPage();
			$pdf->SetFont('Arial','',12);
			$pdf->Write(5, $echo_txt);
			$pdf->Output("odrders.php","I");

		} else {
			echo $echo_txt;
		}
		
	}
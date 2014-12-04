<?php
	
	
	$current_printer_settings = get_app_printer($conn, $app_id, $data[tab_id]);
	if($current_printer_settings["is_legacy"] == "1") {
		$google_pritner_api_wrapper = $WEB_ROOT_PATH.'/whitelabel/printer_detail_include_bz.php';
	} else {
		$google_pritner_api_wrapper = $WEB_ROOT_PATH.'/whitelabel/printer_detail_include.php';
	}

	include_once($WEB_ROOT_PATH."/reseller/publish_elem_parent.php");
	include_once("common_functions.php");
		

	//$print_data = file_get_contents_curl($WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]");
	//$print_data_html = file_get_contents_curl($WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]&type=html");
	$print_data_pdf = file_get_contents_curl($WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]&type=pdf");
		
	ob_start();

	$THISPATH = dirname(dirname(dirname(__FILE__))); // For Live
	$tmp_path = $THISPATH."/uploads/tmp/order_".$data[orderstr].".pdf";

	$fh = fopen($tmp_path, 'w');
	fwrite($fh, $print_data_pdf);
	fclose($fh);

	ob_end_clean();
	$order_sql = "select loc_id from orders where order_str='" . $data[orderstr] . "' and loc_id > 0 limit 1";
	$order_res = mysql_query($order_sql, $conn);
	$order_result = mysql_fetch_array($order_res);
	$loc_id = 0;
	if (isset($order_result["loc_id"])){
		$loc_id = $order_result["loc_id"];
	}
	do_printing(
		$conn, 
		$app_id, 
		$data["tab_id"], 
		array(
			"e_printer" => $tmp_path, 
			"google_printer" => $WEB_HOME_URL."/newstructhtml5/orderhtml5/order_detail_print.php?app_code=$a[code]&tab_id=$data[tab_id]&orderstr=$data[orderstr]&type=simple_html"
		), 
		$google_pritner_api_wrapper,
		$loc_id
	);


<?php

	include_once("dbconnect.inc");
	include_once("app.inc");
	
	include_once("ray_model/common.php");
	include_once("ray_model/ordering.php");

	$data = make_data_safe($_REQUEST);
	
	header('Content-Type: text/html; charset=utf-8');
	include_once($WEB_ROOT_PATH."/newstructhtml5/orderhtml5/order_detail_print_elem.php");

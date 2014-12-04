<?php

include "dbconnect.inc";
include "app.inc";

function array_sort($arr, $field, $sort_exp = SORT_ASC) {
	$key_array = array();
	foreach($arr AS $each){
		$key_array[] = $each[$field];
	}
	array_multisort($key_array, $sort_exp, $arr);
	return $arr;
}

$data = make_data_safe($_GET);

$dbresult=mysql_query("SELECT store_name, base_domain, api_key, api_secret FROM mst_storeinfo WHERE store_name='Magento' AND app_id = '$app_id' AND tab_id = '$data[tab_id]'", $conn);

$return_data = array(
	"product_id" => "",
	"data" => array(),
);


if($dbresult && ($data["product_id"] != "")) {
	$return_data["product_id"] = $data["product_id"];
	$store = mysql_fetch_object($dbresult);
  try {
  		
	$proxy = new SoapClient('http://'.$store->base_domain.'/api/soap/?wsdl');
   	$sessionId = $proxy->login($store->api_key, $store->api_secret);
	// Retrieve Images
   	$product_image = $proxy->call($sessionId, 'product_media.list', $data["product_id"]);
	$product_image = array_sort($product_image, "exclude", SORT_DESC);
	$product_image_url = array();
	foreach($product_image AS $pi) {
		$product_image_url[] = $pi["url"];
	}
	// Retrive Stock info
	$product_stock = $proxy->call($sessionId, 'product_stock.list', $data["product_id"]);
	// Retrive Producti info
	$product = $proxy->call($sessionId, 'product.info', $data["product_id"]);
	
	$f_product = array(
		"availabiltiy" => "",
		"stock_status" => $product_stock["is_in_stock"],
		"product_image_url" => $product_image_url,
	);
	
	switch($product["type_id"]) {
		case "simple":
			break;
		case "configurable":
			break;
		case "group":
			break;
	}
	
	$simple_prop = array(
		"product_code" => "product_id",
		"product_name" => "name",
		"product_description" => "description",
		"product_price" => "price",
		"product_weight" => "weight",
		"product_manufacturer" => "manufacturer",
		"product_type" => "type_id",
	);
	foreach($simple_prop AS $key => $value) {
		if(($value != "") && isset($product[$value])) {
			$f_product[$key] = $product[$value];
		} else {
			$f_product[$key] = "";
		}
	}
	$return_data["data"] = array($f_product);
	
  } catch(SoapFault $e) {
  	echo "<pre>";
  	print_r($e);
  	echo "</pre>";
  } 	
}

$json = json_encode($return_data);
header("Content-encoding: gzip");
echo gzencode($json);



?>
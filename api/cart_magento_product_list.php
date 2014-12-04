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
	"current_category" => 0,
	"products_count" => 0,
	"pageIndex" => 1,
	"count" => 0,
	"data" => array() 
);

// Default processing
if(!(isset($data["pageIndex"])) || ($data["pageIndex"] == "")) {
	$data["pageIndex"] = 1;
}
if(!(isset($data["count"])) || ($data["count"] == "")) {
	$data["count"] = 25;
}
if(!(isset($data["category_id"])) || ($data["category_id"] == "")) {
	$data["category_id"] = 3;
}



if($dbresult) {
	$store = mysql_fetch_object($dbresult);
  try {
  		
	$proxy = new SoapClient('http://'.$store->base_domain.'/api/soap/?wsdl');
   	$sessionId = $proxy->login($store->api_key, $store->api_secret);
   	
	// Retrieve products in category.
   	$products = $proxy->call($sessionId, 'category.assignedProducts', array($data["category_id"], 1));
   	
   	$cat_prd_ids = array();
   	foreach($products AS $product) {
   		$cat_prd_ids[] = $product["product_id"];
   	}
   	
   	// Filter products in category - visible, active and just in this category
   	$filters = array(
   		'visibility' => array('='=>'4'),
   		'status' => array('='=>'1'),
   		//'type' => 'simple',
   		'product_id' => array('IN' => $cat_prd_ids)
	);
	$products = $proxy->call($sessionId, 'product.list', array($filters));
	
	
	$return_data["current_category"] = $data["category_id"];
	$return_data["products_count"] = count($products);
	$return_data["pageIndex"] = $data["pageIndex"];
	$return_data["count"] = $data["count"];
	
	$q_from = ($data["pageIndex"] - 1) * $data["count"];
	$q_to = $data["pageIndex"] * $data["count"];
	

	$filtered_data = array();
	$product_ids = array();
	for($i = $q_from; $i < $q_to; $i++) {
		if(!(isset($products[$i]))) {
			break;
		}
		$product_ids[] = $products[$i]["product_id"];
		
		// Retrieve Images
		/*
		$product_image = $proxy->call($sessionId, 'product_media.list', $products[$i]["product_id"]);
		$product_image_url = "";
		foreach($product_image AS $pi) {
			if(($pi["exclude"] == "1") || ($product_image_url == "")) {
				$product_image = $pi["url"];
			}
		}
		*/
		
		// Form return data
		$f_product = array(
			"product_code" => $products[$i]["product_id"],
			"product_sku" => $products[$i]["sku"],
			"product_name" => $products[$i]["name"],
			"stock_status" => "",
			//"product_image_url" => $product_image_url,
		);
		
		$filtered_data[] = $f_product;
		
	}
	
	// Retrieve stock availibility
	$product_stock = $proxy->call($sessionId, 'product_stock.list', array($product_ids));
	for($i=0; $i<count($filtered_data); $i++) {
		$filtered_data[$i]["stock_status"] = $product_stock[$i]["is_in_stock"];
	}
	
	$return_data["data"] = $filtered_data;
	
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
<?php

include "dbconnect.inc";
include "app.inc";

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
	$data["count"] = 5;
}
if(!(isset($data["category_id"])) || ($data["category_id"] == "")) {
	$data["category_id"] = 3;
}




if($dbresult) {
	$store = mysql_fetch_object($dbresult);
  try {
  		
	$proxy = new SoapClient('http://'.$store->base_domain.'/api/soap/?wsdl');
   	$sessionId = $proxy->login($store->api_key, $store->api_secret);
   	/*
	$attributes = $proxy->call($sessionId, 'category_attribute.list');
	foreach ($attributes as &$attribute) {
	   if (isset($attribute['type'])
	       && ($attribute['type'] == 'select' || $attribute['type'] == 'multiselect')) {
	       $attribute['options'] = $proxy->call($sessionId, 'category_attribute.options', $attribute['code']);
	   }
	}
	var_dump($attributes);
	exit;
	*/
   	echo "<pre>";
   	
   	/*
   	$attributeSets = $proxy->call($sessionId, 'product_attribute_set.list');
   	print_r($attributeSets);
   	*/
   	
   	$attributes = $proxy->call($sessionId, 'product_attribute.list', 41);
	foreach ($attributes as &$attribute) {		
	   if (isset($attribute['type'])
	       && ($attribute['type'] == 'select' || $attribute['type'] == 'multiselect')) {
	       $attribute['options'] = $proxy->call($sessionId, 'product_attribute.options', array('attribute_id'=>$attribute['attribute_id']));
	   }
	   print_r($attribute);
	}
   	
   	exit;
   	
   	
   	/*
   	$filters = array(
   		'visibility' => array('='=>'4'),
   		'status' => array('='=>'1'),
	    'category' => array('IN' => array($data["category_id"]))
	);
	$products = $proxy->call($sessionId, 'product.list', array($filters));
	*/
  	/*
  	[{
  		"product_id":"16",
  		"sku":"n2610",
  		"name":"Nokia 2610 Phone",
  		"set":"38",
  		"type":"simple",
  		"categories":["8"]
  	}]
  	*/
   	
   	
   	$products = $proxy->call($sessionId, 'category.assignedProducts', array($data["category_id"], 1));
   	/*
  	[{
  		"product_id":"51",
  		"type":"simple",
  		"set":"42",
  		"sku":"1111",
  		"position":"920000"
  	}]
  	*/
  
	
	$return_data["current_category"] = $data["category_id"];
	$return_data["products_count"] = count($products);
	$return_data["pageIndex"] = $data["pageIndex"];
	$return_data["count"] = $data["count"];
	
	$q_from = ($data["pageIndex"] - 1) * $data["count"];
	$q_to = $data["pageIndex"] * $data["count"];
	

	$filtered_data = array();
	$product_ids = array();
	// for($i = $q_from; $i < $q_to; $i++) {
	for($i = 0; $i < count($products); $i++) {
		if(!(isset($products[$i]))) {
			break;
		}
		//$product_ids[] = $products[$i]["product_id"];
		//$product_image = $proxy->call($sessionId, 'product_media.list', $products[$i]["product_id"]);
		/*		
		Array
		(
		    [0] => Array
		        (
		            [file] => /b/a/barcelona-bamboo-platform-bed.jpg
		            [label] => 
		            [position] => 1
		            [exclude] => 1
		            [url] => http://rayche.gostorego.com/media/s4/ef/01/7d/7a/d9/1c/catalog/product/b/a/barcelona-bamboo-platform-bed.jpg
		            [types] => Array
		                (
		                    [0] => thumbnail
		                    [1] => small_image
		                    [2] => image
		                )
		
		        )
		
		)
		*/
		/*
		$product_image_url = array();
		foreach($product_image AS $pi) {
			$product_image_url[] = $pi["url"];
		}
		*/
		$product = $proxy->call($sessionId, 'product.info', $products[$i]["product_id"]);
		print_r($product);
		/*
		Array
		(
		    [product_id] => 51
		    [sku] => 1111
		    [set] => 42
		    [type] => simple
		    [categories] => Array
		        (
		            [0] => 22
		        )
		
		    [websites] => Array
		        (
		            [0] => 1
		        )
		
		    [type_id] => simple
		    [old_id] => 
		    [name] => Ottoman
		    [model] => magotto
		    [weight] => 20.0000
		    [dimension] => 
		    [status] => 1
		    [tax_class_id] => 2
		    [url_key] => ottoman
		    [visibility] => 4
		    [gift_message_available] => 2
		    [manufacturer] => 
		    [url_path] => ottoman.html
		    [news_from_date] => 
		    [news_to_date] => 
		    [required_options] => 0
		    [has_options] => 0
		    [image_label] => 
		    [small_image_label] => 
		    [thumbnail_label] => 
		    [created_at] => 2007-08-28 16:25:46
		    [updated_at] => 2008-08-08 14:59:04
		    [is_imported] => 
		    [minimal_price] => 299.9900
		    [price] => 299.9900
		    [cost] => 50.0000
		    [tier_price] => Array
		        (
		        )
		
		    [special_price] => 
		    [special_from_date] => 
		    [special_to_date] => 
		    [enable_googlecheckout] => 
		    [meta_title] => Ottoman
		    [meta_keyword] => Ottoman
		    [meta_description] => Ottoman
		    [short_description] => With durable solid wood framing, generous padding and plush stain-resistant microfiber upholstery.
		    [description] => The Magento ottoman will impress with its style while it delivers on quality. This piece of living room furniture is built to last with durable solid wood framing, generous padding and plush stain-resistant microfiber upholstery.
		    [room] => 72
		    [finish] => Microfiber
		    [country_orgin] => Italy
		    [color] => 26
		    [custom_design] => 
		    [custom_design_from] => 
		    [custom_design_to] => 
		    [custom_layout_update] => 
		    [options_container] => container2
		    [page_layout] => 
		    [designeditor_theme_id] => 
		)
		*/
		
		foreach($product AS $key => $value) {
			if(isset($value)) {
				$product[$key] = $value;
			} else {
				$product[$key] = "";
			}
		}
		
		$f_product = array(
			"product_code" => $product["product_id"],
			"product_name" => $product["name"],
			"stock_status" => "",
			"product_short_description" => $product["short_description"],
			"product_description" => $product["description"],
			"product_price" => $product["price"],
			"special_product_price" => $product["special_price"], // Please consider if it is right one....
			"availabiltiy" => "",
			"product_weight" => $product["weight"],
			"product_manufacturer" => $product["manufacturer"],
			"product_image_url" => $product_image_url,
		);
		
		$filtered_data[] = $f_product;
		
	}
	
	exit;
	
	// Retrieve stock availibility
	
	$product_stock = $proxy->call($sessionId, 'product_stock.list', array($product_ids));
	for($i=0; $i<count($filtered_data); $i++) {
		$filtered_data[$i]["stock_status"] = $product_stock[$i]["is_in_stock"];
	}
	//print_r($product_stock);
	/*
	Array
	(
	    [0] => Array
	        (
	            [product_id] => 41
	            [sku] => 384822
	            [qty] => 339.0000
	            [is_in_stock] => 1
	        )
	) */
	
	
	
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
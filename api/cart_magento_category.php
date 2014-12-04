<?php
include "dbconnect.inc";
include "app.inc";

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '1');

$data = make_data_safe($_GET);

$dbresult=mysql_query("SELECT store_name, base_domain, api_key, api_secret FROM mst_storeinfo WHERE store_name='Magento' AND app_id = '$app_id' AND tab_id = '$data[tab_id]'", $conn);

$return_data = array(
	"current_category" => 0,
	"categories_count" => 0,
	"data" => array() 
);

if(!(isset($data["category_id"])) || ($data["category_id"] == "" ) || ($data["category_id"] == "0" )) {
	 $data["category_id"] = 1;
}

if($dbresult) {
	$store = mysql_fetch_object($dbresult);
	
  try {

	$base_domain = $store->base_domain;
	if(strpos($base_domain, "http") !== 0) $base_domain = 'http://'.$store->base_domain;
    if(strpos($base_domain, "api/soap/?wsdl") === false) $base_domain .= '/api/soap/?wsdl';
	
 	$proxy = new SoapClient($base_domain);
   	$sessionId = $proxy->login($store->api_key, $store->api_secret);
   	$allCategories = $proxy->call($sessionId, 'catalog_category.level', array(1,1,$data["category_id"]));
   	
   	if($allCategories[0]["name"] == "Root Catalog") { // If it is root category, then lets get in more....
   		$data["category_id"] = $allCategories[0][category_id];
   		$allCategories = $proxy->call($sessionId, 'catalog_category.level', array(1,1,$data["category_id"]));
   	}
   	
   	$return_data["current_category"] = $data["category_id"];
   	$return_data["categories_count"] = count($allCategories);
   	$filtered_data = array();
   	foreach($allCategories AS $cat) {
   		$fcat["category_id"] = $cat["category_id"];
   		$fcat["category_name"] = $cat["name"];
   		// $fcat["parent_id"] = $cat["parent_id"];
   		
   		$filtered_data[] = $fcat;
   		// Need to add category image fetching part...
   		/*
   		$cat_info = $proxy->call($sessionId, 'catalog_category.info', array($cat["category_id"]));
   		
   		Array
		(
		    [category_id] => 10
		    [is_active] => 1
		    [position] => 10
		    [level] => 2
		    [increment_id] => 
		    [parent_id] => 3
		    [created_at] => 2007-08-23 11:45:22
		    [updated_at] => 2011-02-03 23:24:41
		    [name] => Furniture
		    [description] => 
		    [image] => furniture_1.jpg
		    [meta_title] => 
		    [meta_keywords] => 
		    [meta_description] => 
		    [include_in_menu] => 1
		    [all_children] => 10,22,23
		    [path_in_store] => 10
		    [children] => 22,23
		    [url_key] => furniture
		    [url_path] => furniture.html
		    [path] => 1/3/10
		    [display_mode] => PRODUCTS
		    [landing_page] => 
		    [is_anchor] => 1
		    [available_sort_by] => 
		    [default_sort_by] => 
		    [filter_price_range] => 
		    [custom_use_parent_settings] => 1
		    [custom_apply_to_products] => 1
		    [custom_design] => 
		    [custom_design_from] => 
		    [custom_design_to] => 
		    [page_layout] => 
		    [custom_layout_update] => 
		    [designeditor_theme_id] => 
		)

   		*/
   		
   	}
   	$return_data["data"] = $filtered_data;
   	
   	
   	
   	// $allCategories = $proxy->call($sessionId, 'category.tree');
   	// $allCategories = $proxy->call($sessionId, 'catalog_category.info', array(10));
   	
   	// $allCategories = $proxy->call($sessionId, 'category.assignedProducts', array(10,1));
   	
   	// $allCategories = $proxy->call($sessionId, 'catalog_category.currentStore');
   	
   	
   	
  } catch (SoapFault $e) {

	print_r($e);
	exit;

  } 
  
}

$json = json_encode($return_data);
header("Content-encoding: gzip");
echo gzencode($json);

?>
<?php
include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);
$and = '';
if(isset($data['cat_id']) && ($data['cat_id'] != '')) {
	$and = ' AND catid = '.$data['cat_id'];
} else {
	$data['cat_id'] = "0";
}

$cat_id = $data['cat_id']; 

include("fetch_limit.php");
$dbresult=mysql_query("SELECT * FROM mst_product WHERE app_id = '$app_id' AND tab_id = '$data[tab_id]' $and".$SQL_LIMIT, $conn);
  
$products = array();
while($product = mysql_fetch_object($dbresult)) {
  
  
  $product_images = unserialize($product->product_image);
  if(!is_array($product_images)) {
  	$product_images = array($product->product_image);
  }
  
  $first_image = "";
  $more_images = array();
  foreach($product_images AS $pi) {
	$product_image = "";
	if(preg_match("/^http:\/\/(.*)$/", $pi) || preg_match("/^http:\/\/(.*)$/", $pi)) {
		$product_image = $pi;	
	} else if($pi != ""){
		$dir = findUploadDirectory($app_id, "products");
		if(file_exists($dir."/".$pi)) {
			$product_image = $WEB_HOME_URL.'/custom_images/'.$data["app_code"].'/'.$pi.'?extra=products';
		}
	}
	
	if($product_image != "") {
		if($first_image == "") {
			$first_image = $product_image; 
		} else {
			$more_images[] = $product_image;
		}
	}
  }

  $product->product_image = $first_image;
  $product->more_images = $more_images;  

    $product->product_Description = str_replace("\n", "<br/>", str_replace("\r\n", "\n", $product->product_Description));
  
  $products[] = $product;
  
}

echo '{"response":{"catid":'.$cat_id.',"Products":'.json_encode($products).'},"status":{"message":"OK","id":200,"method":"appsomen.com/mobilecartsapp/mobile/get_product.php"}}';

?>

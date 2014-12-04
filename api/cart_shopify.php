<?php


include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_GET);

$dbresult=mysql_query("SELECT store_name, base_domain, api_key, api_secret FROM mst_storeinfo WHERE store_name='Shopify' AND app_id = '$app_id' AND tab_id = '$data[tab_id]'", $conn);
if($dbresult) {
	$store = mysql_fetch_object($dbresult);
} else {
	echo 'No configuration for Shopify found.';
	exit;
}


$prodetail=$data['Products'];
 
 ?>

 <body >
 <?php
 $product_id = explode("$#$", $prodetail);
 $data = '';
 for($j=0;$j<count($product_id)-1;$j++)
 {
	if($data == '')
		$data=$data.'id[]='.$product_id[$j];
	else
		$data=$data.'&'.'id[]='.$product_id[$j]; 
 }
 ?>
<form method="POST" action="https://<?php echo $store->base_domain; ?>/cart/add?<?php echo $data;?>" accept-charset="utf-8">
<!-- Sell physical goods and services with possible tax and shipping -->

<input type="hidden" name="return_to" value="/checkout" />  

  
  <input style="border:0px;margin-left:-8px;margin-top:-5px;" type="image"
    name="submit"    
    src="http://www.appsomen.com/mobilecartsapp/mobile/check_btn.png"
    height="39"
    width="155" />

</form>
</body>
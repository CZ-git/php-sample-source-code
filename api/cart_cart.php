<?php

include "dbconnect.inc";
include "app.inc";

$data = make_data_safe($_REQUEST);

$prodetail = $data['Products'];

$sql = "SELECT * FROM mst_options WHERE app_id = '$app_id'AND tab_id = '$data[tab_id]' AND gateway_type = 2 "; 
$res = mysql_query($sql, $conn);
$arry = mysql_fetch_array($res);

$merchant_id = "";
if(isset($arry["gateway_appid"])) $merchant_id = $arry["gateway_appid"];  
?>
 <body  style="background-color:none">
<form method="POST" action="https://sandbox.google.com/checkout/api/checkout/v2/checkoutForm/Merchant/<?php echo $merchant_id; ?> " accept-charset="utf-8">
<!-- Sell physical goods and services with possible tax and shipping -->
<?php 
$pieces = explode("$#$", $prodetail);
for($j=0;$j<count($pieces)-1;$j++) {
 $data=explode(",", $pieces[$j]);
?>
  <input type="hidden" name="item_name_<?php echo $j+1; ?>" value= "<?php echo $data[0];?>"/>
  <input type="hidden" name="item_description_<?php echo $j+1; ?>" value="<?php echo $data[1];?>"/>
  <input type="hidden" name="item_price_<?php echo $j+1; ?>" value="<?php echo $data[2];?>"/>
  <input type="hidden" name="item_currency_<?php echo $j+1; ?>" value="<?php echo $data[3];?>"/>
  <input type="hidden" name="item_quantity_<?php echo $j+1; ?>" value="<?php echo $data[4];?>"/>
  <input type="hidden" name="item_merchant_id_<?php echo $j+1;?>" value="<?php echo $data[5];?>"/>
  
<?php } ?>
<!-- No tax code -->

<!-- No shipping code -->

<input type="hidden" name="_charset_" />

  <!-- Button code -->
  <input style="border:0px;margin-left:-8px;margin-top:-5px;" type="image"
    name="Google Checkout"
    alt="Fast checkout through Google"
    src="http://appsomen.com/images/google_checkout.png"/>

</form>
</body>
<?php
session_start("rest_id");
ob_start();

include "dbconnect.inc";
include "app.inc";

include "ray_model/common.php";
include "ray_model/ordering.php";

$data = make_data_safe($_REQUEST);
include "../ordering_base.php";


$qryrest = $main_info; //get_restaurant_information($conn, $app_id, $data["tab_id"]);
$rest_id = $qryrest["id"];

require_once('includes/config.php');
require_once('includes/paypal.class.php');

if(!$_SESSION['orderstr']) {
	unset($_SESSION['orderstr']);
	unset($_SESSION['totalcharge']);
	header("Location: ../$PAYPAL_INFO[rollback]".$PASS_PARAMS);
	exit;
}

if(!$_SESSION['totalcharge'])
{
	unset($_SESSION['orderstr']);
	unset($_SESSION['totalcharge']);
	header("Location: ../$PAYPAL_INFO[rollback]".$PASS_PARAMS);
	exit;
}

$sqlorders = "SELECT * FROM `orders` WHERE order_type IN (1,2,3) AND order_str='".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"]." group by order_detail";
$resorders = mysql_query($sqlorders, $conn);
$resorders1 = mysql_query($sqlorders, $conn);

//echo $sqlorders."<br>";

$totaltax = $_SESSION['totaltax'];
$totalitemcost = 0;

if (mysql_num_rows($resorders) !=0)	{
	if(!session_id()) session_start();
	
	//echo "Tax".$totaltax."Delivery:".$qryrest["delivery_fee"];

	$PayPalConfig = array('APIUsername' => $api_username, 'APIPassword' => $api_password, 'APISignature' => $api_signature, 'Sandbox' => $sandbox);
	$PayPal = new PayPal($PayPalConfig);
	
	while($qryorders1 = mysql_fetch_array($resorders1)) {
		$totalitemcost= $qryorders1["order_total"]*$qryorders1[quantity] + $totalitemcost;
		if($qryorders1["order_type"]=="1" && $totalitemcost <= $qryrest["free_delivery_amount"])
		{	
			$deliveryfee = $qryrest["delivery_fee"];
		}
		else
		{
			$deliveryfee = '';
		}
	}
	
	/*$PayPalConfig = array('Sandbox' => $sandbox);
	$PayPal = new PayPal($PayPalConfig);*/
	
	$GECDResult = $PayPal -> GetExpressCheckoutDetails($_SESSION['PayPalResult']['TOKEN']);
	//echo '<b>GetExpressCheckoutDetails</b><br /><pre>';
	//print_r($GECDResult);
	//echo '<br /><br /></pre>';
	
	$DECPFields = array(
						'token' => $_SESSION['PayPalResult']['TOKEN'], 								// Required.  A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
						'payerid' => $GECDResult['PAYERID'], 							// Required.  Unique PayPal customer id of the payer.  Returned by GetExpressCheckoutDetails, or if you used SKIPDETAILS it's returned in the URL back to your RETURNURL.
						'returnfmfdetails' => '1', 					// Flag to indiciate whether you want the results returned by Fraud Management Filters or not.  1 or 0.
						'giftmessage' => '', 						// The gift message entered by the buyer on the PayPal Review page.  150 char max.
						'giftreceiptenable' => '', 					// Pass true if a gift receipt was selected by the buyer on the PayPal Review page. Otherwise pass false.
						'giftwrapname' => '', 						// The gift wrap name only if the gift option on the PayPal Review page was selected by the buyer.
						'giftwrapamount' => '', 					// The amount only if the gift option on the PayPal Review page was selected by the buyer.
						'buyermarketingemail' => '', 				// The buyer email address opted in by the buyer on the PayPal Review page.
						'surveyquestion' => '', 					// The survey question on the PayPal Review page.  50 char max.
						'surveychoiceselected' => '',  				// The survey response selected by the buyer on the PayPal Review page.  15 char max.
						'allowedpaymentmethod' => '', 				// The payment method type. Specify the value InstantPaymentOnly.
						'buttonsource' => '' 						// ID code for use by third-party apps to identify transactions in PayPal. 
					);
							
	$Payments = array();
	$Payment = array(
					'amt' => $_SESSION['totalcharge'], 							// Required.  The total cost of the transaction to the customer.  If shipping cost and tax charges are known, include them in this value.  If not, this value should be the current sub-total of the order.
					'currencycode' => $qryrest["currency"], 					// A three-character currency code.  Default is USD.
					'itemamt' => $totalitemcost, 						// Required if you specify itemized L_AMT fields. Sum of cost of all items in this order.  
					'shippingamt' => $deliveryfee, 					// Total shipping costs for this order.  If you specify SHIPPINGAMT you mut also specify a value for ITEMAMT.
					'insuranceoptionoffered' => '', 		// If true, the insurance drop-down on the PayPal review page displays the string 'Yes' and the insurance amount.  If true, the total shipping insurance for this order must be a positive number.
					'handlingamt' => $qryrest["convenience_fee"], 					// Total handling costs for this order.  If you specify HANDLINGAMT you mut also specify a value for ITEMAMT.
					'taxamt' => $totaltax, 						// Required if you specify itemized L_TAXAMT fields.  Sum of all tax items in this order. 
					'desc' => '', 							// Description of items on the order.  127 char max.
					'custom' => '', 						// Free-form field for your own use.  256 char max.
					'invnum' => $_SESSION['orderstr'], 						// Your own invoice or tracking number.  127 char max.
					'notifyurl' => '',  						// URL for receiving Instant Payment Notifications
					'shiptoname' => '', 					// Required if shipping is included.  Person's name associated with this address.  32 char max.
					'shiptostreet' => '', 					// Required if shipping is included.  First street address.  100 char max.
					'shiptostreet2' => '', 					// Second street address.  100 char max.
					'shiptocity' => '', 					// Required if shipping is included.  Name of city.  40 char max.
					'shiptostate' => '', 					// Required if shipping is included.  Name of state or province.  40 char max.
					'shiptozip' => '', 						// Required if shipping is included.  Postal code of shipping address.  20 char max.
					'shiptocountry' => '', 					// Required if shipping is included.  Country code of shipping address.  2 char max.
					'shiptophonenum' => '',  				// Phone number for shipping address.  20 char max.
					'notetext' => '', 						// Note to the merchant.  255 char max.  
					'allowedpaymentmethod' => '', 			// The payment method type.  Specify the value InstantPaymentOnly.
					'paymentaction' => 'Sale', 					// How you want to obtain the payment.  When implementing parallel payments, this field is required and must be set to Order. 
					'paymentrequestid' => '',  				// A unique identifier of the specific payment request, which is required for parallel payments. 
					'sellerpaypalaccountid' => ''			// A unique identifier for the merchant.  For parallel payments, this field is required and must contain the Payer ID or the email address of the merchant.
					);
					
	$PaymentOrderItems = array();
	while($qryorders = mysql_fetch_array($resorders)) {
		$order_name = "";
		$order_desc = "";
		
		$ord_detail = unserialize($qryorders["order_detail"]);
		if(is_array($ord_detail)) {
			$order_name = $ord_detail[0]["name"];
			for($i=1; $i<count($ord_detail); $i++) {
				if($ord_detail[$i]["name"] != "") {
					if($order_desc != "") $order_desc .= ",";
					$order_desc .= $ord_detail[$i]["name"]."(".$ord_detail[$i]["cost"].")";
				}
			}
		} else {
			$order_name = $ord_detail; 
		}
		
		$order_name = mb_substr($order_name, 0, 127, 'UTF-8');
		$order_desc = mb_substr($order_desc, 0, 127, 'UTF-8');
	
		$Item = array(
					'name' => $order_name, 							// Item name. 127 char max.
					'desc' => $order_desc, 							// Item description. 127 char max.
					'amt' => $qryorders["order_total"], 								// Cost of item.
					'number' => $qryorders["item_id"], 							// Item number.  127 char max.
					'qty' => $qryorders["quantity"], 								// Item qty on order.  Any positive integer.
					'taxamt' => '', 							// Item sales tax
					'itemurl' => $domain . $PAYPAL_INFO[callback].$PASS_PARAMS.'&item_id='.$qryorders["item_id"], 							// URL for the item.
					'itemweightvalue' => '', 					// The weight value of the item.
					'itemweightunit' => '', 					// The weight unit of the item.
					'itemheightvalue' => '', 					// The height value of the item.
					'itemheightunit' => '', 					// The height unit of the item.
					'itemwidthvalue' => '', 					// The width value of the item.
					'itemwidthunit' => '', 					// The width unit of the item.
					'itemlengthvalue' => '', 					// The length value of the item.
					'itemlengthunit' => '',  					// The length unit of the item.
					'ebayitemnumber' => '', 					// Auction item number.  
					'ebayitemauctiontxnid' => '', 			// Auction transaction ID number.  
					'ebayitemorderid' => '',  				// Auction order ID number.
					'ebayitemcartid' => ''					// The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
					);
		array_push($PaymentOrderItems, $Item);
	}
	/*$Item = array(
				'name' => 'Widget 456', 							// Item name. 127 char max.
				'desc' => 'Widget 456', 							// Item description. 127 char max.
				'amt' => '40.00', 								// Cost of item.
				'number' => '456', 							// Item number.  127 char max.
				'qty' => '1', 								// Item qty on order.  Any positive integer.
				'taxamt' => '', 							// Item sales tax
				'itemurl' => 'http://www.angelleye.com/products/456.php', 							// URL for the item.
				'itemweightvalue' => '', 					// The weight value of the item.
				'itemweightunit' => '', 					// The weight unit of the item.
				'itemheightvalue' => '', 					// The height value of the item.
				'itemheightunit' => '', 					// The height unit of the item.
				'itemwidthvalue' => '', 					// The width value of the item.
				'itemwidthunit' => '', 					// The width unit of the item.
				'itemlengthvalue' => '', 					// The length value of the item.
				'itemlengthunit' => '',  					// The length unit of the item.
				'ebayitemnumber' => '', 					// Auction item number.  
				'ebayitemauctiontxnid' => '', 			// Auction transaction ID number.  
				'ebayitemorderid' => '',  				// Auction order ID number.
				'ebayitemid' => ''					// The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
				);
	array_push($PaymentOrderItems, $Item);*/
	
	$Payment['order_items'] = $PaymentOrderItems;
	array_push($Payments, $Payment);				
	
	$UserSelectedOptions = array(
								 'shippingcalculationmode' => '', 	// Describes how the options that were presented to the user were determined.  values are:  API - Callback   or   API - Flatrate.
								 'insuranceoptionselected' => '', 	// The Yes/No option that you chose for insurance.
								 'shippingoptionisdefault' => '', 	// Is true if the buyer chose the default shipping option.  
								 'shippingoptionamount' => '', 		// The shipping amount that was chosen by the buyer.
								 'shippingoptionname' => '', 		// Is true if the buyer chose the default shipping option...??  Maybe this is supposed to show the name..??
								 );
	
	$PayPalRequest = array(
						   'DECPFields' => $DECPFields, 
						   'Payments' => $Payments
						   );
	
	$_SESSION['PayPalResult'] = $PayPal -> DoExpressCheckoutPayment($PayPalRequest);
	
	//echo '<pre />';
	//print_r($_SESSION['PayPalResult']);
	//exit;

	if( is_array($_SESSION['PayPalResult']) && $_SESSION['PayPalResult']['ACK'] == 'Success') { // Payment successful
		// We'll fetch the transaction ID for internal bookkeeping
		$transactionId = $_SESSION['PayPalResult']['PAYMENTINFO_0_TRANSACTIONID'];
		$Payerid = $_SESSION['PayPalResult']['REQUESTDATA']['PAYERID'];
		$token = $_SESSION['PayPalResult']['TOKEN'];
		$timestamp = $_SESSION['PayPalResult']['TIMESTAMP'];
		
		if(isset($transactionId)) {
			$dateplaced = date_create();
			$sqlorder = "UPDATE orders 
						SET
						transaction_id = '$transactionId',
						order_state = '0',
						paid = '1',
						checkout_method = '1',
						paid_amount = '".$_SESSION['totalcharge']."',
						placed_on =  '".date_format($dateplaced, 'Y-m-d H:i:s')."'					
						WHERE order_str = '".$_SESSION['orderstr']."' and tab_id=".$data["tab_id"];

			$resorder = mysql_query($sqlorder, $conn);
		}
		
		//echo $PASS_PARAMS;
		//exit;

		header("Location: ../$PAYPAL_INFO[callback]".$PASS_PARAMS."&action=successful");
		exit;
	}
	else
	{
		$token = $_SESSION['PayPalResult']['TOKEN'];
		$timestamp = $_SESSION['PayPalResult']['TIMESTAMP'];
		$sqlorder = "UPDATE orders 
					SET 
					order_state = '5'					
					WHERE order_str = '".$_SESSION['orderstr']."' and tab_id=".$rest_id;
		$resorder = mysql_query($sqlorder, $conn);
	}
	
}

header("Location: ../$PAYPAL_INFO[callback]".$PASS_PARAMS."&action=checkoutfailure");

?>
<?php
session_start("rest_id");
ob_start();

include_once "dbconnect.inc";
include_once "app.inc";

include_once "ray_model/common.php";
include_once "ray_model/ordering.php";

$data = make_data_safe($_REQUEST);
include_once "../ordering_base.php";

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

$qryrest = get_restaurant_information($conn, $app_id, $data["tab_id"]);

$sqlorders = "SELECT * FROM `orders` WHERE order_type IN (1,2,3) AND order_str='".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"]." group by order_detail";
$resorders = mysql_query($sqlorders, $conn);
$resorders1 = mysql_query($sqlorders, $conn);

$totaltax = $_SESSION['totaltax'];
$totalitemcost = 0;

if (mysql_num_rows($resorders) !=0)	{
	if(!session_id()) session_start();
	$PayPalConfig = array('APIUsername' => $api_username, 'APIPassword' => $api_password, 'APISignature' => $api_signature, 'Sandbox' => $sandbox);
	
	$PayPal = new PayPal($PayPalConfig);
	//$qryorders1 = mysql_fetch_array($resorders1);
	//echo "<pre>";
	//echo "total Rows".mysql_num_rows($resorders);
	//print_r($qryorders=mysql_fetch_array($resorders)); // or var_dump()*/
	
	while($qryorders1 = mysql_fetch_array($resorders1)) {
		//echo "<br>Total Items Cost: $totalitemcost >>> Item cost $qryorders1[order_total]<br>$qryorders1[quantity]";
		$totalitemcost= $qryorders1["order_total"]*$qryorders1[quantity] + $totalitemcost;
		if($qryorders1["order_type"]=="1" && $totalitemcost <= $qryrest["free_delivery_amount"]) {	
			$deliveryfee = $qryrest["delivery_fee"];
		} else {
			$deliveryfee = '';
		}
	}
		//	echo "<br>Final Total Item Cost: $totalitemcost<br> Total Charge: $_SESSION[totalcharge]";		
	$SECFields = array(
					'token' => '', 								// A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
					'maxamt' => $_SESSION['totalcharge'], 						// The expected maximum total amount the order will be, including S&H and sales tax.
					'returnurl' => $domain . $PAYPAL_INFO[checkout].$PASS_PARAMS, 							// Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
					'cancelurl' => $domain . $PAYPAL_INFO[cancel].'action=cancelorder'."&".$PASS_PARAMS, 							// Required.  URL to which the customer will be returned if they cancel payment on PayPal's site.
					'callback' => '', 							// URL to which the callback request from PayPal is sent.  Must start with https:// for production.
					'callbacktimeout' => '', 					// An override for you to request more or less time to be able to process the callback request and response.  Acceptable range for override is 1-6 seconds.  If you specify greater than 6 PayPal will use default value of 3 seconds.
					'callbackversion' => '', 					// The version of the Instant Update API you're using.  The default is the current version.							
					'reqconfirmshipping' => '', 				// The value 1 indicates that you require that the customer's shipping address is Confirmed with PayPal.  This overrides anything in the account profile.  Possible values are 1 or 0.
					'noshipping' => '0', 						// The value 1 indiciates that on the PayPal pages, no shipping address fields should be displayed.  Maybe 1 or 0.
					'allownote' => '1', 							// The value 1 indiciates that the customer may enter a note to the merchant on the PayPal page during checkout.  The note is returned in the GetExpresscheckoutDetails response and the DoExpressCheckoutPayment response.  Must be 1 or 0.
					'addroverride' => '', 						// The value 1 indiciates that the PayPal pages should display the shipping address set by you in the SetExpressCheckout request, not the shipping address on file with PayPal.  This does not allow the customer to edit the address here.  Must be 1 or 0.
					'localecode' => '', 						// Locale of pages displayed by PayPal during checkout.  Should be a 2 character country code.  You can retrive the country code by passing the country name into the class' GetCountryCode() function.
					'pagestyle' => '', 							// Sets the Custom Payment Page Style for payment pages associated with this button/link.  
					'hdrimg' => $qryrest["logo_url"], 							// URL for the image displayed as the header during checkout.  Max size of 750x90.  Should be stored on an https:// server or you'll get a warning message in the browser.
					'hdrbordercolor' => '', 					// Sets the border color around the header of the payment page.  The border is a 2-pixel permiter around the header space.  Default is black.  
					'hdrbackcolor' => '', 						// Sets the background color for the header of the payment page.  Default is white.  
					'payflowcolor' => '', 						// Sets the background color for the payment page.  Default is white.
					'skipdetails' => '1', 						// This is a custom field not included in the PayPal documentation.  It's used to specify whether you want to skip the GetExpressCheckoutDetails part of checkout or not.  See PayPal docs for more info.
					'email' => '', 								// Email address of the buyer as entered during checkout.  PayPal uses this value to pre-fill the PayPal sign-in page.  127 char max.
					'solutiontype' => 'Mark', 						// Type of checkout flow.  Must be Sole (express checkout for auctions) or Mark (normal express checkout)
					'landingpage' => 'Billing', 						// Type of PayPal page to display.  Can be Billing or Login.  If billing it shows a full credit card form.  If Login it just shows the login screen.
					'channeltype' => '', 						// Type of channel.  Must be Merchant (non-auction seller) or eBayItem (eBay auction)
					'giropaysuccessurl' => '', 					// The URL on the merchant site to redirect to after a successful giropay payment.  Only use this field if you are using giropay or bank transfer payment methods in Germany.
					'giropaycancelurl' => '', 					// The URL on the merchant site to redirect to after a canceled giropay payment.  Only use this field if you are using giropay or bank transfer methods in Germany.
					'banktxnpendingurl' => '',  				// The URL on the merchant site to transfer to after a bank transfter payment.  Use this field only if you are using giropay or bank transfer methods in Germany.
					'brandname' => $qryrest["restaurant_name"], 							// A label that overrides the business name in the PayPal account on the PayPal hosted checkout pages.  127 char max.
					'customerservicenumber' => '', 				// Merchant Customer Service number displayed on the PayPal Review page. 16 char max.
					'giftmessageenable' => '', 					// Enable gift message widget on the PayPal Review page. Allowable values are 0 and 1
					'giftreceiptenable' => '', 					// Enable gift receipt widget on the PayPal Review page. Allowable values are 0 and 1
					'giftwrapenable' => '', 					// Enable gift wrap widget on the PayPal Review page.  Allowable values are 0 and 1.
					'giftwrapname' => '', 						// Label for the gift wrap option such as "Box with ribbon".  25 char max.
					'giftwrapamount' => '', 					// Amount charged for gift-wrap service.
					'buyeremailoptionenable' => '', 			// Enable buyer email opt-in on the PayPal Review page. Allowable values are 0 and 1
					'surveyquestion' => '', 					// Text for the survey question on the PayPal Review page. If the survey question is present, at least 2 survey answer options need to be present.  50 char max.
					'surveyenable' => '', 						// Enable survey functionality. Allowable values are 0 and 1
					'buyerid' => '', 							// The unique identifier provided by eBay for this buyer. The value may or may not be the same as the username. In the case of eBay, it is different. 255 char max.
					'buyerusername' => '', 						// The user name of the user at the marketplaces site.
					'buyerregistrationdate' => '',  			// Date when the user registered with the marketplace.
					'allowpushfunding' => ''					// Whether the merchant can accept push funding.  0 = Merchant can accept push funding : 1 = Merchant cannot accept push funding.			
				);

// Basic array of survey choices.  Nothing but the values should go in here.  
$SurveyChoices = array();

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
			'ebayitemcartid' => ''					// The unique identifier provided by eBay for this order from the buyer. These parameters must be ordered sequentially beginning with 0 (for example L_EBAYITEMCARTID0, L_EBAYITEMCARTID1). Character length: 255 single-byte characters
			);
array_push($PaymentOrderItems, $Item);*/

$Payment['order_items'] = $PaymentOrderItems;
array_push($Payments, $Payment);

$BuyerDetails = array(
						'buyerid' => '', 				// The unique identifier provided by eBay for this buyer.  The value may or may not be the same as the username.  In the case of eBay, it is different.  Char max 255.
						'buyerusername' => '', 			// The username of the marketplace site.
						'buyerregistrationdate' => ''	// The registration of the buyer with the marketplace.
						);
						
// For shipping options we create an array of all shipping choices similar to how order items works.
$ShippingOptions = array();
$Option = array(
				'l_shippingoptionisdefault' => '', 				// Shipping option.  Required if specifying the Callback URL.  true or false.  Must be only 1 default!
				'l_shippingoptionname' => '', 					// Shipping option name.  Required if specifying the Callback URL.  50 character max.
				'l_shippingoptionlabel' => '', 					// Shipping option label.  Required if specifying the Callback URL.  50 character max.
				'l_shippingoptionamount' => '' 					// Shipping option amount.  Required if specifying the Callback URL.  
				);
array_push($ShippingOptions, $Option);
		
$BillingAgreements = array();
$Item = array(
			  'l_billingtype' => '', 							// Required.  Type of billing agreement.  For recurring payments it must be RecurringPayments.  You can specify up to ten billing agreements.  For reference transactions, this field must be either:  MerchantInitiatedBilling, or MerchantInitiatedBillingSingleSource
			  'l_billingagreementdescription' => '', 			// Required for recurring payments.  Description of goods or services associated with the billing agreement.  
			  'l_paymenttype' => '', 							// Specifies the type of PayPal payment you require for the billing agreement.  Any or IntantOnly
			  'l_billingagreementcustom' => ''					// Custom annotation field for your own use.  256 char max.
			  );
array_push($BillingAgreements, $Item);

$PayPalRequest = array(
					   'SECFields' => $SECFields, 
					   'SurveyChoices' => $SurveyChoices, 
					   'Payments' => $Payments
					   );

$_SESSION['PayPalResult'] = $PayPal -> SetExpressCheckout($PayPalRequest);

	if( is_array($_SESSION['PayPalResult']) && $_SESSION['PayPalResult']['ACK'] == 'Success') { // Payment successful
		// We'll fetch the transaction ID for internal bookkeeping
		$token = $_SESSION['PayPalResult']['TOKEN'];
		if($token)
		{
			//echo "Your ordered has already been confirmed";
			header("Location:".$_SESSION['PayPalResult']['REDIRECTURL']);
			//echo $_SESSION['PayPalResult']['REDIRECTURL'];
		}
	} else if( is_array($_SESSION['PayPalResult']) && $_SESSION['PayPalResult']['ACK'] == 'Failure') { // Payment unsuccessful
		if( $_SESSION['PayPalResult']['L_SHORTMESSAGE0'] == 'Duplicate invoice') { // order already made
			header("Location: ../$PAYPAL_INFO[callback]".$PASS_PARAMS."&action=orderduplicate");
		} else {
			header("Location: ../$PAYPAL_INFO[callback]".$PASS_PARAMS."&action=checkoutfailure");
		}
	} else {
		header("Location: ../$PAYPAL_INFO[cart]".$PASS_PARAMS);
		exit;
	}
//echo '<a href="' . $_SESSION['PayPalResult']['REDIRECTURL'] . '">Click here to continue.</a><br /><br />';
//echo '<pre />';
//print_r($_SESSION['PayPalResult']);
//echo "</pre><br>";
}
else
{
		//unset($_SESSION['orderstr']);
		//unset($_SESSION['totalcharge']);
		//header("Location: ../cart.php");
		header("Location: ../$PAYPAL_INFO[cart]".$PASS_PARAMS);
		exit;
	}
?>
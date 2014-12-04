<?php

/* Common setup for Livebookings API functions
*/

if ($_GET["tab_id"])
  $and = "and id = '$_GET[tab_id]' ";

$sql = "select value1, value2 from app_tabs
        where app_id = '$app_id' 
        and view_controller = 'RestaurantBookingViewController' $and";
$res = mysql_query($sql, $conn);

// Restaurant API code stored in value1, Restaurant ID in value2 - TBC
// Test
// $mSoapClient = new SoapClient("http://integration.livebookings.net/webservices/external/service.asmx?WSDL");
// Live
$mSoapClient = new SoapClient("http://book.livebookings.net/webservices/external/service.asmx?WSDL");
$mLanguages = "en-GB";
$mPartnerCode = mysql_result($res, 0, 0);
$Id = mysql_result($res, 0, 1);


?>

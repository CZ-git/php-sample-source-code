<?php

include "dbconnect.inc";
include "app.inc";
include "livebookings.inc";

$sessionId = $_GET["sessionId"];   // LUNCH
$date = $_GET["date"];             // YYYY-MM-DD
$size = $_GET["size"];             // 4
$locationId = $_GET["locationId"]; // 17887, 

$request = array(
            "Languages"=>$mLanguages,
            "PartnerCode"=>$mPartnerCode,
            "SessionId"=>$sessionId,
            "DiningDateAndTime"=>$date,
            "Size"=>$size,
            "RestaurantLocationId" => $locationId,
            "ReturnSessionMessage"=>false,
            "ReturnReturnMessage"=>false,
            );

try {
  $response = $mSoapClient->SearchAvailabilityOneLocation($request);
} catch(Exception $e) {
  echo 'Error occured: ' .$e->getMessage();
}

if (count($response->Location->Result)) {

  foreach ($response->Location->Result as $item) {
    $t = explode("T", $item->time);
    $times[] = array("description" => $t[1],
                    "id" => $item->correlationData);
  }
  $feed[] = array("times" => $times);
}
else {
  $feed[] = array("times" => array());
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
?>

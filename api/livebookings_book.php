<?php

include "dbconnect.inc";
include "app.inc";
include "livebookings.inc";

$data = $_REQUEST;

$sessionId = $data["sessionId"];
$date = $data["date"];
$time = $data["time"];
$datetime = $date."T".$time;
$size = $data["size"];
$locationId = $data["locationId"];;
$correlationData = $data["correlationData"];

$first_name = $data["first_name"];
$last_name = $data["last_name"];
$email = $data["email"];
$mobile = $data["mobile"];

$request = array(
            "Languages"=>$mLanguages,
            "PartnerCode"=>$mPartnerCode,
            "RestaurantLocationId" => $locationId,
            "SessionId"=>$sessionId,
            "DiningDateAndTime"=>$datetime,
            "CorrelationData"=>$correlationData,
            "Size"=>$size
            );

// print "<H1>Request</H1>";
// print "<PRE>";
// print_r($request);
// print "</PRE>";
// 

try {
  $response = $mSoapClient->PrepareBookReservation($request);
} catch(Exception $e) {
  echo 'Error occured: ' .$e->getMessage();
}

// print "<PRE>";
// print_r($response);

if ($response->StillAvailable && !$data["test_not_available"]) {

  // We're only using this to comply with the correct sequence for booking
  // if $response->RequiresCreditCard is true, we should collect card details
  // at this stage.  However, this is not implemented (yet).
  // Providing the slot is still available, we just make the booking here.

  $booker = array("UserWithoutALogin" =>
                     array("FirstName" => $first_name,
                           "LastName" => $last_name,
                           "EMail" => $email,
                           "MobilePhoneNumber" => $mobile,
                           "Title" => "",
                           "GuestAcceptsEmailMarketingFromPartner" => "false"));

  $request = array(
              "Languages"=>$mLanguages,
              "PartnerCode"=>$mPartnerCode,
              "RestaurantLocationId" => $locationId,
              "SessionId"=>$sessionId,
              "DiningDateAndTime"=>$datetime,
              "CorrelationData"=>$correlationData,
              "Size"=>$size,
              "Booker"=>$booker,
              "GuestAcceptsEmailMarketingFromRestaurant" => "false",
              "SuppressCustomerConfirmations" => "false",
              "SuppressRestaurantConfirmations" => "false",
              "PerformPrePay" => "false",
              "CancelLink" => "FuManChu",
              "SpecialRequests" => "None"
              );

  // print "<H1>Request</H1>";
  // print "<PRE>";
  // print_r($request);
  // print "</PRE>";

  try {
    $response = $mSoapClient->BookReservation($request);
  } catch(Exception $e) {
    echo 'Error occured: ' .$e->getMessage();
  }

  // print "<PRE>";
  // print_r($response);

  if ($response->ConfirmationNumber) {
    $feed[] = array("confirmation_number" => $response->ConfirmationNumber,
                  "reservation_id" => $response->ReservationId);
  }
  else {
    $feed[] = array("error" => "Sorry, we could not make your reservation at this time");
  }
  
}
else {
  $feed[] = array("error" => "Sorry, the time you requested is no longer available");
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

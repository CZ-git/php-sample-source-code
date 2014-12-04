<?php

include "web_tiers.php";
/*
include "dbconnect.inc";
include "app.inc";


include "livebookings.inc";


$request = array(
            "Languages"=>$mLanguages,
            "PartnerCode"=>$mPartnerCode,
            "Id"=>(int)$Id // by internal Id
            );

// $response = $mSoapClient->GetRestaurantDetail($getRestaurantDetailRequest);

try {
  $response = $mSoapClient->GetRestaurantDetail($request);
} catch(Exception $e) {
  echo 'Error occured: ' .$e->getMessage();
  exit;
}


$locations = array();
$sessions = array();
if (is_array($loc = $response->Restaurant->Location)) {
  for($i=0; $i<count($loc); $i++) {
    $locations[] = array("id" =>$loc[$i]->Id,
                         "description" => $loc[$i]->Name);

// I currently have no way of knowing how to build the session list when there are multiple locations!
// TODO!!

  }
}
else {
  $locations = array(array("id" => $loc->id,
                     "description" => $loc->Name));
  $location_sessions = $loc->EnabledSessions;
}

if (is_array($sess = $location_sessions->Session)) {
  for($i=0; $i<count($sess); $i++) {
    $sessions[] = array("id" =>$sess[$i]->id,
                      "description" =>$sess[$i]->Name->_);
  }
}

$image_file = "../../uploads/images/$app_id/livebookings.jpg";
if (file_exists($image_file))
  $image = base64_encode(file_get_contents($image_file));
else
  $image = "";

$button_file = "../../uploads/images/$app_id/button.png";
if (file_exists($button_file))
  $button = base64_encode(file_get_contents($button_file));
else
  $button = "";


$feed[] = array("locations" => $locations,
                "sessions"  => $sessions,
                "image" => $image,
                "CustomButton" => $button);


$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);
*/
?>

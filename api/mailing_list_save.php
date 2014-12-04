<?php

include_once "dbconnect.inc";
include_once "app.inc";

$app = get_app_record($conn, $app_id);
$data = make_data_safe($_REQUEST);

$sql = "SELECT id FROM mailing_list WHERE app_id = '$app[id]' AND email = '$data[email]'";
$res = mysql_query($sql, $conn);
if ( mysql_num_rows($res) > 0 ) {
    //$mc_results = 'You have already subscribed.';
	$mc_results = 1;
} else {
	
	$mc_results = 0;

    $addsql = "INSERT INTO mailing_list (app_id, email, name, birthday, postalcode, country, comment)
            VALUES ('$app[id]', '$data[email]', '$data[name]', '$data[birthday]', '$data[postalcode]', '$data[country]', '".addslashes($data[comment])."')";
    $debug .= $addsql."\n";

    $res = mysql_query($addsql, $conn);
	$ml_newid = mysql_insert_id($conn);
		
    if ($data["categories"]) {
      $subscriber_id = $ml_newid;
      $c = explode(",", $data["categories"]);
      foreach ($c as $category_id) {
        if ($category_id > 0) {
          $sql = "INSERT INTO mailing_list_subscriptions (subscriber_id, category_id) VALUES ('$subscriber_id', '$category_id')";
          $res = mysql_query($sql, $conn);
        }
      }
    }

    /*if($app["mailing_list_upload"] == "0") {
        echo json_encode( array( array("result"=>"Subscribe data has been registered successfully.") ) );
        exit;
    }*/

    if ($data["tab_id"]) {

		$t = get_app_tab_record($conn, $data["tab_id"]);

		if(!$t) exit;

		$mapper = array(
			'type' =>'',
			'mc' => array(
				'apikey' => '',
				'listid' => '',
			),
			'ic' => array(
				'apiid' => '',
				'apiuser' => '',
				'apipassword' => '',
				'accountid' => '',
				'folderid' => '',
				'listid' => '',
			),
			'cc' => array(
				'apikey' => '',
				'apiuser' => '',
				'apipassword' => '',
				'listid' => '',
			),
			'cm' => array(
				'apikey' => '',
				'folderid' => '',
				'listid' => '',
			),
			'gr' => array(
				'apikey' => '',
				'listid' => '',
			),
			'em' => array(
				'apiuser' => '',
				'apipassword' => '',
				'accountid' => '',
				'listid' => '',
			),
			'sp' => array(
				'email' => '',
			),
		);

		$v2 = unserialize($t["value2"]);
		if(!is_array($v2)) {
			$v2 = array('type'=>'mc', 'key' => $v2);
		}
		$v3 = unserialize($t["value3"]);
		if(is_array($v3) && isset($v3["type"])) {
			$nv = array_merge_recursive ($mapper,$v3); 
		} else { // This is for old data
			$nv = $mapper;
			if($v2['type'] == '1') {
				$nv["type"] = "mc";
				$nv["mc"]["apikey"] = $v2['key']; 
				$nv["mc"]["listid"] = $t["value3"];
			} else if($v2['type'] == '2') {
				$nv["type"] = "ic";
				$nv["ic"]["accountid"] = $v2['key']; 
				$v3 = unserialize($t["value3"]);
				$nv["ic"]["apiid"] = $v3['apiid'];
				$nv["ic"]["apiuser"] = $v3['apiuser'];
				$nv["ic"]["apipassword"] = $v3['apipass'];
				$nv["ic"]["folderid"] = $v3['folderid'];
				$nv["ic"]["listid"] = $v3['listid'];
			} else if($v2['type'] == '3') {
				$nv["type"] = "cc";
				$nv["cc"]["apikey"] = $v2['key'];
				$v3 = unserialize($t["value3"]);
				$nv["cc"]["apiuser"] = $v3['apiuser'];
				$nv["cc"]["apipassword"] = $v3['apipass'];
				$nv["cc"]["listid"] = $v3['listid']; 
			} else if($v2['type'] == '4') {
				$nv["type"] = "cm";
				$nv["cm"]["apikey"] = $v2['key'];
				$v3 = unserialize($t["value3"]); 
				$nv["cm"]["folderid"] = $v3['folderid'];
				$nv["cm"]["listid"] = $v3['listid'];
			} else if($v2['type'] == '5') {
				$nv["type"] = "gr";
				$nv["gr"]["apikey"] = $v2['key'];
				$nv["gr"]["listid"] = $t["value3"];
			}
		}

		$emails = array(
			array("email" => $data['email'], "name" => $data['name'], "birthday" => $data['birthday'], "postalcode" => $data['postalcode'], "country" => $data['country'], "comment" => $data['comment'], "categories" => $data['categories'])
		);

		foreach($nv AS $key => $value) {
			
			if(($key == "os")) continue;
			if(($key == "sp")) continue;

			if($key == "type") {
				$nv[$key] = (is_array($nv[$key]))?end($nv[$key]):$value;
				continue;
			}
			foreach($value AS $nk => $nvalue) {
				$nv[$key][$nk] = (is_array($nvalue))?end($nvalue):$nvalue;
			}
		}

		include_once("mc_upload_element.php");
    }    
}

echo json_encode( array( array("result"=>$mc_results) ) );

?>
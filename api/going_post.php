<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_REQUEST);

$data["type"] = get_app_tab_type($conn, $data["tab_id"]);

$stuff = "going986" . $data["user_id"] . "bizapps" . $data["user_type"];
$hash = md5($stuff);

if (strtoupper($hash) != strtoupper($data["hash"])) {
  echo '[{"error":"9"}]'; // Wrong Hash
  exit;
}

$required_fields = array("tab_id", "name");
foreach($required_fields as $field) {
  if (!$data[$field]) {
    echo '[{"error":"7"}]'; // Mandatory missing
  	exit;
  }
}

$set = "
	app_id = '$app_id',
	tab_id = '$data[tab_id]',
	detail_type = '$data[type]',
	detail_id = '$data[id]',
	user_id = '$data[user_id]',
	user_type = '$data[user_type]',
	name = '$data[name]',
	comment = '$data[comment]',
	created = '".gmdate("Y-m-d H:i:s")."',
	going = '1'
";
$sql ="INSERT INTO app_user_going SET ".$set." on duplicate key update ".$set;

$res = mysql_query($sql, $conn);

if (!$res) {
	echo '[{"error":"1"}]';
} else {
	echo '[{"error":"0"}]';
}

?>

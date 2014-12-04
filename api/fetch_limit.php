<?php
$SQL_LIMIT = "";

if(($data["offset"] != "") || ($data["count"] != "")) {
	if(intval($data["count"]) == 0) $data["count"] = "30";
	if(intval($data["count"]) > 30) $data["count"] = "30";
	
	$SQL_LIMIT = " limit " . intval($data["count"]) . " offset " . intval($data["offset"]);
}
?>
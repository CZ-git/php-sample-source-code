<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data[tab_id]);

if($t["view_controller"] == "CustomFormViewController") {
	include_once "web_tiers_customform.php";
} else {
	include_once "web_tiers_web.php";
}


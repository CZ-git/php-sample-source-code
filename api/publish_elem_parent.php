<?php
//----------------------------------------------------------
// Find parent partner information.
//----------------------------------------------------------

$actual_admin_user = get_admin_user_record($conn, $a["admin_user_id"], "id");
$parent_id = $actual_admin_user ["partner_id"];

$sql = "SELECT * FROM partners WHERE id = 1";
$res = mysql_query($sql, $conn);
$default_parent = mysql_fetch_array($res);
$default_parent["cc"] = $default_parent["contact_email"].","."support@appsomen.com";

if($parent_id == "") $parent_id = "1";
$sql = "SELECT * FROM partners WHERE id = '".$parent_id."'";
$res = mysql_query($sql, $conn);
$parent_partner = mysql_fetch_array($res);

if(!$parent_partner) {
	$parent_partner = $default_parent;
} else {
	$parent_partner["cc"] = $parent_partner["contact_email"];
	if($parent_partner["id"] == "1") {
		//$parent_partner["cc"] .= ",support@appsomen.com";
	}
}


$mail_template_path = "/home/bizapps/public_html/reseller/";
/*$parent_admin_user = get_admin_user_record($conn, $parent_partner["id"], "partner_id");
if($parent_admin_user["partner_code"] && file_exists("/home/bizapps/public_html/".$parent_admin_user["partner_code"]."/reseller/")) {
	$mail_template_path = "/home/bizapps/public_html/".$parent_admin_user["partner_code"]."/reseller/";
}*/

//----------------------------------------------------------

?>

<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_REQUEST);

$fields = '';
$errors = '';

$fields .= " SET comment='" . addslashes($data['action']) . "'";

$fields .= ", app_id = '$app_id'";

if ( $data['tab_id'] ) {
    $tabsql = "SELECT app_id FROM app_tabs WHERE id='" . $data['tab_id'] . "'";
    $tabres = mysql_query($tabsql, $conn);
    if ( mysql_num_rows( $tabres ) ) {
        $fields .= ", tab_id='" . $data['tab_id'] . "'";
    } else {
        $errors .= 'Invalid tab.';
    }    
} else {
    $errors .= 'Invalid tab.';
}

if ( $data['id'] ) {
    $couponsql = "SELECT app_id FROM loyalty WHERE id='" . $data['id'] . "'";
    $couponres = mysql_query($couponsql, $conn);
    if ( mysql_num_rows( $couponres ) ) {
        $fields .= ", detail_id='" . $data['id'] . "'";
    } else {
        $errors .= 'Invalid loyalty.';
    }    
} else {
    $errors .= 'Invalid loyalty.';
}

$fields .= ", detail_type = '12'";

if ( $data['user_type'] ) {
    $fields .= ", user_type='" . intval( $data['user_type'] ) . "'";
}

if ( $data['user_id'] ) {
    $fields .= ", user_id='" . $data['user_id'] . "'";
}

if ( $data['avatar_url'] ) {
    $fields .= ", avatar='" . $data['avatar_url'] . "'";
} else {
	$fields .= ", avatar=''";
}

if ( $data['name'] ) {
    $fields .= ", name='" . addslashes($data['name']) . "'";
}

$fields .= ", created='" . gmdate('Y-m-d H:i:s') . "'";

$fields .= ", sequence='" . intval($data['sequence']) . "'";

if ( !$errors ) {
	$sql = "INSERT INTO app_user_comments $fields";
	$res = mysql_query($sql, $conn);
	if ( $res ) {
		$feed = array(
			array('result' => 'success')
		);
	} else {
		$feed = array(
			array('result' => 'failed')
		);
	}
} else {
	$feed = array(
		array('result' => 'failed', 'error' => $errors)
	);
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

die();

?>
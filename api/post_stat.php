<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_REQUEST);

$fields = '';
$errors = '';

if ( $data['app_code'] ) {
	$appsql = "SELECT id FROM apps WHERE code='" . $data['app_code'] . "'";
	$appres = mysql_query($appsql, $conn);
	if ( mysql_num_rows( $appres ) ) {
		$app_id = mysql_result( $appres, 0, 0 );
	} else {
        $errors .= 'Invalid app code.';
    }
} else {
    $errors .= 'Invalid app code.';
}

if ( !$data['device_token'] ) {
	$errors .= 'Invalid device token.';
}

if ( !$errors ) {
	$messages = intval( $data['messages'] );
	$fanwallposts = intval( $data['fanwallposts'] );
	$shares = intval( $data['shares'] );
	$rewards = intval( $data['rewards'] );
	$comments = intval( $data['comments'] );

	if ( !$data['device'] ) {
		$data['device'] = 'iphone5';
	}

	$fields .= "SET device_token='" . $data['device_token'] . "'";
	$fields .= ", app_id='" . $app_id . "'";
	$fields .= ", user='" . stripslashes($data['user']) . "'";
	$fields .= ", messages='" . $messages . "'";
	$fields .= ", fanwallposts='" . $fanwallposts . "'";
	$fields .= ", shares='" . $shares . "'";
	$fields .= ", rewards='" . $rewards . "'";
	$fields .= ", comments='" . $comments . "'";
	$fields .= ", device='" . $data['device'] . "'";
	$fields .= ", updated='" . gmdate('Y-m-d H:i:s') . "'";

	// Check device token
    $tokensql = "SELECT id FROM app_user_stats WHERE device_token='" . $data['device_token'] . "'";
    $tokenres = mysql_query($tokensql, $conn);
    if ( mysql_num_rows( $tokenres ) ) {
        $sql = "UPDATE app_user_stats $fields WHERE id='" . mysql_result($tokenres, 0, 0) . "'";
    } else {
		$sql = "INSERT INTO app_user_stats $fields";
	}

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
		array('result' => 'failed', 'errors' => $errors)
	);
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

die();

?>
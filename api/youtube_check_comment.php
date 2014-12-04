<?php

/* 
 * youtube_check_comment.php
 * This api is called when checking youtube video if users can leave the comment
 * @params: id
 * Created by Daniel 5/16/2014
 */

include_once("dbconnect.inc");

$result = array(
    'allow_comment' => 0
);

if ( $_GET['id'] ) {
    $feed = json_decode( file_get_contents('http://gdata.youtube.com/feeds/api/videos/' . $_GET['id'] . '?v=2&alt=json'), true );
    $access_controls = $feed['entry']['yt$accessControl'];
    if ( count($access_controls) > 0 ) {
        foreach ( $access_controls as $access ) {
            if ( $access['action'] == 'comment' && $access['permission'] == 'allowed' ) {
                $result['allow_comment'] = 1;
                break;
            }                
        }
    }
}

$json = json_encode($result);

header("Content-encoding: gzip");
echo gzencode($json);
exit;

?>
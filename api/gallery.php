<?php

include_once "dbconnect.inc";
include_once "app.inc";

$data = make_data_safe($_GET);

$t = get_app_tab_record($conn, $data["tab_id"]);
if ($t["value1"])
    $width = $t["value1"];
else
    $width = 100;

$height = 100;

$isDesc = 'n';
$isCoverflow = 'n';

$list_sql = "SELECT * from galleries
    WHERE app_id = '$app_id'
    and tab_id = '$data[tab_id]'
    ORDER BY seq ASC";
$list_res = mysql_query($list_sql, $conn);
$gallery_list_rows = mysql_num_rows($list_res);

if ( $gallery_list_rows == 0 ) {
    $feed[] = array("result" => "no data");
} else {
    while ($list_qry = mysql_fetch_array($list_res)) {
        
        foreach ( $list_qry as $k => $v ) {
            $list_qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
        }

        $info = $list_qry['info'];
        if ( $info ) {
            $info = unserialize($info);
            if ( is_array($info) ) {
                if ( $info['isdesc'] == '1' )
                    $isDesc = 'y';
                else
                    $isDesc = 'n';

                if ( $info['type'] == '1' )
                    $isCoverflow = 'y';
                else
                    $isCoverflow = 'n';
            }
        }
    }
}

$sql = "SELECT id, seq, width, height, info, ext from gallery_images
    WHERE app_id = '$app_id'
    and tab_id = '$data[tab_id]'
    ORDER BY seq ASC, list_id ASC";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {
    $feed[] = array("id" => "0");
} else {
    while ($qry = mysql_fetch_array($res)) {

        foreach ( $qry as $k => $v ) {
            $qry[$k] = str_replace( array("&amp;", "&#039;", "&quot;", "&lt;", "&gt;"), array("&", "'", '"', "<", ">"), $v );
        }
        
        if ( $isDesc == 'y' ) {
            $img[] = array("id" => $qry["id"], "width" => $qry["width"], "height" => $qry["height"], "info" => $qry["info"], "ext" => $qry["ext"]);
        } else {
            $img[] = array("id" => $qry["id"], "width" => $qry["width"], "height" => $qry["height"], "info"=>"", "ext" => $qry["ext"]);
        }
    }
}

$feed[] = array(
    "width" => $width, 
    "height" => $height,
    "coverflow" => $isCoverflow,
    "withnote" => $isDesc,
    "images" => $img
);

$bg_image = getBackgroundImageValue($conn, $app_id, $data, "0", "../../");
$feed[0]["background"] = $bg_image;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
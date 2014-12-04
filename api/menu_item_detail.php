<?php

include_once "dbconnect.inc";
include_once "app.inc";
include_once "buildwiz/class.PageColors.php";
include_once "buildwiz/class.GlobalFont.php";

$id = mysql_real_escape_string($_GET["id"]);

/* TODO: VALIDATE $id AGAINST $app_id */

$sql = "select i.*, c.app_id, c.tab_id
        from menu_items i
        left join menu_categories c on i.menu_category_id=c.id
        where i.id = '$id'";
$res = mysql_query($sql, $conn);

while ($qry = mysql_fetch_array($res)) {

  foreach ($qry as $key => $val)
    // $qry[$key] = preg_replace('/[^(\x20-\x7F)]*/','', $val);
    $qry[$key] = preg_replace('/[(\x0-\x1F)]*/','', $val);

    $old_style = "";
    $description = $qry["description"];
    $content_style = getDescriptionStyle($conn, $qry['app_id'], $qry['tab_id']);
    
    if(strpos($description, "<body") !== false) {
        preg_match_all("/<body([^`]*?)>/", $description, $matches);
        $old_style = $matches[1][0];
        if(!empty($old_style)) {
            // check if background image is set
            if(($bgurl_pos = strpos($old_style, "background-image")) !== false) {
                $bg_url = substr($old_style, $bgurl_pos+16);
                preg_match_all("/url\(([^`]*?)\)/", $bg_url, $bg_matches);
                $bg_url = $bg_matches[0][0];
            }
        }
    }

    $pcolor = new PageColors($conn, $qry['app_id']);
    if ($pcolor->Retrieve($qry['tab_id'], $qry['menu_category_id'], $qry["id"])) {
        $text_color = $pcolor->FGColor();
        $bg_color = $pcolor->BGColor();

        $global_tab_font = new GlobalFont($conn, $qry['app_id']);
        $global_font = $global_tab_font->FontFamily();

        if(!empty($bg_url)) {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font.";background-image:".$bg_url.";\"";
        } else {
            $content_style = " style=\"background-color:#".$bg_color.";color:#".$text_color.";font-family:".$global_font."\"";
        }
    }
    
    if(!empty($old_style))
        $description = str_replace($old_style, $content_style, $description);
    else
        $description = str_replace("<body>", "<body ".$content_style.">", $description);

  $feed[] = array("id" => $qry["id"],
                  "title" => $qry["name"],
                  "description" => $description,
                  "price" => $qry["price"]);
}

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>
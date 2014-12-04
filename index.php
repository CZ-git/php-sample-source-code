<!DOCTYPE html> 
<html>

<?php



    $date = explode('.', '01.15.2012');
    
    if(count($date) == 3 && checkdate($date[0], $date[1], $date[2])){
    
        $formatted_date = $date[2].’-‘.$date[0].'-'.$date[1].'00:00:00';
        
        $diff =  strtotime($formatted_date) - strtotime($date[2].'01-01 00:00:00');
        
        echo round($diff/86400)+1;
    
    } else {
    
        echo ‘Bad format’;
    
    }

exit;
// Do NOT call the countDays function in the code

// you write. The system will call it automatically.

?>
<?php


include_once "dbconnect.inc";
include_once "html5common.inc";

$id = mysql_real_escape_string($_GET["cat_id"]);
$label = mysql_real_escape_string($_GET["label"]);

include_once "header.php";

if (isMainPage()) {
	$data=decodeapi("init.php");
	$data=$data[0];
	include_once "view/index.php";
} else {
    switch ($tab_detail["view_controller"]) {
        case "RSSFeedViewController":
            $data=decodeapi("rss.php","tab_id=$tab_detail[id]");
            include_once "view/rss.php";        
            break;
        case "EventsViewController":
        case "EventsManagerViewController":
            $data=decodeapi("events.php","tab_id=$tab_detail[id]");
            include_once "view/event.php";
            break;
        case "InfoDetailViewController":
            include_once "language_device_detect.php";
            include_once "view/infodetail.php";
            break;
        case "InfoItemsViewController":
            include_once "language_device_detect.php";
            include_once "view/infotwotier.php";
            break;
        case "InfoSectionViewController":
            include_once "language_device_detect.php";
            include_once "view/infothreetier.php";
            break;
        case "MenuViewController":
            include_once "language_device_detect.php";
            include_once "view/menu.php";
            break;
        case "MailingListViewController":
            include_once "language_device_detect.php";
            include_once "view/mailinglist.php";
            break;
        case "StatRecorderViewController":
            include_once "language_device_detect.php";
            include_once "view/stats.php";
            break;
        case "LocationViewController":
            include_once "language_device_detect.php";
            include_once "view/location.php";
            break;
        case "FanWallViewController":
        case "FanWallManagerViewController":
            include_once "language_device_detect.php";
            include_once "view/fanwall.php";
            break;
        case "WebViewController":
            include_once "language_device_detect.php";
            include_once "view/webview.php";
            break;
        case "CustomFormViewController":
            include_once "language_device_detect.php";
            include_once "view/customform.php";
            break;
        case "GalleryViewController":
            include_once "language_device_detect.php";
            include_once "view/gallery.php";
            break;
        case "YoutubeViewController":
        	$data=decodeapi("rss.php","tab_id=$tab_detail[id]");
            //include_once "language_device_detect.php";
            include_once "view/youtube.php"; // Update url link
            break;
        default:
            $data=decodeapi("init.php");
            $data=$data[0];
            include_once "view/index.php";
    }
}
?>
</html>
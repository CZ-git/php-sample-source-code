<? 
$id = mysql_real_escape_string($_GET["cat_id"]);
$domainfanchk=substr($_SERVER['HTTP_HOST'], 0, 4);
if(substr_count($_SERVER['HTTP_HOST'],".")>1)
{
    $_SESSION['domain'] = $_SERVER['HTTP_HOST'].$path;
}
else
{
    $_SESSION['domain'] = "www.".$_SERVER['HTTP_HOST'].$path;
}

//$id = make_data_safe($_GET['id']);
session_start("mob_app");
$sql = "SELECT * FROM `app_locations` WHERE `id` ='$tab_detail[cat_id]'";
//echo $sql;
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);
$weburlleft= str_split($qry["website"], 4);
if ($weburlleft[0] != "http")
{
    $qry["website"]="http://".$qry["website"];
}
// store session data
unset($_SESSION[changepage]);
if($id)
{
    $_SESSION['detail_id']=$id;
}
$_SESSION[fanwallv2]=1;
// store session data
if (array_key_exists("login", $_GET)) {
    $oauth_provider = $_GET['oauth_provider'];
    $_SESSION['id_Fan'] = $_GET['login'];
    $_SESSION[commentpage]="?controller=LocationViewController&tab_id=$tab_detail[id]&cat_id=".$_SESSION['detail_id'];
    if ($oauth_provider == 'twitter') {
        //header("Location: login-twitter.php");?>
        <script> window.location = "<?=$socialext?>/login-twitter.php?domain=<?=$_SESSION['domain']?>" </script>
        <?
    } else if ($oauth_provider == 'facebook') {
        ?>
        <script> window.location = "<?=$socialext?>/login-facebook.php?domain=<?=$_SESSION['domain']?>" </script>
        <? }
}

?>
<style type="text/css">
    #popaddcomment .ui-btn-left, #popshareloc<?=$id?> .ui-btn-left { display:none; text-decoration:none; }
    #map-canvas<?=$id ?> { height: 300px }
</style>
<script type="text/javascript">
    // When map page opens get location and display map
    $('#loccontrl<?=$id?>').bind('pageshow', function() {
        var lat = <? echo $qry["latitude"] ?>,
        lng = <? echo $qry["longitude"] ?>;

        var latlng = new google.maps.LatLng(lat, lng);

        var myOptions = {
            zoom: 14,
            center: latlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map-canvas<?=$id ?>"),myOptions);
        zoom = map.getZoom(); 
        map.setZoom(zoom-1);
        map.setZoom(zoom+1);
        var marker = new google.maps.Marker({
            position: latlng, 
            map: map,
            animation: google.maps.Animation.DROP
        }); 
        google.maps.event.addListener(marker, 'click', toggleBounce);

        // disable swiperight event of left panel when google drag is started
        // Added by Wang Jia
        google.maps.event.addListener(map, 'dragstart', function(e) { 
            mapDragFlag = true;
            $.mobile.activePage.on("touchmove", false);
        });

        google.maps.event.addListener(map, 'mousedown', function(e) { 
            $.mobile.activePage.on("touchmove", false);
        });

        // enable swiperight event for left menu panel when google drag is ended
        // Added by Wang Jia
        google.maps.event.addListener(map, 'dragend', function(e) {
            mapDragFlag = false;
            $.mobile.activePage.unbind("touchmove");
        });

        google.maps.event.trigger(marker, "resize");
        infowindow = new google.maps.InfoWindow();
        function toggleBounce() {
            if (marker.getAnimation() != null) {
                marker.setAnimation(null);
            } else {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }
        }
    });
</script>
<div data-role="header" data-position="fixed" > 
    <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
    <h1>Our Location</h1> 
    <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
<?
$image_file = get_app_bg_html5($conn, '1', $id, '0',$app_code, $app_id);
?>

<div data-role="content" style="background:url(<?=$image_file; ?>); background-size: 100% 100%; background-repeat:no-repeat;"> 
    <div id="map-canvas<?=$id ?>" style="height:211px; margin: -15px -15px -35px -15px;"></div>
    <div id="subheader">
        <div><?=$qry["city"]?><br><?=$qry["address_1"]." ".$qry["address_2"].", ".$qry["city"].", ".$qry["state"];?></div>
    </div>
    <? $img_file1 = findUploadDirectory($app_id) . "/location/$id.jpg";
$img_url1="/images/theme_editor/no button.png";
$img_url2="/images/theme_editor/no button.png";
    if (file_exists($img_file1)) {
        $img_url1 = "/custom_images/".$_SESSION["app_code"]."/location/$id.jpg";
    } 
    $img_file2 = findUploadDirectory($app_id) . "/location/$id@2x.png";

    if (file_exists($img_file2)) {
        $img_url2 = "/custom_images/".$_SESSION["app_code"]."/location/$id@2x.png";
    } ?>

    <div class="ui-grid-a images">
        <div class="ui-block-a"><img src="<?=$img_url1?>" width="100%" height="71px"/></div>
        <div class="ui-block-b"><img src="<?=$img_url2?>" width="100%" height="71px"/></div>
    </div><!-- /grid-a -->
    <? 	$sqltimes = "select * from app_location_opening_times where app_location_id = '$id' order by seq";
    $restimes = mysql_query($sqltimes, $conn);
    if (mysql_num_rows($restimes) > 0)
    {?>
        <div data-role="collapsible" class="locationday" data-collapsed="false" data-iconpos="right" data-collapsed-icon="arrow-d" data-expanded-icon="arrow-u">
            <h4><?=$lngopening_hours?></h4>
            <ul data-role="listview">
                <?	$countrow=0;
                $langs=explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
                $choice = $langs[0];
                $choice=substr($choice,0,2);
                $codetocountry = country_code_to_locale($choice).".UTF-8";
                setlocale(LC_ALL, $codetocountry);
                //$localset= setlocale(LC_ALL, "fi_FI");
                //echo "set".$localset;
                //$days_of_week = strftime("%A", strtotime(Monday));
                //echo $days_of_week;
                while ($o = mysql_fetch_array($restimes)) 
                {
                    $countrow++;
                    $days_of_week = strftime("%A", strtotime($o[day]));
                    ?>
                    <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><fieldset class="ui-grid-a">
                            <div class="ui-block-a" ><em><?=$days_of_week?></em></div>
                            <div class="ui-block-b"><?=$o["open_from"]." to ".$o["open_to"]?></div>	   
                        </fieldset></li>
                    <?	}?>
            </ul>
        </div>
        <?	}?>
    <? 	$sqlcomments = "SELECT *, unix_timestamp(created) as timestamp FROM `app_user_comments` where detail_id=$id and app_id=$app_id ORDER BY `app_user_comments`.`id`  DESC";
    $rescomments = mysql_query($sqlcomments, $conn);
    ?>
    <div data-role="popup" id="popaddcomment" data-theme="a" class="ui-corner-all">
        <div data-role="header" data-theme="b" class="ui-corner-top"> 
            <h1><?=$lnglogin?></h1> 
        </div> 
        <div data-role="content"> 
            <a href="?controller=LocationViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$_GET['cat_id']?>&login=<?=$_GET['cat_id']?>&oauth_provider=twitter" onclick="window.location = $(this).attr('href')" rel="external" data-role="button" data-theme="b">Twitter</a>
            <a href="?controller=LocationViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$_GET['cat_id']?>&login=<?=$_GET['cat_id']?>&oauth_provider=facebook" onclick="window.location = $(this).attr('href')" rel="external" data-role="button" data-theme="b">Facebook</a>
        </div> 
    </div> 
    <div data-role="collapsible" data-collapsed="false" data-iconpos="right" data-collapsed-icon="arrow-d" data-expanded-icon="arrow-u">
        <h4><?=$lngcomments?></h4>
        <ul data-role="listview">
            <?	if (mysql_num_rows($rescomments) > 0)
            {
                $countrow=0;
                while ($o = mysql_fetch_array($rescomments)) 
                {
                    $countrow++;
                    $time_ago =  timeago($o["timestamp"]);?>
                    <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><fieldset class="ui-grid-b loc_comment">
                            <div class="ui-block-a"><img src="<?=$o[avatar];?>"/></div>
                            <div class="ui-block-b"><?=$o[name]."<p>".stripslashes(strip_tags($o[comment]))."</p>"?></div>
                            <div class="ui-block-c"><?=$time_ago?></div>	   
                        </fieldset></li>
                    <?	}
            }?>
            <a href="#popaddcomment" data-position-to="window" data-rel="popup" data-theme="b" data-role="button"><?=$lngadd_comment?></a>
        </ul>
    </div>
</div>

<div data-role="footer" data-position="fixed">
    <div data-role="navbar">
        <ul data-theme="c">
            <?
            if ($qry["telephone"])
            {?>
                <li><a href="tel:<?=$qry["telephone"]?>" data-icon="callus"><?=$lngcallus?></a></li>
                <?
            }
            $url=$qry["website"];
            $urlleft= str_split($url, 4);
            if( $urlleft[0] != "http")
            {
                $url="http://".$url;
            }
            ?>
            <li><a href="directions.php?id=<?=$id?>" data-icon="directions"><?=$lngdirection?></a></li>
            <?
            //}
            if($qry["email"])
            {?>
                <li><a href="mailto:<?=$qry["email"]?>" data-icon="emailus"><?=$lngemail_us?></a></li>
                <?
            }
            ?>
            <li><a href="<?=$_SESSION['app_website']?>" data-icon="website" rel="external"><?=$lngview_website?></a></li>
        </ul>
    </div><!-- /navbar -->
</div><!-- /footer -->
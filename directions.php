<? 
include_once "dbconnect.inc";
include_once "html5common.inc";
$id = mysql_real_escape_string($_GET["id"]);
//$id = make_data_safe($_GET['id']);
session_start("mob_app");
$sql = "SELECT `city`, `state`,`latitude` , `longitude` FROM `app_locations` WHERE `id` ='$id'";
//echo $sql;
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);

// store session data
?>
<!DOCTYPE html> 
<html> 
    <? include "header.php";?>
    <body>
        <div id="directions_map" data-role="page">
            <?php 		
            $sql = "SELECT `city`, `state`,`latitude` , `longitude` FROM `app_locations` WHERE `id` ='$id'";
            $res = mysql_query($sql, $conn);
            $qry = mysql_fetch_array($res);
            $image_file = get_app_bg_html5($conn, '1', $id, '0',$app_code, $app_id);
            ?>
            <div data-role="header" data-position="fixed">
                <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
                <h1><?=$lngdirection?></h1>
            </div>
            <div data-role="content" style="background:url(<?=$image_file; ?>); background-size: 100% 100%; background-repeat:no-repeat;">
                <div class="ui-bar-b ui-corner-all ui-shadow" style="padding:0.5em;">
                    <div id="map_canvas" style="height:200px;"></div>
                    <input id="from" class="ui-bar-b" type="hidden" />
                    <input id="to" class="ui-bar-b" type="hidden" />
                </div>

                <div id="results" class="ui-listview ui-listview-inset ui-corner-all ui-shadow" style="display:none;">
                    <div class="ui-li ui-li-divider ui-btn ui-bar-b ui-corner-top ui-btn-up-undefined" style="text-align:center">Results</div>
                    <div class="ui-bar-b ui-corner-all" id="directions" style="font-size:10px"></div>
                </div>

                <div id="error" style="display: none; text-align: center; color: #ff0000; padding: 5px 10px; margin-top: 10px; border-radius: 3px; background-color: #fff; "></div>

                <script type="text/javascript">
                    $("#directions_map").on("pagebeforeshow", function(){
                        $('#map_canvas').gmap({'center': '<?=$qry["latitude"].",".$qry["longitude"] ?>', 'zoom': 10, 'disableDefaultUI':true, 'callback': function(event, map) {
                            var self = this;
                            self.getCurrentPosition(function(position, status) {
                                self.refresh();
                                if ( status === 'OK' ) {
                                    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                                    self.get('map').panTo(latlng);
                                    self.search({ 'location': latlng }, function(results, status) {
                                        if ( status === 'OK' ) {
                                            $('#from').val(results[0].formatted_address);
                                            $('#to').val("<? echo $qry["latitude"] ?>,<? echo $qry["longitude"] ?>");
                                            self.displayDirections(
                                                { 
                                                    'origin': $('#from').val(), 
                                                    'destination': $('#to').val(), 
                                                    'travelMode': google.maps.DirectionsTravelMode.DRIVING 
                                                }, 
                                                { 'panel': document.getElementById('directions')}, 
                                                function(response, status) {
                                                    if (status == 'OK') {
                                                        $('#results').show();
                                                    } else {
                                                        $('#results').hide();
                                                        $('#error').html("Directions not available.").show();
                                                    }

                                                    $(".ui-content:visible").bind("touchstart mouseover load resize",function() {
                                                        $(".ui-content:visible").getNiceScroll().resize();
                                                    });
                                                }
                                            );
                                        } else {
                                            $('#error').html("Unable to search geolocation address.").show();
                                        }
                                    });
                                } else {
                                    $('#error').html("Unable to get current position.").show();
                                }
                            });  
                        }}).bind('init', function(event, map) { 
                            $(map).addEventListener( 'drag', function() {
                                mapDragFlag = true;
                                $.mobile.activePage.on("touchmove", false);
                            });
                            $(map).addEventListener( 'mousedown', function() {
                                $.mobile.activePage.on("touchmove", false);
                            });
                            $(map).dragend( function (event) {
                                mapDragFlag = false;
                                $.mobile.activePage.unbind("touchmove");
                            });
                        });
                    });
                </script>
            </div>
            <?php include_once "view/leftsidepanel.php";   ?>
        </div>

    </body>
</html>
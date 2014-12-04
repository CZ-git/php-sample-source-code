<?
//$image_file = get_app_bg_html5($conn, '0', $id, '0',$app_code, $app_id); 
?>
<div data-role="page" data-add-back-btn="true" id="Gallerys" >
	<script>
	$("Gallerys").on('pageinit', function() {
		try {
				ga('create', '<?=$gaanalyst?>');  // Creates a tracker.
				ga('send', {
				  'hitType': 'event',          	// Required.
				  'eventCategory': '<?=$id?>',  // Required Tab ID.
				  'eventAction': '0',      		// Required Item ID.
				  'eventLabel': '0',			// Category ID
				  'dimension1': <?=$app_id?>,
				});
			} catch(err) {      
			}
    })
	</script>
	<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
	</div> 
    <style>
		.ui-li .ui-btn-inner a.ui-link-inherit, .ui-li-static.ui-li {
			padding-top: 0.4em;
			padding-left: 75px;
			padding-bottom: 0.4em;
			display: block;
		}
	</style>
	<div data-role="content" > 
 	   <ul data-role="listview" data-divider-theme="d">
    <?php
	/*$_SESSION[userid] = "93298639@N04";
	$_SESSION[apikey] = "e2e537e642e1d6dcba31185296d2f140";*/
    $userid = $_SESSION[userid];
    $apikey = $_SESSION[apikey];
	//$getuserid ="http://api.flickr.com/services/rest/?method=flickr.people.findByUsername&api_key=$apikey&user_id=$userid&format=json&nojsoncallback=1";
	$getuserid ="https://api.flickr.com/services/rest/?method=flickr.people.findByEmail&api_key=$apikey&find_email=$userid&format=json&nojsoncallback=1";
	//echo $getuserid;
	$getuserid = file_get_contents($getuserid);
	$getuserid = json_decode($getuserid,true);
	//print_r($getuserid);
	$getuserid = $getuserid[user][id];
    $feedURL = "https://api.flickr.com/services/rest/?method=flickr.photosets.getList&api_key=$apikey&user_id=$getuserid&format=json&nojsoncallback=1";
    //echo $feedURL;
	$feedURL = file_get_contents($feedURL);
    // read feed into SimpleXML object
	
	   		//print_r(json_decode($feedURL,true)); 
			$photosets = json_decode($feedURL,true);
			 $countrow=0;
			//print_r($sxml);
			
			//print_r($photosets[photosets][photoset]);
			$sxml = $photosets[photosets][photoset];
			foreach ($sxml as $entry) { 
			$title = $entry[title][_content];
			$numphotos = $entry[photos];
			$albumid = $entry[id];
			$sideimage = 'http://farm'.$entry[farm].'.staticflickr.com/'.$entry[server].'/'.$entry[primary].'_'.$entry[secret].'_s.jpg';
			$countrow++;    
			?>	
             <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="FlickrGallery.php?albumid=<?=$albumid?>" data-prefetch><img src="<?=$sideimage;?>" style="height: 100%; padding-top: 1px;" width="70"/><h3><?=$title?></h3>Total Images: <?=$numphotos?></p></a></li>	
            <?
			}
			
			?>
    	</ul>
    </div>  
	<?php include_once "view/leftsidepanel.php";  ?>  
</div><!-- /page --> 
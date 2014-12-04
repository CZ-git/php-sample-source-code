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
    $userid = $_SESSION[userid];
    
    // build feed URL
    $feedURL = "http://picasaweb.google.com/data/feed/api/user/$userid?kind=album";
    
    // read feed into SimpleXML object
    $sxml = simplexml_load_file($feedURL);
    ?>
       
		<?  
			$countrow=0;
			foreach ($sxml->entry as $entry) { 
			$title = $entry->title;
			$gphoto = $entry->children('http://schemas.google.com/photos/2007');
			$numphotos = $gphoto->numphotos; 
			$albumname = $gphoto->name; 
			$media = $entry->children('http://search.yahoo.com/mrss/');
      		$thumbnail = $media->group->thumbnail;
			$sideimage = $thumbnail->attributes()->{'url'};
			$countrow++;    
			?>	
             <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="PicasaGallery.php?albumname=<?=$albumname?>" data-prefetch><img src="<?=$sideimage;?>" style="height: 100%; padding-top: 1px;" width="70"/><h3><?=$title?></h3><?=$lngtot_images." ".$numphotos?></p></a></li>	
            <?
			}
			
			?>
    	</ul>
    </div>  
	<?php include_once "view/leftsidepanel.php";  ?>  
</div><!-- /page --> 
 
</body> 
</html>
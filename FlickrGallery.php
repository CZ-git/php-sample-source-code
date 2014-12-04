<? 
include_once "dbconnect.inc";
include_once "appmob.inc";

session_start("mob_app");

?>
<div data-role="page" data-add-back-btn="true" class="gallery-page">
	<?
	// build feed URL
    $feedURL = "https://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key=$_SESSION[apikey]&photoset_id=$_GET[albumid]&format=json&nojsoncallback=1";
    //echo $feedURL."<br>";
	$feedURL = file_get_contents($feedURL);
	//echo $feedURL;
	//$photos = json_decode($feedURL,true);
	$photos = json_decode($feedURL,true);
	$photos = $photos[photoset][photo];
	$countrow=0;
    ?>
	<script type="text/javascript">
    (function(e,t,n){t(document).ready(function(){t("div.gallery-page").on("pageshow",function(e){var n=t(e.target),r={},i=t("ul.gallery a",e.target).photoSwipe(r,n.attr("id"));return true}).on("pagehide",function(e){var r=t(e.target),i=n.getInstance(r.attr("id"));if(typeof i!="undefined"&&i!=null){n.detatch(i)}return true})})})(window,window.jQuery,window.Code.PhotoSwipe)
    </script>
	<div data-role="header" data-position="fixed"> 
            <h1><? echo $sxml->title?></h1> 
            <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
    </div> 
	<div data-role="content">	
         <ul class="gallery">
            <? 
			// iterate over entries in album
			// print each entry's title, size, dimensions, tags, and thumbnail image
			foreach ($photos as $entry) {
			  //print_r($entry);
			  $title = $entry[title];
			  $sideimage = 'http://farm'.$entry[farm].'.staticflickr.com/'.$entry[server].'/'.$entry[id].'_'.$entry[secret].'_s.jpg';
			  $image = 'http://farm'.$entry[farm].'.staticflickr.com/'.$entry[server].'/'.$entry[id].'_'.$entry[secret].'.jpg';
			  ?>
              <li>
              <a href="<?=$image?>" rel="external">
              	<img src="<?=$sideimage?>" alt="<?=$title?>" style="width: 100%; height:100px" />
              </a>
              </li>
              <?
			}
			?>
          </ul>
    </div>
<?php include_once "view/leftsidepanel.php";  ?>
</div>
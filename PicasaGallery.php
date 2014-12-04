<? 
include_once "dbconnect.inc";
include_once "appmob.inc";

session_start("mob_app");

?>
<div data-role="page" data-add-back-btn="true" class="gallery-page">
	<?
	// build feed URL
    $feedURL = "http://picasaweb.google.com/data/feed/api/user/$_SESSION[userid]/album/$_GET[albumname]";
    // read feed into SimpleXML object
    $sxml = simplexml_load_file($feedURL);
    // get album name and number of photos
    $counts = $sxml->children('http://a9.com/-/spec/opensearchrss/1.0/');
    $total = $counts->totalResults;
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
			foreach ($sxml->entry as $entry) {
			  $title = $entry->title;
			  $summary = $entry->summary;
			  
			  $gphoto = $entry->children('http://schemas.google.com/photos/2007');
			  $size = $gphoto->size;
			  $height = $gphoto->height;
			  $width = $gphoto->width;
			  
			  $media = $entry->children('http://search.yahoo.com/mrss/');
			  $thumbnail = $media->group->thumbnail;
			  $image = $media->group->content;
			  $tags = $media->group->keywords;
			  ?>
              <li>
              <a href="<?=$image->attributes()->{'url'}?>" rel="external">
              	<img src="<?=$thumbnail->attributes()->{'url'}?>" alt="<?=$summary?>" style="width: 100%; height:100px" />
              </a>
              </li>
              <?
			}
			?>
          </ul>
    </div>
<?php include_once "view/leftsidepanel.php";  ?>
</div>
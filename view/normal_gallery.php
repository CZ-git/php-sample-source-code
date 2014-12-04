<div data-role="page" data-add-back-btn="true" id="Gallery1" class="gallery-page">
	<script>
	$('#Gallery1').on('pageinit', function() {
		try {
				ga('create', '<?=$gaanalyst?>');  // Creates a tracker.
				ga('send', {
				  'hitType': 'event',          	// Required.
				  'eventCategory': '<?=$id?>',  // Required Tab ID.
				  'eventAction': '0',      		// Required Item ID.
				  'eventLabel': '0',			// Category ID
				  'dimension1': <?=$app_id?>,
				});
			} catch(err) {      
			}
    });
	</script>
	<script type="text/javascript">
    (function(e,t,n){t(document).ready(function(){t("div.gallery-page").on("pageshow",function(e){var n=t(e.target),r={},i=t("ul.gallery a",e.target).photoSwipe(r,n.attr("id"));return true}).on("pagehide",function(e){var r=t(e.target),i=n.getInstance(r.attr("id"));if(typeof i!="undefined"&&i!=null){n.detatch(i)}return true})})})(window,window.jQuery,window.Code.PhotoSwipe)
    </script>
	<div data-role="header" data-position="fixed"> 
            <h1><? echo $label; ?></h1>
            <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
            <a href="#popshareloc<?=$id?>" data-inline="true" data-role="button" data-icon="share"></a>
 </div> 
	<div data-role="content">
    	<div id="gallery_page1" class="galleryPage">	
             <ul class="gallery">
               
                <? 
                while($qry=mysql_fetch_array($res))
                {
                    if ($qry["ext"]==NULL) $qry["ext"]="png";
                    $file_name = "/gallery_images/" . $qry["id"] . "." . $qry["ext"];
                    //$file_name = substr($file_name,25);
                ?>
                    <li>
                        <a href="<?=$file_name?>" rel="external">
                            <img src="<?=$file_name?>?width=128&height=164" alt="<? echo strip_tags($qry["info"]) ?>" style="width: 100%; height:25%" />
                        </a>
                    </li>
                <?		
                }
				?>
              </ul>
            <br />
      <br><br><br>
    </div>
<script type="text/javascript">
		$(document).on("pagecreate","#Gallery1",function(){
		});
</script>
<?
include_once "view/leftsidepanel.php"; ?>
</div>
<body> 
<div data-role="page" class="rssfeed" id="rsscontrl<?=id?>">
	<style>
		.rssfeed .ui-content .ui-btn-icon-right:after
		{
			display:none;
		}
		.shortlen
		{
			height:60px;
		}
		.rssfeed .ui-content .btnlist
		{
			position: absolute;
			right: -8px;
			top: 26px;
			z-index:1;
		}
		.rssfeed li p
		{
			white-space: normal;
			margin-left: 9px;
			margin-right: 15px;
		}
		.rssfeed li p img{
			float: left;
			padding-right: 5px;
			height: 60px
		}
	</style>
	<? makehtml5page ("2", "2", "rss");?>
	<script>
	$('#rsscontrl<?=id?>').on('pageshow', function() {
		try {
				ga('create', '<?=$gaanalyst?>');  // Creates a tracker.
				ga('send', {
				  'hitType': 'event',          	// Required.
				  'eventCategory': '<?=(is_numeric($_GET['tab_id']))?$_GET['tab_id']:'0'?>',  // Required Tab ID.
				  'eventAction': '<?=(is_numeric($_GET['item_id']))?$_GET['item_id']:'0'?>',      		// Required Item ID.
				  'eventLabel': '<?=(is_numeric($_GET['cat_id']))?$_GET['cat_id']:'0'?>',			// Category ID
				  'dimension1': <?=$app_id?>,
				});
			} catch(err) {      
			}
			//alert("I am In!");
        	$('li').each(function() {
				$('.btnlist').on("click", function() {
					//alert("over k");
				  if($(this).parent().find("p").hasClass("shortlen"))
				  {
				  	//alert("over");
				    $(this).parent().find("p").removeClass("shortlen");
				    $(this).addClass("ui-icon-carat-u").removeClass("ui-icon-carat-d")
				  }
				  else
				  {
				  	//alert("over");
				    $(this).parent().find("p").addClass("shortlen");
				    $(this).addClass("ui-icon-carat-d").removeClass("ui-icon-carat-u")
				  }
				});
			});
    })
	</script>
</div><!-- /page --> 
 
</body> 
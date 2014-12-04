<body>
<div data-role="page" id="pageiframe"> 
	<script>
	$('#pageiframe').on('pageinit', function() {
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
    	})
	</script>
	<?
	if(!empty($tab_detail[cat_id]) || !empty($_GET[url]))
	{
		include_once "webframe.php";
	}
	else
	{
		include_once "weblist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
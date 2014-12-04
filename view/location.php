<body> 
<div data-role="page" id="loccontrl<?=$id?>" class="locationdesc"> 
	<script>
	$('#loccontrl<?=$id?>').on('pageinit', function() {
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
	if(!empty($tab_detail[cat_id]))
	{

		include_once "locationdesc.php";
	}
	else
	{
		$sql = "SELECT * 
					FROM `app_locations` 
					WHERE app_id='$app_id' order by seq";
		$res = mysql_query($sql, $conn);
		if ( mysql_num_rows($res) == 1 )
		{
           	$id = mysql_result($res, 0, 0);
			header("Location: ?controller=LocationViewController&tab_id=$tab_detail[id]&cat_id=$id");
		}
		include_once "locationlist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
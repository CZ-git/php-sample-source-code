<body>
<div data-role="page" id="emailformcontrl<?=$id?>"> 
	<script>
	$('#emailformcontrl<?=$id?>').on('pageinit', function() {
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
	if(!empty($_GET[tk]))
	{
		$tk = $_GET[tk];
		include_once "formview.php";
	}
	else
	{
		$sql="SELECT * FROM `form_layout` WHERE tab_id =$tab_detail[id] AND is_active='1' AND is_removed='0' ORDER BY seq ASC";
		$res = mysql_query($sql, $conn);
		include_once "customformlist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
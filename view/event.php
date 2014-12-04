<body> 
<div data-role="page" id="eventcontroller<?=$id?>">
	<script>
	$('#eventcontroller<?=$id?>').on('pageinit', function() {
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
	<style> 
	.ui-li-month { position: absolute; font-size: 13px; font-weight: bold; padding: 0em .5em; top: 50%; margin-top: 0.7em; left: 27px; top:9px; color:#000 }
	.ui-li-day { position: absolute; font-size: 40px; font-weight: bold; margin-top: .5em; left: 23px; text-align:center; color:#000}
	</style>
	<? 
	if(!empty($tab_detail[cat_id]))
	{
		include_once('language_device_detect.php');
		$sql2 = "select id, name, description
			from events
			where id='$tab_detail[cat_id]'";
		$res2 = mysql_query($sql2, $conn);
		$qry2 = mysql_fetch_array($res2);
		include_once "eventdetail.php";
	}
	else
	{
		makehtml5page ("2", "2", "event");
	}
	?>
  
</div><!-- /page --> 
 
</body>
<body>
<div data-role="page" id="infoitmscntlr<?=$id?>"> 
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
	<?
	if(!empty($tab_detail[cat_id]))
	{
		$sql2 = "select id, name, description
			from info_items
			where is_active = 1 and id='$tab_detail[cat_id]'
			order by seq";
		$res2 = mysql_query($sql2, $conn);
		$qry2 = mysql_fetch_array($res2);
		$sqlpagecolor = "SELECT * 
				FROM  page_colors 
				WHERE detail_id ='$tab_detail[cat_id]'";
		$respagecolor = mysql_query($sqlpagecolor, $conn);
		$qrypagecolor = mysql_fetch_array($respagecolor);	
		include_once "twotierdesc.php";
	}
	else
	{
		$sql = "select id from info_categories where tab_id = '$tab_detail[id]' and is_active=1 order by seq";
		$res2 = mysql_query($sql, $conn);
		$catid = mysql_result($res2, 0, 0);
		$sql = "select section
		        from info_items
		        where info_category_id = '$catid' and is_active=1
			  	GROUP BY section
		        order by seq";
		$res = mysql_query($sql, $conn);
		include_once "twotierlist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
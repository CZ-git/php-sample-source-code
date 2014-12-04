<body> 
<div data-role="page" id="menucontrl<?=$id?>"> 
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
	if(!empty($tab_detail[item_id]))
	{
		$sql2 = "SELECT id, description, name FROM `menu_items` 
				where is_active > 0 and id='$tab_detail[item_id]'";
		$res2 = mysql_query($sql2, $conn);
		$qry2 = mysql_fetch_array($res2);
		include_once "menudesc.php";
	}
	else
	if(!empty($tab_detail[cat_id]))
	{
		$sql = "SELECT * FROM `menu_items` WHERE menu_category_id = '$tab_detail[cat_id]' and is_active > 0";
		//echo $sql;
    	$res1 = mysql_query($sql, $conn);	
		include_once "menusect.php";
	}
	else
	{
		$sql = "SELECT section FROM `menu_categories` WHERE `tab_id`='$tab_detail[id]' and is_active > 0 group by section order by seq";
		//echo $sql;
   		$res1 = mysql_query($sql, $conn);
		include_once "menulist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 

<body> 
<div data-role="page" id="infoseccontrl<?=$id?>"> 
	<script>
	$('#infoseccontrl<?=$id?>').on('pageinit', function() {
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
		$sql2 = "select id, name, description
			from info_items
			where is_active = 1 and id='$tab_detail[item_id]'
			order by seq";
		$res2 = mysql_query($sql2, $conn);
		$qry2 = mysql_fetch_array($res2);
		$sqlpagecolor = "SELECT * 
				FROM  page_colors 
				WHERE detail_id ='$tab_detail[item_id]'";
		$respagecolor = mysql_query($sqlpagecolor, $conn);
		$qrypagecolor = mysql_fetch_array($respagecolor);		
		include_once "twotierdesc.php";
	}
	else
	if(!empty($tab_detail[cat_id]))
	{
		$sql = "SELECT DISTINCT * 
			FROM (
			SELECT section
			FROM  `info_items` 
			WHERE  `info_category_id` =  '$tab_detail[cat_id]' and is_active=1
			ORDER BY  `info_items`.`seq` ASC
			)dumptable";
			//echo $sql;
    	$res1 = mysql_query($sql, $conn);
			
		include_once "threetiersect.php";
	}
	else
	{
		$sql = "SELECT section FROM info_categories WHERE tab_id ='$tab_detail[id]' and is_active=1 GROUP BY section ORDER BY seq";
		//echo $sql;
	    $res1 = mysql_query($sql, $conn);
		include_once "threetierlist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
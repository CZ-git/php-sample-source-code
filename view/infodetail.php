<?php
$sql = "select id from info_categories where tab_id = '$tab_detail[id]' and is_active=1 order by seq, id";
$res = mysql_query($sql, $conn);
if (mysql_num_rows($res) == 1) {
	$catid = mysql_result($res, 0, 0);
}
?>
<body> 
<?
$sql = "select *
        from info_items
        where info_category_id = '$catid'";
$res = mysql_query($sql, $conn);
$qry = mysql_fetch_array($res);
$sqlpagecolor = "SELECT * 
				FROM  page_colors 
				WHERE category_id ='$catid'";
$respagecolor = mysql_query($sqlpagecolor, $conn);
$qrypagecolor = mysql_fetch_array($respagecolor);
?>            
<div data-role="page" id="infodetail<?=$id?>">     	
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
    <div data-role="header" data-position="fixed"> 
            <h1><? echo $label; ?></h1> 
            <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
            <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
    </div> 
	<div id="desc" data-role="content" style="background: #<?=$qrypagecolor[background]?>; color: #<?=$qrypagecolor[foreground]?>">
	<? $qry["description"]=preglinkmatch($qry["description"]);?>
<p><? echo $qry["description"] ?></p> 
	</div> 
	
	<? include_once "view/leftsidepanel.php";   ?>
    <style>
		#desc span
		{
			color: #<?=$qrypagecolor["foreground"];?> !important;
			background: #<?=$qrypagecolor["background"] ;?>!important;
		}
	</style>
</div><!-- /page -->
</body> 
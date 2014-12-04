<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%;">
    <ul data-role="listview" data-divider-theme="d"> 
<? while ($qry = mysql_fetch_array($res1)) 
	{ ?>
					<li data-role="list-divider"><?=$qry["section"] ?></li>
					<? 	$section = mysql_real_escape_string($qry["section"]);
						$sql = "SELECT id, name,section FROM `menu_categories` WHERE `app_id`='$app_id' and section ='$section' and is_active > 0 order by seq";
						$res2 = mysql_query($sql, $conn);
						if ( mysql_num_rows($res2) > 0 ) {
							$countrow=0;
							while ($qry2 = mysql_fetch_array($res2)) {
								$countrow++
						?>
							<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?controller=MenuViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<? echo $qry2["id"] ?>&label=<? echo $section ?>"><? echo $qry2["name"] ?></a></li>					
					 											  <? } 
								}  
	} ?>
            </ul> 
	</div>
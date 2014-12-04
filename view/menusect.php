<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
		<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a>
		<a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
	</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%;">
    <ul data-role="listview" data-divider-theme="d"> 
<? 	$countrow=0;
	while ($qry = mysql_fetch_array($res1)) 
	{	
	$countrow++?>
					<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?controller=MenuViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$tab_detail[cat_id] ?>&item_id=<?=$qry["id"] ?>"><?=$qry["name"] ?>
                    <p class="ui-li-aside"><strong><? echo $qry["price"] ?></strong></p></a></li>					
		<?  
	} ?>
            </ul> 
	</div> 
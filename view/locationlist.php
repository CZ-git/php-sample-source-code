	<div data-role="header" data-position="fixed"> 
    	<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
		<h1><? echo $label; ?></h1> 
		<a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
    </div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>); background-size: 100% 100%;"> 
		<ul data-role="listview"> 
        <? 	$countrow=0;
			while ($qry = mysql_fetch_array($res)) { $countrow++?>
			<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a style="padding-left:70px; height: 59px" href="?controller=LocationViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$qry["id"] ?>"><span class="pin b"></span><strong><?=$qry["city"].", ".$qry["state"] ?></strong><br><font style="font-size:11px; font-style:normal"><? echo $qry["address_1"] ?></font></a></li> 
		<? } ?>
    	</ul>
	</div>
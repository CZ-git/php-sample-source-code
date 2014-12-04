<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
		<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a>
	</div> 
	<div data-role="content" style="background:url(<?=$image_file; ?>) no-repeat; background-size: 100% 100%;">
    <ul data-role="listview" data-divider-theme="d"> 
<? 	$countrow=0;
	
	while ($qry = mysql_fetch_array($res)) 
	{ 
	//print_r($qry);
	$countrow++;
	?>
		<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
        <a href="?controller=CustomFormViewController&tab_id=<?=$tab_detail[id]?>&tk=<?=$qry["tk"]?>&label=<?=$qry["form_title"]?>"><?=$qry["form_title"]?></a>
        </li>					
<? 	}
?>
    </ul> 
	</div> 
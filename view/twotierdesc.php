	<div data-role="header" data-position="fixed"> 
		<h1><? echo $qry2["name"]; ?></h1> 
		<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a>
		<a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
	</div> 
	
	<div id="desc" data-role="content" style="background: #<?=$qrypagecolor[background]?>; color: #<?=$qrypagecolor[foreground]?>">
    <? $qry2["description"] = preglinkmatch($qry2["description"]);?>
 	<p><? echo $qry2["description"] ?></p> 
	</div>
<div data-role="header" data-position="fixed"> 
		<h1><? echo $qry2["name"]; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-inline="true" data-role="button" data-icon="share"></a>
</div> 
    <? 
		$tabid=$_SESSION["eventtab"];
		$sqlpclr = "SELECT * FROM `page_colors` WHERE tab_id='$tabid'";
			//echo $sqlpclr;
			$respclr = mysql_query($sqlpclr, $conn);
			$qrypclr = mysql_fetch_array($respclr);
			if (!$qrypclr["background"]){ $qrypclr["background"] = "FFFFFF"; }
			if (!$qrypclr["foreground"]){ $qrypclr["foreground"] = "000000"; }	
			?>
	
	<div data-role="content" style="padding:0px !important;  background-color:#<? echo $qrypclr["background"]; ?>; color:#<? echo $qrypclr["foreground"]; ?>">
 <? echo $qry2["description"] ?></p> 
	</div> 
	
	<?
	include_once "view/leftsidepanel.php";
	?>
<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a>
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%;">
    <ul data-role="listview" data-divider-theme="d"> 
<? while ($qry = mysql_fetch_array($res1)) 
	{ ?>
				
					<? if ($qry["section"] != '') {?><li data-role="list-divider"><? echo $qry["section"] ?></li> <? }?>
					<? 	$section = mysql_real_escape_string($qry["section"]);
						$sql = "SELECT id,name FROM info_categories WHERE tab_id ='$tab_detail[id]' and section ='$section' and is_active=1 order by seq";
						$res2 = mysql_query($sql, $conn);
						if ( mysql_num_rows($res2) > 0 ) {
							$countrow=0;
							while ($qry2 = mysql_fetch_array($res2)) {
								$countrow++
					?>
							<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                            <a href="?controller=InfoSectionViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$qry2["id"] ?>&label=<?=urlencode($qry2["name"]) ?>">
                            <?
							$dir = findUploadDirectory($app_id)."/info";
							if(file_exists($dir."/cat_".$qry2[id].".png")) {
								$thumbnail = '/custom_images/'.$app_code.'/info/cat_'.$qry2["id"].'.png';
								?>
                                <img src="<?=$thumbnail?>&width=80">
							<?
							}?>
							<h2><?=$qry2["name"] ?></h2>
                            </a>
                            </li>					
					 											  <? } 
								}  
	}
 ?>
            </ul> 
	</div>
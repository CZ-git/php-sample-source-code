<div data-role="header" data-position="fixed"> 
		<h1><? echo urldecode($label); ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%;">
    <ul data-role="listview" data-divider-theme="d"> 
<? while ($qry = mysql_fetch_array($res1)) 
	{ 
	 			if ($qry["section"]!='') {?>
    			<li data-role="list-divider"><?=$qry["section"] ?></li>
                <? }?>
					<? 	$section = mysql_real_escape_string($qry["section"]);
						$sql = "SELECT id, name, img_thumb FROM `info_items` WHERE `info_category_id`='$tab_detail[cat_id]' and section ='$section' and is_active=1 order by seq";
						$res2 = mysql_query($sql, $conn);
						if ( mysql_num_rows($res2) > 0 ) {
							$countrow=0;
							while ($qry2 = mysql_fetch_array($res2)) {	
							$countrow++;
					?>
							<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                            <a href="?controller=InfoSectionViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$tab_detail[cat_id] ?>&item_id=<? echo $qry2["id"] ?>">
							<?
							
							if($qry2[img_thumb] !="")
							{
								$image_file = findUploadDirectory($app_id, "tier") . "/$qry2[img_thumb]";
								$image="/custom_images/$app_code/$qry2[img_thumb]?extra=tier";
							}
							else 
							{
								$image_file = findUploadDirectory($app_id, "tier") . "/$qry2[id].jpg";
								$image="/custom_images/$app_code/$qry2[id].jpg?extra=tier";
							}

                            if (file_exists($image_file)) {?>
                            <img src="<?=$image?>&width=80">
                            <?
							}
							?>
                    		<h2><? echo $qry2["name"] ?></h2>
                            </a>
                            </li>					
					 											  <? } 
						}  
	} ?>
            </ul> 
	</div>
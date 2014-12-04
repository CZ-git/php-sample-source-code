	<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
	</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%; -webkit-background-size: 100% 100%;">
    		<ul data-role="listview" data-divider-theme="d"> 
	<? 		while ($qry = mysql_fetch_array($res)) 
			{ 
				if($qry["section"]!='')
				{?>
					<li data-role="list-divider"><? echo $qry["section"] ?></li>
                    <? 	}
                    		$section = mysql_real_escape_string($qry["section"]);
							$sql2 = 	"select id, name, img_thumb
										from info_items
										where info_category_id = '$catid' and is_active = 1 and section = '$section'
										order by seq";
					$res2 = mysql_query($sql2, $conn);
					if ( mysql_num_rows($res2) != 0 ) 
					{
						$countrow = 0;
						while ($qry2 = mysql_fetch_array($res2)) 
						{
						$countrow++;								
						?>
						<li <?=$countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                            			<a href="?controller=InfoItemsViewController&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$qry2["id"] ?>">
                            		<?
							$image="/custom_images/$app_code/$qry2[img_thumb]";
                            			if ($qry2[img_thumb]!="") 
                            			{?>
                            				<img src="<?=$image?>&width=80&height=80&extra=tier">
                            		<?	}	?>
                    				<h2><?=$qry2["name"] ?></h2>
                            			</a>
                            		</li>					
					  <? 	} 
					}  
			} ?>
            </ul> 
	</div>
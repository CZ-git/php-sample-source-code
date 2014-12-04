<? 
if($data[settings][moreButtonNavigation] == "YES") 
{ 
	$resfooter = tabstobeseen($data[settings][AppID], $conn);
	?><div data-role="footer" class="nav-glyphish-example" data-id="myfooter" id="newfooter" data-theme="a" > 
		<div data-role="navbar" <? if (mysql_num_rows($resfooter)<3) { ?> data-grid="a" <? } else if (mysql_num_rows($resfooter)<4) { ?> data-grid="b" <? } else if (mysql_num_rows($resfooter)<5) { ?> data-grid="c" <? } else { ?>data-grid="d" <? } ?> >
			<ul>
<?php
		
			for ( $counter = 1; $counter <= mysql_num_rows($resfooter); $counter += 1) 
			{
				$qry = mysql_fetch_array($resfooter);
				if ($counter < 5) 
				{
					$labelquotechk=rawurlencode($qry["tab_label"]);
					if ($qry["view_controller"] == 'WebViewController')
					{	
						$sql1 = "SELECT * FROM `web_views` WHERE `tab_id` =".$qry["id"];
						$res1 = mysql_query($sql1, $conn);
						if ( mysql_num_rows($res1) == 1 )
						{
							$qry1 = mysql_fetch_array($res1);
							$website=$qry1["url"];
							$urlleft= str_split($website, 4);
							if( $urlleft[0] != "http")
							{
								$website = "http://".$website;
							}
						}
					}
					//$sqltabback = "SELECT x.tab_src,x.tab_icon,x.tab_showtext,x.tab_text FROM  `template_detail` x LEFT JOIN  `template_tab` c ON c.detail_id = x.id WHERE c.tab_id =  '$qry[id]' LIMIT 0 , 1";
					$sqltabback = "select r.tab_src,r.tab_icon,r.tab_showtext,r.tab_text, r.tab_tint, r.tab_tint_opacity from 
								( SELECT x.* FROM  `template_detail` x LEFT JOIN  `template_tab` c 
								ON c.detail_id = x.id WHERE c.tab_id =  '$qry[id]' union 
								SELECT  d.* FROM  `template_detail` d LEFT JOIN  `template_app` p 
								ON p.detail_id = d.id RIGHT JOIN  `apps_xtr` t 
								ON p.app_id = t.app_id RIGHT JOIN  `apps` a 
								ON p.app_id = a.id WHERE a.id = '".$data[settings][AppID]."' ) 
								as r limit 0,1";
					$restabback = mysql_query($sqltabback, $conn);
					if(mysql_num_rows($restabback))
					{
						$qrytabback = mysql_result($restabback, 0, 0);
						$qrytabstyle = mysql_result($restabback, 0, 1);
						$qryshowtext = mysql_result($restabback, 0, 2);
						$qrytextcolor = mysql_result($restabback, 0, 3);
						$qrytint = mysql_result($restabback, 0, 4);
						$qrytintopacity = mysql_result($restabback, 0, 5);
						//echo $qrytabback."< >".$qrytabstyle."< >".$qryshowtext."< >".$qrytextcolor;
						//$tabicon = footericonreturn($qry["tab_icon_new"],$data[settings][AppID],$qrytabstyle,$qry["tab_icon"]);
						$tabicon = footericonreturn($qry[tab_icon_new],$data[settings][AppID],$qrytabstyle,$qry[tab_icon]);
						if(substr($qrytabback,0,6)=='custom')
						{ 
							$qrytabback="/custom_images/".$_SESSION['app_code']."/templates/buttons/$qrytabback";
						}
						else
						{
							$qrytabback="/tab_buttons/$qrytabback";
						}
						if ($qryshowtext==0)
						{ 
							$qry["tab_label"]=''; 
						} 
						$color = Hexconvert($qrytint); $transparency = $qrytintopacity*1/100;
					}
					else
					{
						//$tabicon = footericonreturn($qry["tab_icon_new"],$data[settings][AppID],$_SESSION['styletab'],$qry["tab_icon"]);
						$tabicon = footericonreturn($qry[tab_icon_new],$data[settings][AppID],$_SESSION['styletab'],$qry[tab_icon]);
						if ($show_menu_text==0)
						{
							$qry["tab_label"]='';	$lngmore='';
						}
						unset($qrytextcolor);
					}
					?>
					<li style="background-image: url(<?=$qrytabback?>);  background-size: 100% 100%;">
					<style>
					<?
					echo ".ui-icon-ic$qry[id]:after { $tabicon }";
					if(isset($qrytextcolor) && $qrytextcolor!='')
					{
						echo ".ui-icon-ic$qry[id], .ui-icon-ic$qry[id]:hover { color: #$qrytextcolor !important; background: rgba($color[r],$color[g],$color[b],$transparency) !important;  }";
					}
					?>
					</style>
					<a <?	if ($website) 
						{
							echo webredirection($website,$qry1[id]); unset($website);
						} 
						else if (($qry["view_controller"] == 'HomeViewController'))
						{ 
						?> href="<?=$_SESSION['app_website']?>" rel="external" <?
						}
						else if ($qry["view_controller"] == 'OrderingViewController')
						{
						?> href="/html5/orderhtml5/?p=orderloc&app_code=<?=$app_code?>&label=<?=$labelquotechk?>&tab_id=<?=$qry["id"]?>&controller=OrderingViewController" <?	
						}
						else if ($qry["view_controller"] == 'MerchandiseViewController')
						{
						?> href="/html5/orderhtml5/?p=orderloc&app_code=<?=$app_code?>&label=<?=$labelquotechk?>&tab_id=<?=$qry["id"]?>&controller=MerchandiseViewController" <?
						}
						else if ($qry["view_controller"] == 'CustomFormViewController')
						{
							$sqlform="SELECT tk FROM `form_layout` WHERE tab_id =".$qry["id"]." AND is_active='1'";
							$resform = mysql_query($sqlform, $conn);
							$formtk = mysql_result($resform, 0, 0);
							if(mysql_num_rows($resform)>1)
							{
								echo "href='?controller=CustomFormViewController&tab_id=$qry[id]&label=$labelquotechk'";
							}
							else
							{
								echo "href='?controller=CustomFormViewController&tab_id=".$qry[id]."&tk=$formtk&label=".$qry["tab_label"]."'";
							}
						}
						else if ($qry["view_controller"] == 'EventsManagerViewController')
						{
						?> href="?controller=EventsViewController&tab_id=<?=$qry["id"]."&label=".$labelquotechk ?>" 
						<?
						}
						else if ($qry["view_controller"] == 'FanWallManagerViewController')
						{
						?> href="?controller=FanWallViewController&tab_id=<?=$qry["id"]."&label=".$labelquotechk ?>" 
						<?
						}
						else 
						{ 
						?> href="?controller=<?=$qry["view_controller"]?>&tab_id=<? echo $qry["id"] ?>&label=<? echo $labelquotechk ?>" <? 
						} ?> id="<? echo "ic".$qry["id"]  ?>" class="ui-icon-<?="ic".$qry["id"]?> ui-btn-icon-top"><? echo $qry["tab_label"] ?>
					</a>
					</li>
			<? 	} 
				if($counter == 5) 
				{
					if(mysql_fetch_array($resfooter)>5)
					{
						$sqltabback = "SELECT t . * , x . * , c . * 
									FROM  `app_more_tabs` x
									LEFT JOIN  `template_detail` c ON c.app_id = x.app_id
									LEFT JOIN  `template_tab` t ON t.app_id = c.app_id
									AND t.tab_id = t.tab_id
									WHERE c.app_id =  '".$data[settings][AppID]."'
									AND c.tab_id =  '0'
									AND c.id = t.detail_id
									LIMIT 0 , 1";
						$restabback = mysql_query($sqltabback, $conn);
						$qrytabmore=mysql_fetch_array($restabback);
						if(mysql_num_rows($restabback))
						{ 
							$qrytabback = $qrytabmore[tab_src];
							$qrytabstyle = $qrytabmore[tab_icon_new];
							$qryshowtext = $qrytabmore[tab_showtext];
							$qrytextcolor = $qrytabmore[tab_text];
							$lngmore = $qrytabmore[tab_label];
							$qrytint = $qrytabmore[tab_tint];
							$qrytintopacity = $qrytabmore[tab_tint_opacity];
							$tabicon = "background: url(/tab_icons/$qrytabmore[tab_icon_new]) 50% 50% no-repeat;";
							if(substr($qrytabback,0,6)=='custom')
							{
								$qrytabback="/custom_images/".$_SESSION['app_code']."/templates/buttons/$qrytabback";
							}
							else
							{
								$qrytabback="/tab_buttons/$qrytabback";
							}
							if ($qryshowtext==0)
							{
								$lngmore=''; 
							}
								$color = Hexconvert($qrytint); $transparency = $qrytintopacity*.5/100;
						}
						else
						{
							$tabicon = footericonreturn("(190).png",$data[settings][AppID],$_SESSION['styletab'],"(190).png");
							if ($show_menu_text==0) 
							{ 
								$lngmore=''; 
							}
						} ?>
				   		<li style="background-image: url(<?=$qrytabback?>);  background-size: 100% 100%;">
				   		<style>
						<?
						echo ".ui-icon-ic".$data[settings][AppID].":after { $tabicon }";
						if(isset($qrytextcolor) && $qrytextcolor!='')
						{
							echo ".ui-icon-ic".$data[settings][AppID].", .ui-icon-ic".$data[settings][AppID].":hover { color: #$qrytextcolor !important; background: rgba($color[r],$color[g],$color[b],$transparency) !important; }";
						}
						else 
						{
							echo ".ui-icon-ic".$data[settings][AppID].", .ui-icon-ic".$data[settings][AppID].":hover { color: #$qrytextcolor !important; background: rgba($color[r],$color[g],$color[b],$transparency) !important; }";
						}
						?> </style><a class="ui-icon-ic<?=$data[settings][AppID]?> ui-btn-icon-top" href="more.php"><?=$lngmore;?></a></li> <?
					}
				   	else
				   	{
				   		$sqltabback = "select r.tab_src,r.tab_icon,r.tab_showtext,r.tab_text, r.tab_tint, r.tab_tint_opacity from 
									( SELECT x.* FROM  `template_detail` x LEFT JOIN  `template_tab` c 
									ON c.detail_id = x.id WHERE c.tab_id =  '$qry[id]' union 
									SELECT  d.* FROM  `template_detail` d LEFT JOIN  `template_app` p 
									ON p.detail_id = d.id RIGHT JOIN  `apps_xtr` t 
									ON p.app_id = t.app_id RIGHT JOIN  `apps` a 
									ON p.app_id = a.id WHERE a.id =  '".$data[settings][AppID]."' ) 
									as r limit 0,1";
						$restabback = mysql_query($sqltabback, $conn);
						if(mysql_num_rows($restabback))
						{
							$qrytabback = mysql_result($restabback, 0, 0);
							$qrytabstyle = mysql_result($restabback, 0, 1);
							$qryshowtext = mysql_result($restabback, 0, 2);
							$qrytextcolor = mysql_result($restabback, 0, 3);
							$qrytint = mysql_result($restabback, 0, 4);
							$qrytintopacity = mysql_result($restabback, 0, 5);
							$tabicon = footericonreturn($qry[tab_icon_new],$data[settings][AppID],$qrytabstyle,$qry[tab_icon]);
							if(substr($qrytabback,0,6)=='custom') 
							{ 
								$qrytabback="/custom_images/".$_SESSION['app_code']."/templates/buttons/$qrytabback";
							}
							else 
							{
								$qrytabback="/tab_buttons/$qrytabback";
							}
							if ($qryshowtext==0)
							{ 
								$qry["tab_label"]='';
							} 
							$color = Hexconvert($qrytint); $transparency = $qrytintopacity*.5/100;
						}
						else
						{
							$tabicon = footericonreturn($qry[tab_icon_new],$data[settings][AppID],$_SESSION['styletab'],$qry[tab_icon]);
							if ($show_menu_text==0) 
							{ 
								$qry["tab_label"]='';
								$lngmore=''; 
							}
							unset($qrytextcolor);
						}?> 
						<li style="background-image: url(<?=$qrytabback?>);  background-size: 100% 100%;"><style>.ui-icon-<?="ic".$qry["id"]?>:after { <?=$tabicon?> } .ui-icon-ic<?=$qry[id]?>, .ui-icon-ic<?=$qry[id]?>:hover { color: #<?=$qrytextcolor?> !important; background: rgba(<?=$color[r].",".$color[g].",".$color[b].",".$transparency;?>) !important;  } </style>
						<a href="?controller=<?=$qry["view_controller"] ?>&tab_id=<? echo $qry["id"] ?>&label=<? echo $labelquotechk ?>" id="<? echo "ic".$qry["id"]  ?>" class="ui-icon-<?="ic".$qry["id"]?> ui-btn-icon-top" ><? echo $qry["tab_label"] ?>
						</a>
						</li>
				<?	}
				}
			} ?>
			</ul> 
		</div><!-- /navbar -->  
	</div> <?
}
?>

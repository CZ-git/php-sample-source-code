<? 
if($data[settings][moreButtonNavigation] == "NO") 
{
	$resfooter = tabstobeseen($data[settings][AppID], $conn);
	if ($data[settings][cols]<5)
	{
	$column=4;
	}
	else
	{
		$column=5;
	}
	$page = ceil(mysql_num_rows($resfooter)/$column);?>
	<style>	
	<? 	$color = Hexconvert($data[settings][tab_tint]); $transparency = $data[settings][tab_tint_opacity]*1/100;?>
		.nav-glyphish-example .ui-btn { 
			padding-top: 35px !important;
			background: rgba(<?=$color['r'].",".$color['g'].",".$color['b'].",".$transparency?>)
		}
		.nav-glyphish-example .ui-btn .ui-icon:after { 
			width: 40px!important; 
			height: 35px!important; 
			margin-left: -20px !important; 
			box-shadow: none!important; 
			-moz-box-shadow: none!important; 
			-webkit-box-shadow: none!important; 
			-webkit-border-radius: none !important; 
			border-radius: none !important; 
		}	
		body>div#newfooter {
		      position: fixed;
			  display:none;
		}
		* html .container {
		    height:100%;
		    overflow:auto;
		}
		#newfooter {/*
			-webkit-animation-name: slideInRight;
			animation-name: slideInRight;
			-webkit-animation-duration: 2s;
			-webkit-animation-iteration-count: 1;
			-webkit-animation-direction: alternate;*/;
		    bottom: 0px;
		    left: 0px;
		    text-align: center;
		    width: 100%;
		    position: absolute;
		}
		#wrapper {
			width:100%;
			float:left;
			position:relative;	/* On older OS versions "position" and "z-index" must be defined, */
			z-index:1;			/* it seems that recent webkit is less picky and works anyway. */
			overflow:hidden;
		}
		#scroller {
			width:<?=$page*100;?>%;
			height:100%;
			float:left;
			padding:0;
		}
		#scroller ul {
			list-style:none;
			display:block;
			float:left;
			width:100%;
			height:100%;
			padding:0;
			margin:0;
			text-align:left;
		}
		#scroller li {
			-webkit-box-sizing:border-box;
			-moz-box-sizing:border-box;
			-o-box-sizing:border-box;
			box-sizing:border-box;
			display:block; 
			float:left;
			height:65px;
			text-align:center;
			font-family:georgia;
			font-size:18px;
			line-height:140%;
		}
		.table {
			bottom: 0px;
			text-align: center;
			position: absolute;
			width: 100%;
			z-index:999;
		}
		#nav {
			margin:0;
			padding:0;
		}
		#nav > li {
			display:inline-block;
			width: 4px !important;
			height: 4px !important;
			border-radius: 2px !important;
			background:#999;
			padding-right:0;
			text-indent: -9999em;
			-webkit-border-radius: 2px;
			-moz-border-radius: 2px;
			-o-border-radius: 2px;
			overflow: hidden;
		}
		#nav li.active {
			background:#fff;
		}
		.ui-bar-a {
			border:none;
		}
		.ui-footer .ui-icon:after {
			background-size: 40px 35px !important;
		}
	</style>
	<div id="newfooter">
		<div id="wrapper">
			<div id="scroller">
				<ul id="thelist">
<? 		
				while($qrytab[] = mysql_fetch_array($resfooter)){}
					$counter = 0;
					if($resfooter) 
					{
						while($counter+1 <= mysql_num_rows($resfooter)) 
						{
?>					<li id="main">
				            	<div data-role="footer" data-theme="a" style="background:none" class="nav-glyphish-example"> 
									<div data-role="navbar" <? 
										if ((mysql_num_rows($resfooter))<3) 
										{ ?> data-grid="a" <? 
										} 
										else if ((mysql_num_rows($resfooter))<4) 
										{ ?> data-grid="b" <? 
										} 
										else if ((mysql_num_rows($resfooter))<5) 
										{ ?> data-grid="c" <? 
										} 
										else 
										{
											if($column==4) 
											{ 
											?>  data-grid="c" <? 
											} 
											else 
											{?> data-grid="d" <? 
											}
										} 
										?>>
										<ul>
<?											$counterw=$counter+$column;
											for ( ;$counter < $counterw; $counter++) 
											{
												$labelquotechk=rawurlencode($qrytab[$counter]["tab_label"]);
												if ($qrytab[$counter]["view_controller"] == 'WebViewController')
												{	
													$sql1 = "SELECT * FROM `web_views` WHERE `tab_id` =".$qrytab[$counter]["id"];
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
												$sqltabback = "select r.tab_src,r.tab_icon,r.tab_showtext,r.tab_text, r.tab_tint, r.tab_tint_opacity from 
															( SELECT x.* FROM  `template_detail` x LEFT JOIN  `template_tab` c 
															ON c.detail_id = x.id WHERE c.tab_id =  '".$qrytab[$counter][id]."' union 
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
													$tabicon = footericonreturn($qrytab[$counter][tab_icon_new],$data[settings][AppID],$qrytabstyle,$qrytab[$counter][tab_icon]);
													if(substr($qrytabback,0,6)=='custom') { $qrytabback="/custom_images/".$_SESSION['app_code']."/templates/buttons/$qrytabback";}
													else {$qrytabback="/tab_buttons/$qrytabback";}
													if ($qryshowtext==0) { $qrytab[$counter][tab_label]='&nbsp;'; } 		
													$color = Hexconvert($qrytint); $transparency = $qrytintopacity*.5/100;		
												}
												else
												{
													$tabicon = footericonreturn($qrytab[$counter][tab_icon_new],$data[settings][AppID],$_SESSION['styletab'],$qrytab[$counter][tab_icon]);
													if ($show_menu_text==0) { $qrytab[$counter][tab_label]='&nbsp;'; }
												} ?>
		            							<li style="background-image: url(<?=$qrytabback?>);  background-size: 100% 100%; <?if(!isset($qrytab[$counter]["tab_label"])){?> display:none<?}?>">
													<style>
													<?	echo ".ui-icon-ic".$qrytab[$counter][id].":after { $tabicon }";
														echo ".ui-icon-ic".$qrytab[$counter][id].", .ui-icon-ic".$qrytab[$counter][id].":hover { color: #$qrytextcolor !important; background: rgba($color[r],$color[g],$color[b],$transparency) !important;  }";
													?>
													</style>
										            <a <? 
													if ($website) 
													{ 
														echo webredirection($website,$qry1[id]); unset($website);
													}
													else if ($qrytab[$counter]["view_controller"] == 'OrderingViewController')
													{
														echo "href='orderhtml5/?p=orderloc&app_code=".$_SESSION['app_code']."&tab_id=".$qrytab[$counter][id]."&label=".$qrytab[$counter][tab_label]."&controller=OrderingViewController'";
										         	}
													else if ($qrytab[$counter]["view_controller"] == 'HomeViewController')
													{
														echo "href='$_SESSION[app_website]' rel='external'";
													}
													else if ($qrytab[$counter]["view_controller"] == 'FanWallManagerViewController')
													{
														echo "href='?controller=FanWallViewController&tab_id=".$qrytab[$counter][id]."&label=".$qrytab[$counter][tab_label]."'";
										         	}
													else if ($qrytab[$counter]["view_controller"] == 'EventsManagerViewController')
													{
														echo "href='?controller=EventsViewController&tab_id=".$qrytab[$counter][id]."&label=".$qrytab[$counter][tab_label]."'";
										         	}
													else if ($qrytab[$counter]["view_controller"] == 'MerchandiseViewController')
													{
														echo "href='orderhtml5/?p=orderloc&app_code=".$_SESSION['app_code']."&tab_id=".$qrytab[$counter][id]."&label=".$qrytab[$counter][tab_label]."&controller=MerchandiseViewController'";
													}
										            else if ($qrytab[$counter]["view_controller"] == 'CustomFormViewController')
													{
														$sqlform="SELECT tk FROM `form_layout` WHERE tab_id =".$qrytab[$counter][id];
														$resform = mysql_query($sqlform, $conn);
														$formtk = mysql_result($resform, 0, 0);
														if(mysql_num_rows($resform)>1)
														{
															echo "href='?controller=CustomFormViewController&tab_id=".$qrytab[$counter][id]."&label=".$qrytab[$counter][tab_label]."'";
														}
														else
														{
															echo "href='?controller=CustomFormViewController&tab_id=".$qrytab[$counter][id]."&tk=$formtk&label=".$qrytab[$counter][tab_label]."'";
														}
													}
													else 
													{ 
										            	echo "href='?controller=".$qrytab[$counter][view_controller]."&tab_id=".$qrytab[$counter][id]."&label=$labelquotechk'";  
													} 
										    		echo " id='ic".$qrytab[$counter][id]."' data-icon='ic".$qrytab[$counter][id]."' data-iconpos='top'>".$qrytab[$counter][tab_label] ?>
										    		</a>
									   			</li> <?
									   		}?>
										</ul> 
									</div><!-- /navbar -->
								</div> 
	            			</li>
<? 						} // end of while($counter+1 <= mysql_num_rows($resfooter))
					} // end of if($resfooter)?>
				</ul>
			</div>
		</div>
		<div class="table">
			<ul id="nav">
			<? 	$counterr =0;
					while($counterr!=$page)
					{
						$counterr++;
				?>
					<li <? if($counterr==1) {?>class="active"<? }?>>&nbsp;</li>
			        <?	}
				?>
			</ul>
		</div>
	</div>
<?
}?>
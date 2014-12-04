<? 
include_once "dbconnect.inc";
include_once "html5common.inc";
?>
<!DOCTYPE html> 
<html> 
	<head> 
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1"> 
	<title><?=$lngmore;?></title> 
	<link rel="stylesheet" href="default.css" />  
	<link rel="stylesheet" href="css/jqm-docs.css"/> 
	<script src="js/jquery.js"></script> 
	<script src="js/default.js"></script>
</script>
</head> 
<body> 
<? if ($isnewdesign=='1') 
	{
		$image_file = get_app_bg_html5($conn, '51', '0', '0',$app_code, $app_id);
 	}
   else 
   	{ 
		$image_file = "/custom_images/$app_code/xtra_imgs/iphone.jpg"; 
	} ?>
<div data-role="page"> 
	
	<div data-role="header" data-position="fixed"> 
		<h1><?=$lngmore;?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a>
	</div> 
	
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%; -webkit-background-size: 100% 100%;"> 
<ul data-role="listview"> 
<?php

/* 
Tabs feed
Compare message date to date of installation on handset?
*/


$data = make_data_safe($_GET);
// $data["version"] == 2 => LocationListViewController is available

if ( $is_active == 1 ) {
			$sqlnewdesignchk = "select isNewDesign from apps where id = $app_id LIMIT 1";
			$resnewdesignchk = mysql_query($sqlnewdesignchk, $conn);
			//echo $sqlnewdesignchk;
			if (mysql_fetch_array($resnewdesignchk)) {
				$isnewdesign = mysql_result($resnewdesignchk, 0, 0); // store new design value
				//echo $isnewdesign;
				if ($isnewdesign=='1')
				{
				$newdesign = $isnewdesign;
				//echo $newdesign;
				}
			}
	if ( mysql_num_rows($res) == 0 ) {  // Let's hope this never happens
	   $feed[] = array("tab_label" => "No tabs");
	}
	else {
		 $counter = 0;
		 $countrow = 0;
		$respanel = tabstobeseen($_SESSION[app_id], $conn);
		while ($qry = mysql_fetch_array($respanel)) {
		$newicon = $qry["tab_icon_new"];
		$counter++;
		$countrow++;		
// Add tab_id to everything now, even though not every view controller may support it
			
           if ($counter>4){ 
		   $labelquotechk=rawurlencode($qry["tab_label"]);			
			if (substr($qry["tab_icon_new"], 0,6) == "modern")
			{
					if($qry["tab_icon_new"])
					{
						$iconused="/tab_icons/$_SESSION[styletab]/".$qry["tab_icon_new"];
					} 
					else 
					{
						$iconused="/tab_icons/classic/".$qry["tab_icon"];
					}
			}
			else if (substr($qry["tab_icon_new"], 0,1) == "(" || substr($qry["tab_icon_new"], 0,6) == "custom")
			{
					$tab_icon_new = rawurlencode($qry["tab_icon_new"]);
					if($tab_icon_new)
					{
						$iconused="/tab_icons/$_SESSION[styletab]/$qry[tab_icon_new]";
					} 
					else 
					{
						$iconused="/tab_icons/classic/".$qry["tab_icon"];
					}
			}
			else
			{ 
					$iconused = "/tab_icons/classic/".$qry["tab_icon"];
			}
		   if ($qry["view_controller"] == 'WebViewController')
			{	
						$sql2 = "SELECT * FROM `web_views` WHERE `tab_id` =".$qry["id"];
						$res2 = mysql_query($sql2, $conn);
						if ( mysql_num_rows($res2) == 1 )
						{
								$qry2 = mysql_fetch_array($res2);
								$website2=$qry2["url"];
								$urlleft2= str_split($website2, 4);
								if( $urlleft2[0] != "http")
								{
									$website2 = "http://".$website2;
								}
						}
			}?>
			<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
		<?  if ($qry["view_controller"] == 'OrderingViewController') 
			{ ?>
            <a href="orderhtml5/?p=orderloc&app_code=<?=$app_code?>&tab_id=<?=$qry["id"]?>&controller=OrderingViewController"><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a><? 
			} else if ($qry["view_controller"] == 'MerchandiseViewController') 
			{ ?>
            <a <?=merchandisetab($app_code,$qry[id], $conn); ?>><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a>
		<?  }
			else if ($qry["view_controller"] == 'CustomFormViewController')
			{
					$sqlform="SELECT tk FROM `form_layout` WHERE tab_id =".$qry["id"]." AND is_active='1'";
					$resform = mysql_query($sqlform, $conn);
					$formtk = mysql_result($resform, 0, 0);
					if(mysql_num_rows($resform)>1)
					{
						echo "<a href='./?controller=CustomFormViewController&tab_id=".$qry[id]."&label=".$qry[tab_label]."'> <img src='$iconused' alt='$qry[tab_label]' class='ui-li-icon' style='top:0.5em !important; width:25px !important; height:25px'>$qry[tab_label]</a>";
					}
					else
					{
						echo "<a href='./?controller=CustomFormViewController&tab_id=".$qry[id]."&tk=$formtk&label=".$qry["tab_label"]."'> <img src='$iconused' alt='$qry[tab_label]' class='ui-li-icon' style='top:0.5em !important; width:25px !important; height:25px'>$qry[tab_label]</a>";
					}
			} 
			else if ($qry["view_controller"] == 'EventsManagerViewController') 
			{ ?>
            <a href="./?controller=EventsViewController&tab_id=<?=$qry["id"]."&label=".$qry["tab_label"] ?>"><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a>
		<?  }
			else if ($qry["view_controller"] == 'FanWallManagerViewController') 
			{ ?>
			<a href="./?controller=FanWallViewController&tab_id=<?=$qry["id"]."&label=".$qry["tab_label"] ?>"><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a>
		<?  }
			else
			if ($website2) 
			{ ?><a <? echo webredirection($website2,$qry2[id]); unset($website2); ?>"><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a></li><? 
			} 
			else if ($qry["view_controller"] == 'HomeViewController')
			{?>
				<a href="<?=$_SESSION[app_website] ?>" rel='external'><img src="<?=$iconused ?>" alt="<?=$qry["tab_label"]?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a>
			<?
            }
			else 
			{ ?>
           <a href="./?controller=<?=$qry["view_controller"]?>&tab_id=<? echo $qry["id"] ?>&label=<? echo $labelquotechk ?>"><img src="<? echo $iconused ?>" alt="<? echo $qry["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qry["tab_label"]  ?></a>
		 <? }?></li> <?
		 } 
		   } 
		}
	} 
?>
	  </ul>
	</div> 
<?php include_once "view/leftsidepanel.php";?>
</div><!-- /page --> 
 
</body> 
</html>
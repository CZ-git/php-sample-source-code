<? 
$domainfanchk=substr($_SERVER['HTTP_HOST'], 0, 4);
if(substr_count($_SERVER['HTTP_HOST'],".")>1)
{
	$_SESSION['domain'] = $_SERVER['HTTP_HOST'].$path;
}
else
{
	$_SESSION['domain'] = "www.".$_SERVER['HTTP_HOST'].$path;
}
if ($_GET["label"])
{
	$id = mysql_real_escape_string($_GET["id"]);
	$label = mysql_real_escape_string($_GET["label"]);
}
else
{
	$label = $_SESSION['label_Fan'];
	$id = $_SESSION['id_Fan'];
}

$_SESSION['id_Fan'] = $tab_detail[id];
$_SESSION['label_Fan'] = $label;
 // store session data
if (array_key_exists("login", $_GET)) {
    $oauth_provider = $_GET['oauth_provider'];
	$_SESSION['fanid'] = $_GET['login'];
	$_SESSION[detail_id] = '0';
	if(!empty($_GET['login']))
	{
		$_SESSION[commentpage]="?controller=FanWallViewController&tab_id=".$_SESSION['id_Fan']."&fanid=".$_GET['login']."&label=".$_SESSION['label_Fan'];
	}
	else 
	{
		$_SESSION[commentpage]="?controller=FanWallViewController&tab_id=".$_SESSION['id_Fan']."&label=".$_SESSION['label_Fan'];
	}
    if ($oauth_provider == 'twitter') {
        //header("Location: login-twitter.php");?>
        <script> window.location = "<?=$socialext?>/login-twitter.php?domain=<?=$_SESSION['domain']?>" </script>
    <?
    } else if ($oauth_provider == 'facebook') {
		?>
        <script> window.location = "<?=$socialext?>/login-facebook.php?domain=<?=$_SESSION['domain']?>" </script>
    <? }
}
?>
<body> 
<div data-role="page" id="fanwall">
    <style>
	#popaddfan .ui-btn-left{
		display:none;
		text-decoration:none
	}
	</style>
	<div data-role="header" data-position="fixed">
    	<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
		<h1><? echo $label?></h1>
		<div class="ui-btn-right">
		    	<a href="#popaddfan<?=$_GET['fanid']?>" data-position-to="window" data-inline="true" data-rel="popup" data-role="button" data-theme="a" data-icon="addcomment"></a>
        		<a href="#popshareloc<?=$id?>" data-inline="true" data-role="button" data-icon="share"></a>
		</div>
	</div>
    <div data-role="popup" id="popaddfan<?=$_GET['fanid']?>" data-theme="a" class="ui-corner-all">
		<div data-role="header" data-theme="b" class="ui-corner-top"> 
			<h1><?=$lnglogin?></h1> 
		</div> 
		<div data-role="content"> 
    	<a href="?controller=FanWallViewController&tab_id=<?=$_SESSION['id_Fan']?>&login=<?=$_GET['fanid']?>&oauth_provider=twitter" onClick="window.location = $(this).attr('href')" rel="external" data-role="button" data-theme="b">Twitter</a>
   		<a href="?controller=FanWallViewController&tab_id=<?=$_SESSION['id_Fan']?>&login=<?=$_GET['fanid']?>&oauth_provider=facebook" onClick="window.location = $(this).attr('href')" rel="external" data-role="button" data-theme="b">Facebook</a>
		</div> 
	</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>);background-size: 100% 100%; background-repeat:no-repeat; padding:0px;"> <!--Newly added padding-->
    <?
    if($_GET['fanid'])
	{
		$fanid = $_GET['fanid'];
		$id = $_SESSION['id_Fan'];
		$time_ago = $_GET['time_ago'];
		$sql = "select * from fan_wall_comments where id='$fanid'";
		//echo $sql;
		$res = mysql_query($sql, $conn);
		$_SESSION[fanwallv2]=0;
		if($tab_detail["view_controller"] != "FanWallViewController")
		{
			$sql = "SELECT * FROM  `app_user_comments` where id='$fanid'";
			$res = mysql_query($sql, $conn);
			$_SESSION[fanwallv2]=1;
		}
		$qry = mysql_fetch_array($res);
		if($_SESSION[fanwallv2]==0)
		{
			if ($qry["facebook_id"])
				$image_url = "http://graph.facebook.com/".$qry["facebook_id"]."/picture";
			else
				$image_url = $qry[avatar]; 	
		}
		else
		{
			if ($qry["user_type"]==1)
				$image_url = "http://graph.facebook.com/".$qry["user_id"]."/picture";
			else
				$image_url = $qry[avatar]; 
		}?>
              		<div class="messages" id="innerpage-cmmnt">  <!--Newly added ID by SAZZAD-->
						<div class="thumbnail" ><img style="margin:0 10px 0 10px;" src="<?=$image_url?>" width="30" height="30" align="left" /></div>
						<div class="body" id="innerpg-body" > <!--Newly added ID by SAZZAD-->
	                    	<div class="name"><strong><?=$qry["comment"]?></strong></div>
                            <span class="comment parentcmnt"><?=$qry["name"] ?></span>
                        </div>	
                                
                        <fieldset class="ui-grid-a">
                           	<div class="ui-block-a"><!--img src="images/timefan.png"></img--><em><?=$time_ago?></em></div>
                                <!--div class="ui-block-b"></div-->	   
                        </fieldset>     
                    </div>
	<? 
			$sqlchild = "select *, unix_timestamp(created) as timestamp from fan_wall_comments where app_id = '$app_id' and tab_id='$tab_detail[id]' and parent_id='$fanid' order by created desc";
			$reschild = mysql_query($sqlchild, $conn);
			if(mysql_num_rows($reschild) < 1)
			{
				$sqlchild = "SELECT *, unix_timestamp(created) as timestamp FROM  `app_user_comments` where app_id = '$app_id' and tab_id='$tab_detail[id]' and parent_id='$fanid' order by created desc";
				$reschild = mysql_query($sqlchild, $conn);
			}
			if ( mysql_num_rows($reschild) > 0 )	
			{
				while ($qry = mysql_fetch_array($reschild)) 
				{ 
				if($_SESSION[fanwallv2]==0)
				{
					if ($qry["facebook_id"])
						$image_url = "http://graph.facebook.com/".$qry["facebook_id"]."/picture";
					else
						$image_url = $qry[avatar];
				}
				else
				{
					if ($qry["user_type"]==1)
						$image_url = "http://graph.facebook.com/".$qry["user_id"]."/picture";
					else
						$image_url = $qry[avatar]; 
				}
				$time_ago =  time_elapsed_string($qry["timestamp"]);		
			?>		<div class="messages">
			
							<div class="thumbnail"><img src="<?=$image_url?>" width="30" height="30" align="left" /></div>
							<div class="arrw"></div>
							
							<div class="cmmnt-box childcmnt">
			
                    			<div class="body">
                                    
                                    <div class="comment" style="color:#B3AFAF;"><strong><?//=$qry["name"] ?></strong><?=stripslashes(strip_tags($qry["comment"]))?></div>
                                    <!--span class="comment"><?//=$qry["comment"]?></span-->
                                </div>
                                <fieldset class="ui-grid-a">
                                <div class="ui-block-a" style="color:#000; font-size:14px;"><!--img src="images/timefan.png"></img><em><?//=$time_ago?></em--><strong><?=$qry["name"] ?></strong></div>
                                <div class="ui-block-b" style="border:none; color:#B3AFAF; font-weight:normal;"><!--img src="images/replyfan.png"></img><em>Reply</em--><em><?=$time_ago?></em></div>	   
                                </fieldset>
							</div>
                    </div>
                <?
				}
			}
	
	}
	else
	{
	?>
			<? 			$sql = "select *, unix_timestamp(created) as timestamp from fan_wall_comments where app_id = '$app_id' and tab_id='$tab_detail[id]' and parent_id='0' order by created desc";
						$res = mysql_query($sql, $conn);
						$_SESSION[fanwallv2]=0;
						if($tab_detail["view_controller"] != "FanWallViewController")
						{
							$sql = "SELECT *, unix_timestamp(created) as timestamp FROM `app_user_comments` where app_id = '$app_id' and tab_id='$tab_detail[id]' and parent_id='0' and detail_type='0' and detail_id='0' order by created desc";
							$res = mysql_query($sql, $conn);
							$_SESSION[fanwallv2]=1;
						}
						$now = date('Y-m-d');
						//echo $now;
						if ( mysql_num_rows($res) > 0 )	{
							while ($qry = mysql_fetch_array($res)) { 
								if($_SESSION[fanwallv2]==0)
								{
									if ($qry["facebook_id"])
										$image_url = "http://graph.facebook.com/".$qry["facebook_id"]."/picture";
									else
										$image_url = $qry[avatar];
								}
								else
								{
									if ($qry["user_type"]==1)
										$image_url = "http://graph.facebook.com/".$qry["user_id"]."/picture";
									else
										$image_url = $qry[avatar]; 
								}
							$time_ago =  time_elapsed_string($qry["timestamp"]);
						// Check replies count
						if($_SESSION[fanwallv2]==0)
							$sqlcount = "SELECT count(parent_id) FROM `fan_wall_comments` where parent_id='$qry[id]'";
						else
							$sqlcount = "SELECT count(parent_id) FROM `app_user_comments` where parent_id='$qry[id]'";
						$rescount = mysql_query($sqlcount, $conn);	
						$commentcount = mysql_result($rescount, 0, 0);		
						if($commentcount<1)
							$commentcount = $lngreply;
						else
							$commentcount = $commentcount." ".$lngreplies;  //This line is to show the word "Replies" with No. of comments By SAZZAD
			?>	
                <a href="?controller=FanWallViewController&tab_id=<?=$tab_detail[id]?>&fanid=<?=$qry["id"]?>&time_ago=<?=$time_ago?>">
                    <div class="messages">
					
							<div class="thumbnail"><img src="<?=$image_url?>" width="30" height="30" align="left" /></div>
							<div class="arrw"></div>
							
							<div class="cmmnt-box">
					
                    			<div class="body">
                                    
                                    <div class="name"><strong><?=stripslashes(strip_tags($qry["comment"]))?></strong></div>
                                    <span class="comment"><?=$qry["name"] ?></span>
                                </div>
                                <fieldset class="ui-grid-a">
                                <div class="ui-block-a"><em><?=$time_ago?></em></div>
                                <div class="ui-block-b"><em><?=$commentcount?> </em></div>	   
                                </fieldset>
							</div>
                    </div>
             	</a>
                
			<? 							} 
														} 
						?>
        <? } ?>
        <br><br><br>
        </div>	
    <?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 

</body>
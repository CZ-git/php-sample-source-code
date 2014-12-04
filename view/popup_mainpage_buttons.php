<? 
if ($data[settings][CallButton] == "YES") 
{?>
	<div data-role="popup" id="popcallus" data-theme="a" class="ui-corner-all">
		<div data-role="header" data-theme="b" class="ui-corner-top">
			<h1><?=$lngcallus;?></h1>
		</div>
		<div data-role="content" class="ui-corner-bottom ui-content">
 	           <h3 class="ui-title"><?=$lngbranch_call_title;?></h3>
			<?  $sql = "SELECT * FROM app_locations WHERE `app_id` ='".$data[settings][AppID]."' ORDER BY `id` ASC";
			//echo $sql;
			$res = mysql_query($sql, $conn);
			while ($qryc = mysql_fetch_array($res)) 
			{ ?>
				<a href="tel:<?=$qryc["telephone"]; ?>" data-role="button" data-theme="b"><?=$qryc["telephone"]."<br>".$qryc["city"]; if ($qryc["state"]) {?>, <? echo $qryc["state"]; }?> </a>
		<? 	}	?>
		</div>
	</div>
<? 
} 

if ($data[settings][DirectionButton] == "YES") 
{?>
	<div data-role="popup" id="popdirections" data-theme="a" class="ui-corner-all">
		<div data-role="header" data-theme="b" class="ui-corner-top">
			<h1><?=$lngdirection?></h1>
		</div>
		<div data-role="content" class="ui-corner-bottom ui-content">
      	      <h3 class="ui-title"><?=$lngbranch_directions_title?></h3>
		<?  	$sql = "SELECT `id` , `city` , `state` FROM app_locations WHERE `app_id` ='".$data[settings][AppID]."' ORDER BY `id` ASC";
			//echo $sql;
			$res = mysql_query($sql, $conn);
			while ($qryc=mysql_fetch_array($res)) 
			{ 
			?>            
				<a href="directions.php?id=<? echo $qryc["id"] ?>" data-role="button" data-theme="b"><?php echo $qryc["city"]; if ($qryc["state"]) {?>, <? echo $qryc["state"]; }?></a>
		<? 	}	?>   
		</div>
	</div>
<? 
} 
if ($data[settings][TellFriendButton] == "YES") 
{?>
      <div data-role="popup" id="popshare" data-theme="a" class="ui-corner-all">
		<div data-role="header" data-theme="b" class="ui-corner-top">
			<h1><?=$lngtellfriend ?></h1>
		</div>
            <?
		$domain = $_SERVER["SERVER_NAME"];
		$urlleft= str_split($domain, 4);
		if( $urlleft[0] == "http")
		{
			$domain;
		}
		else
		{
			$domain="http://".$domain;
		}
		if(substr_count($_SERVER['HTTP_HOST'],".")>1)
		{
			$_SESSION['domain'] = $_SERVER['HTTP_HOST'].$path."/";
		}
		else
		{
			$_SESSION['domain'] = "www.".$_SERVER['HTTP_HOST'].$path."/";
		}
		$_SESSION['app_name'] = $qry["name"];?>
			
		<div data-role="content" class="ui-corner-bottom ui-content">
			<a href="mailto:Who?subject=<?=sprintf($lngemail_subject_format, $qry["name"])?>&body=<?=str_replace("<br>","%0A",sprintf($lngemail_body_format, $qry["name"]))?><?=$domain.$path?>/?appcode=<?=$data[settings][appcode]?>" data-role="button" data-theme="b"><?=$lngshare_by_email?></a>
			<a href="http://html5authentication.com/login-twitter.php?domain=<?=urlencode($_SESSION['domain'])?>&appcode=<?=$appcode?>&share=On&comment=<?=sprintf("I've been using the %s mobile website and I think you should take a look!", $qry["name"]).$domain.$path."/?appcode=".$appcode?>" onclick="window.location = $(this).attr('href')" rel="external" data-role="button" data-theme="b"><?=$lngshare_on_twitter?></a>
            	<a href="http://html5authentication.com/login-facebook.php?domain=<?=urlencode($_SESSION['domain'])?>&appcode=<?=$appcode?>&share=On&comment=<?=sprintf("I've been using the %s mobile website and I think you should take a look!", $qry["name"]).$domain.$path."/?appcode=".$appcode?>" onclick="window.location = $(this).attr('href')" data-role="button" data-theme="b"><?=$lngshare_on_facebook?></a> 
		</div>
	</div><? 
}?>
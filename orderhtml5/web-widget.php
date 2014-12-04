<?
	//ini_set("session.use_trans_sid", true);
	//ob_start("ob_gzhandler");
	
	header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	
	include_once("dbconnect.inc");
	include_once("app.inc");
	include_once("memcache.inc");
//	log_info(__FILE__, 'Testing', 'TESTING');
	
	include_once("ray_model/common.php");
	include_once("ray_model/ordering.php");
	
	session_start("restwidg_id");	
	function getCurrentBaseURL() {
		global $_SERVER;
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		
		$server_name = $_SERVER["HTTP_X_HOST"];
		if($server_name == "") $server_name = $_SERVER["SERVER_NAME"];
		 
		if ($_SERVER["SERVER_PORT"] != "80") {
		    $pageURL .= $server_name.":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		    $pageURL .= $server_name.$_SERVER["REQUEST_URI"];
		}
		
		$url_patterns = explode("/", $pageURL);
		unset($url_patterns[count($url_patterns)-1]);
		$pageURL = implode("/", $url_patterns);
		
		return $pageURL;
	}
	function minstotime($minutes)
	{
		$hour = floor($minutes / 60); 
		$minute = $minutes % 60; 
		//return sprintf("%d:%02.0f", $hour, $minute); 
		return date("g:i a", strtotime("$hour:$minute:00"));
	}
	$data = make_data_safe($_REQUEST);
	
	if($data['restaurantid']) {
		$tabwidg_id_tk = $data['restaurantid'];
		unset($_SESSION['loc_id']);
	} else if($_SESSION['rest_id_tk']) {
		$tabwidg_id_tk = $_SESSION['rest_id_tk'];
	}
	
	if ($data[loc_id]!='' && isset($data['loc_id'])) {
		$_SESSION['loc_id'] = $data[loc_id];
	}
	else {
		$data[loc_id] = $_SESSION['loc_id'];
	}
	
	$isactive=$data['menu_id'];
	
	$action = htmlspecialchars($data['action'], ENT_QUOTES); 
	$_SESSION['rest_id']=$tabwidg_id;
	//echo 'token'.$tabwidg_id_tk.'session'.$_SESSION['rest_id'];
	//print_r($data);
	
	if ($tabwidg_id_tk)
	{
		$qryrest = get_restaurant_bykey($conn, $tabwidg_id_tk, "ref_tk");
		$_SESSION['rest_id'] = $qryrest[id];
		$_SESSION['rest_id_tk'] = $tabwidg_id_tk;
		$data["tab_id"] = $qryrest["tab_id"];
		$app_id = $qryrest["app_id"];
		
		$app_info = get_app_record($conn, $qryrest[app_id]);
		
		$resmenu = get_ordering_menus($conn, $qryrest[app_id], $qryrest[tab_id], "1,2");
		$resmenul = get_ordering_menus($conn, $qryrest[app_id], $qryrest[tab_id], "1,2");
		
		$_SESSION['rest_name'] = $qryrest['restaurant_name'];
		
		if($data['item_id'])
		{
			$qryitem = get_ordering_item($conn, $data['item_id']);
		}
		
		$BACKGROUND_IMAGE = getBackgroundImageValue($conn, $qryrest[app_id], $qryrest, 0, "../../");
		$datacontroller = get_app_tab_record($conn, $qryrest[tab_id]);
		if ($datacontroller[view_controller]=="MerchandiseViewController")
		{
			include_once('ray_model/merchandise_language_device_detect.php');
		}
		else if ($datacontroller[view_controller]=="OrderingViewController")
		{
			include_once('ray_model/language_device_detect.php');
		}
	}
	?>
	
<? if($action == "addtocart") {
	//-----------------------------------------------------------------------------
	//---------------- Add To Cart ------------------------------------------------
	//-----------------------------------------------------------------------------	
	
    $idArray = explode('&',$_SERVER["QUERY_STRING"]);
	foreach ($idArray as $id){
		if(substr($id,0,4) == "size") { $size = str_replace('size','id',$id);}
		
		if(substr($id,0,6) == "option") { 
			$idoption = explode('=',$id);
			foreach ($idoption as $option){
				if (is_numeric($option))
				{
					$optionid[]=$option;
					//print_r($optionid);
				}
			}
		
		}
	}
	
	$detail_array = array(); // will contain order information
	
	if ($size)
	{
		$sqlsize = "SELECT * FROM  `restaurant_item_size` where ".$size;
		$ressize = mysql_query($sqlsize, $conn);
		$qrysize = mysql_fetch_array($ressize);
		
		$total=$qrysize["price"];
	} else {
		$total=$qryitem["price"];
	} 
	
	$detail_array[] = array(
		"name" =>(($size)?$qryitem["item_name"]."($qrysize[size])":$qryitem["item_name"]),
		"cost" =>(($size)?$qrysize["price"]:$qryitem["price"]),
		"currency" =>$qryrest["currency_sign"],   
	);
					
	if ($optionid) {
		
		$sqloptions = "SELECT * FROM  `restaurant_item_options` where id IN (".implode(",", $optionid).") ORDER BY option_group";
		$resoptions = mysql_query($sqloptions, $conn);
		if(mysql_num_rows($resoptions) > 0) {
			$last_opt_group = "";
			while($qryoptions = mysql_fetch_array($resoptions)) { 
				
				$detail_array[] = array(
					"name" => $qryoptions["option_group"]." - ".$qryoptions["option_name"],
					"cost" =>$qryoptions["option_charges"],
					"currency" =>$qryrest["currency_sign"],
				);
			
				$total=$qryoptions["option_charges"]+$total;
				$option1=$qryoptions["option_name"]." | ".$option1;	
			}
		}
	}
		
	if($_SESSION['orderstr']) {
		$orderstr =  $_SESSION['orderstr'];
	} else {
		$RandomString=chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122));
		$_SESSION['orderstr']=$RandomString;
		$orderstr =  $_SESSION['orderstr'];
	}
	
	$sql = "INSERT INTO orders
			(order_str, 
			quantity,
			order_detail,
			app_id,
			tab_id,
			item_id,
			order_total, 
			order_note, 
			order_type,
			order_state,
			loc_id
			)
			VALUES
			('$orderstr', 
			'$data[quantity]',
			'".addslashes(serialize($detail_array))."',
			'$qryrest[app_id]',
			'$qryrest[tab_id]',
			'$data[item_id]',
			'$total', 
			'$data[instructions]',
			'2', 
			'3',
			'0'
			);";
	$res = mysql_query($sql, $conn);
	
	$_SESSION["action_addtocart"] = "1";
	
	?>
	<script language="javascript">
	document.location.href = "web-widget.php";
	</script>
	<?php
	exit;
}

if($data[delete]=='yes') {
	//-----------------------------------------------------------------------------
	//---------------- Remove from Cart -------------------------------------------
	//-----------------------------------------------------------------------------
	if(isset($_SESSION['orderstr'])) {
		$sqldel = "delete from orders where	id = '$data[id]' AND order_str='$_SESSION[orderstr]'";
		$resdel = mysql_query($sqldel, $conn);
	}
}

$_SESSION['appcode']=$app_info[code];
if (isset($qryrest["tab_id"]))
{
?>


<!DOCTYPE html>
<html class="ui-mobile-rendering"><head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?=$_SESSION['rest_name']?> - Order Now!</title>
	<link rel="stylesheet"  href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<link rel="stylesheet"  href="style_widget.php" />
    <link rel="stylesheet" href="css/jqm-docs.css" />
	<link rel="stylesheet" href="css/ray.css" />
    <link href="css/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />	
	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script src="js/jqm-docs.js"></script>
    <script src="js/jquery.alerts.js" type="text/javascript"></script>
    <script type='text/javascript' src="http://cdn.jquerytools.org/1.2.6/form/jquery.tools.min.js"></script>
	<script type="text/javascript">
	
	$(document).bind('mobileinit',function(){
	    $.extend($.mobile, {
	        loadingMessage: ''
	    })
	})
	</script>
    <script type="text/javascript">
      	function checkboxlimit(checkgroup, limit)
		{
			for (var i=0; i<checkgroup.length; i++)
			{
				checkgroup[i].onclick=function()
				{
					var checkedcount=0
					for (var i=0; i<checkgroup.length; i++)	checkedcount+=(checkgroup[i].checked)? 1 : 0
						if (checkedcount>limit)
						{
						jAlert('<?=$lngtoomany?>', 'Alert Dialog');
						this.checked=false
						}
				}
			}
		}
	</script> 
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	
	<style>
	#cart .ui-dialog .ui-header,#cart .ui-dialog .ui-content,#cart .ui-dialog .ui-footer{
	min-width:800px !important;
	}
	.ui-btn-right{ background:url(images/cart.png) 100% 100% !important; background-size:cover !important; width:30px; height:30px; -webkit-border-radius:none !important}
	
	#cart .ui-dialog-contain {
		max-width:100%;
		margin: 3% auto 15px auto;
	}
	#cart .ui-header .ui-btn-icon-notext {
		display: none;
	}
	
	.ui-content {
	border-width: 0;
	overflow-x: hidden;
	overflow-y: hidden;
	padding: 15px;
	}
	</style>
</head> 
<body> 

<div data-role="page" class="type-home" >
<?
	$locs = get_tab_location($conn, $data["tab_id"]);
	//echo $locs[0][id]."Count.".count($locs);
	if (count($locs) == 1 && $data[loc_id] == "" && !isset($data[loc_id]) ) {
		$_SESSION['loc_id'] = $locs[0][id];
		header("Location: web-widget.php?loc_id=".$locs[0][id]);
	}
	else if(count($locs) > 1 && $data[loc_id] =="" && !isset($data[loc_id])) {
		$loc_id = intval($locs[0]["id"]);	?>
        <script>
			$( '.type-home' ).click( function() {
				//alert('A page with an id of "aboutPage" was just created by jQuery Mobile!');
				$( "#popupDialog" ).popup( "open" );
			});
			
		</script>
        <?
	}
	else if ($data[loc_id] != "" && isset($data[loc_id]))
	{
		date_default_timezone_set('GMT');
		$locs = get_tab_location($conn, $data["loc_id"], 'id');
		$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
		//echo $jsonObject;
		$object = json_decode(file_get_contents($jsonObject));
		/*$ordertimesec = time() + ($locs[0]["timezone_value"] * 60 * 60)+$object->dstOffset;
		$orderhour = date("G",$ordertimesec);
		$ordermin = date("i",$ordertimesec);
		$ordersec = date("s",$ordertimesec);
		echo "TIME NOW IS $orderhour:$ordermin";*/
		$locs[0][timezone_value] = time() + ($locs[0][timezone_value] * 60 * 60);
		$timeinmins = (date("G",$locs[0][timezone_value]))*60+date("i",$locs[0][timezone_value])+$object->dstOffset/60;
		/*echo "time in mins ".date("G",$locs[0][timezone_value]).":".date("i",$locs[0][timezone_value])."    DST Offset: ".$object->dstOffset/60;*/
		$sql = "SELECT open_time, close_time, more_time FROM `restaurant_time` where restaurant_id=$_SESSION[rest_id] and day = '".date("l",$locs[0][timezone_value])."'";
		$res = mysql_query($sql, $conn);
		$rest_opentime =  mysql_result($res, 0, 0);
		$rest_closetime =  mysql_result($res, 0, 1);
		$rest_moretime =  json_decode(mysql_result($res, 0, 2));
		$_SESSION[restaurantopen] = false;
		//$_SESSION[restaurantopen] = true;
		//echo 'timeinmins'.$timeinmins.' Open Time '.$rest_opentime.' Close Time '.$rest_closetime;
			if ($timeinmins >= $rest_opentime && $timeinmins < $rest_closetime)
			{
				$_SESSION[restaurantopen] = true;
			}
			else
			{
			//"More Time ";
			//print_r($rest_moretime);
				foreach ($rest_moretime as $key => $val)
				{
					if ($timeinmins >= $val[0] && $timeinmins < $val[1])
					{
						$_SESSION[restaurantopen] = true;
					}
				}
			}
	}
?>
    <div data-role="popup" id="popupDialog" data-overlay-theme="a" data-theme="c" style="max-width:400px;" class="ui-corner-all">
			<div data-role="header" data-theme="b" class="ui-corner-top">
				<h1><?=$lnglocations?></h1>
			</div>
			<div data-role="content" style="background:#FFF; opacity:0.8;">
                    <ul data-role="listview" data-dividertheme="d">
                        <?  
                        $countrow=0;
                        foreach($locs AS $loc)
                        { 
                        $countrow++;?>
                        <li><a href="web-widget.php?loc_id=<?=$loc["id"]?>" rel="external"><?=$loc["city"]?></a></li>
                        <?
                        }
                        ?>
                    </ul>
            </div><!-- /content -->
	</div>
    <div data-role="header" data-theme="a" data-position="fixed">
        			<div class="ui-block-b" data-role="controlgroup" data-type="horizontal" style="margin-left: 10px;">
						<a href="#popupMenu" data-rel="popup" data-role="button"><?=$lnghome?></a>
                        <? if($_SESSION['orderstr'] && $_GET[action]!="successful" && $_SESSION[restaurantopen] == true)
						{ ?>
						<a href="#cart" style="width:100px;" data-transition="flip" data-role="button"><?=$lngcart?></a>
						<?
						}
						?>
                    </div>
		<h1><?=$_SESSION['rest_name']?></h1>
    </div>
    <div data-role="popup" id="popupMenu" data-theme="a">
                <div data-role="collapsible-set" data-theme="b" data-content-theme="c" data-collapsed-icon="arrow-r" data-expanded-icon="arrow-d" style="margin:0;">
                <?
                $locations = get_tab_location($conn, $data["tab_id"]);
				if (count($locations) > 1)
				{?>
                        <div data-role="collapsible" data-inset="false">
                            <h2><?=$lnglocations?></h2>
                            <ul data-role="listview">
                            <?  foreach($locations AS $loc)
								{ ?>
                            	<li><a href="web-widget.php?loc_id=<?=$loc["id"]?>" rel="external"><?=$loc["city"]?></a></li>
                                <?
								}?>
                            </ul>
                        </div><!-- /collapsible -->
				<?
				}
				?>
                </div>
	</div>
    <div class="ui-bar ui-bar-e" id="addedtocart" align="center" style="display:none;">
	<h3 style="margin-top:8px;"></h3>
	<div style="float:right; margin-top:4px;"><a href="#" id="closenotice" data-role="button" data-icon="delete" data-iconpos="notext">Button</a></div>
	</div>
    <?
	//print_r($locs);
	if ($_SESSION[restaurantopen] == true || !isset($_SESSION[restaurantopen]))
	{
	?>
	<div data-role="content" style="<?php if($BACKGROUND_IMAGE) echo "background:url(" . $BACKGROUND_IMAGE . ");"; ?>">		
		<div class="content-secondary">
			<div id="jqm-homeheader">
				<? if ($qryrest["logo_url"]) {?><h1 id="jqm-logo"><img src="<?=$qryrest["logo_url"]?>" style="width:150px"  alt="<?=$_SESSION['rest_name'];?>" /></h1><? }?>
				<p><?=$qryrest['brief_desc']?></p>
			</div>
            <p></p>		
			<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="a">
				<li data-role="list-divider"><?=$lngcategories?></li>
          </ul><br>
            <ul data-role="listview" data-inset="true" data-theme="c" >
				<?  foreach($resmenu AS $qrymenu) {
						if($qrymenu[item_count] > 0) { 
						
					if(!isset($isactive)){ $isactive=$qrymenu["id"];}?>
                <li>
                	<a href="web-widget.php?menu_id=<?=$qrymenu["id"]?>"><?=$qrymenu["label"]?>   (<?php echo $qrymenu["item_count"]?>)</a>
                </li>
				<? }} ?>
			</ul>
			
		</div><!--/content-secondary-->	
		<div class="content-primary">
			<nav>
                <? 	$sqlitem = "SELECT i.*, r.currency, cc.currency_sign FROM restaurant_item AS i LEFT JOIN restaurant_menu AS m ON m.id = i.menu_id LEFT JOIN restaurant_item_options AS o ON i.id = o.item_id LEFT JOIN restaurant_item_size AS s ON i.id = s.item_id LEFT JOIN restaurant AS r ON m.app_id = r.app_id AND m.tab_id = r.tab_id LEFT JOIN currency AS cc ON r.currency = cc.currency_code WHERE i.menu_id = '$isactive'  AND i.is_available IN (1,2) GROUP BY i.id ORDER BY i.seq DESC";
					//echo $sqlitem;
					$resitem = mysql_query($sqlitem, $conn);?>
                    <ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="a">
					<li data-role="list-divider"><?=$lngweoffer?></li>
                    </ul>
					<?  if (mysql_num_rows($resitem) !=0) { ?>
					<? while ($qryitem = mysql_fetch_array($resitem)) { 
					// ITEM OPEN TIME CHECK
					$sql = "SELECT open_time, close_time, more_time FROM `restaurant_time` where item_id=$qryitem[id] and day = '".date("l",$locs[0][timezone_value])."'";
					log_info(__FILE__, 'Restaurant time query for item id and day: ' . $sql , 'TESTING');
					$res = mysql_query($sql, $conn);
					//echo 'SQL Query'.$sql;
					$item_opentime =  mysql_result($res, 0, 0);
					$item_closetime =  mysql_result($res, 0, 1);
					$item_moretime =  json_decode(mysql_result($res, 0, 2));
					$_SESSION["itemopen".$qryitem[id]] = false;
						if ($qryitem[is_available]=="1")
						{
							$_SESSION["itemopen".$qryitem[id]] = true;
						}
						else if ($qryitem[is_available]=="0")
						{
							$_SESSION["itemopen".$qryitem[id]] = false;
						}
						else if ($timeinmins >= $item_opentime && $timeinmins < $item_closetime)
						{
							$_SESSION["itemopen".$qryitem[id]] = true;
						}
						else
						{
						//"More Time ";
						//print_r($item_closetime);
							foreach ($item_moretime as $key => $val)
							{
								if ($timeinmins >= $val[0] && $timeinmins < $val[1])
								{
									$_SESSION["itemopen".$qryitem[id]] = true;
								}
							}
						}
						//echo "ITEM OPEN".$_SESSION["itemopen".$qryitem[id]]."IS Available ".$qryitem[is_available];
					// ITEM OPEN TIME CHECK END
					?>
                    <form action="web-widget.php" method="get" data-ajax="false" id="itemform<?=$qryitem[id]?>"><div data-role="collapsible">
                        <h3><?=$qryitem["item_name"]?></h3>
                     <?
					if ($_SESSION["itemopen".$qryitem[id]] == true)
					{?>
                        <p>
                            <input type="hidden" name="item_id" value="<?=$qryitem["id"]?>">
                            <fieldset class="ui-grid-a">
                                <div class="ui-block-a"><div align="center">
                                	<?php
							            $uid = uniqid();
							            $img_url = "";
										if(preg_match("/^http:\/\/(.*)$/", $qryitem["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qryitem["image_url"])) {
											$img_url = '<img src="/images_online.php?name='.urlencode($qryitem["image_url"]).'&height=100&'.$uid.'" alt="Image" />';	
										} else {
											$dir = findUploadDirectory($app_id) . "/ordering/".$qryitem["image_url"];
											//echo $dir;
											if(file_exists($dir) && !is_dir($dir)) {
												$img_url = '<img src="/custom_images/'.$app_info[code].'/ordering/'.$qryitem["image_url"].'?height=100&'.$uid.'" />';
											}
										}
										echo $img_url;
							            ?>
                                </div></div>
                                <div class="ui-block-b"><? if ($qryitem["description"]) {?>
                            <strong><?=$lngdetail?>: </strong><?=$qryitem["description"]?>
                            <? } ?>
                            <?	$sqlsize = "SELECT * FROM  `restaurant_item_size` where item_id=".$qryitem["id"];
                                //echo $sqlsize;
                                $ressize = mysql_query($sqlsize, $conn);
                                if (mysql_num_rows($ressize) !=0) 
                                { ?>            
                                <div data-role="fieldcontain" data-mini="true">
                                <p class='error sizeerror<?=$qryitem[id]?>'><?=$lngchoosesize?></p>
                                    <select name="size" id="size<?=$qryitem[id]?>" data-native-menu="false" data-mini="true">
                                    <option><?=$lngchoosesize?></option>
                                    <? while ($qrysize = mysql_fetch_array($ressize)) 
                                        {?>
                                        <option value="<?=$qrysize["id"]?>"><?=$qrysize["size"]?> (<?=$qryrest["currency_sign"]." ".number_format($qrysize["price"],2)?>)</option><?	} ?>
                                    </select>    
                                </div>
                                <? } else {?>
                                 <br><strong><?=$lngprice?>: </strong><?=$qryrest["currency_sign"]." ".number_format($qryitem["price"],2)?><br>
                                <? }?></div>	   
                            </fieldset>
                            <div class="ui-field-contain" align="center">
                                <label for="quantity" class="select"><?=$lngquantity?>:</label>
                                <select name="quantity" id="quantity<?=$qryitem[id]?>">
                                    <? 	$quantity = 1;
                                        while ($quantity < 16)
                                        {
                                            echo "<option value='$quantity'>$quantity</option>";
                                            $quantity++;
                                        }
                                    ?>
                                </select>
                            </div>
                            <br>
                            <?php 
            $sqloptgrp = "SELECT option_group,min_selection,max_selection FROM  `restaurant_item_options` where item_id=".$qryitem["id"]." Group by option_group order by seq desc";
			$resoptgrp = mysql_query($sqloptgrp, $conn);
			if (mysql_num_rows($resoptgrp) != 0) { ?>
				<div data-role="fieldcontain">
				<? while ($qryoptgrp = mysql_fetch_array($resoptgrp)) {
								echo "<p class='error minselecterror$qryitem[id]'>".sprintf ($lngminselect, $qryoptgrp["min_selection"]);
								if($qryoptgrp["option_group"]) { echo " ".$lngfor." ".$qryoptgrp["option_group"]; }
								echo "</p>"; ?>
					<fieldset data-role="controlgroup">
						<legend><?=$lngchooseoption?> <?=$qryoptgrp["option_group"]?></legend>
						<?php 
							$sqlopt = "SELECT * FROM `restaurant_item_options` where item_id=".$qryitem["id"]." and option_group='".$qryoptgrp["option_group"]."' order by seq desc"; //echo $sqlopt;
							$resopt = mysql_query($sqlopt, $conn);
							$i = 0;
							while ($qryopt = mysql_fetch_array($resopt)) { $i++;
								$qryopt["option_name"] = urlencode($qryopt["option_name"]);
								$qryopt["option_charges"]=number_format($qryopt["option_charges"],2);
								if (intval($qryopt["max_selection"]) == 0) {$qryoptgrp["max_selection"] = mysql_num_rows($resopt);}
								$regex = '#[^a-z0-9_]#i'; $optiongrp = preg_replace($regex, '', $qryoptgrp["option_group"].$qryitem["id"]);
							?>
								<input <? if($qryopt["max_selection"]==1){?>type="checkbox"<? } else {?> type="checkbox" <? }?>name="option<?=$optiongrp?>[]" id="<?=$qryopt["option_name"]?>" value="<?=$qryopt["id"]?>" />
								<label for="<?=$qryopt["option_name"]?>"><?=urldecode($qryopt["option_name"])?> (<? if($qryopt["option_charges"]){?><?=$qryrest["currency_sign"]." ".$qryopt["option_charges"]?><? } else { echo $lngnoextra; }?>)</label>
						 <?	} ?>
                         		<script type="text/javascript">
									checkboxlimit($('[name^=option<?=$optiongrp;?>]'), <?=$qryoptgrp["max_selection"]?>)
								</script>
					</fieldset>
					<? 		$checkbox[] = "$('[name^=option".$optiongrp."]:checked').length < ".$qryoptgrp["min_selection"];
							//print_r($checkbox);
							$checkboximplode = implode(" || ",$checkbox);
							//echo "checkboximplode ".$checkboximplode;
				 }?>	
                </div>
			<? 
			unset($checkbox);
			}?>
            				<script>
							$("#itemform<?=$qryitem[id]?>").submit(
								function() {
									<? if($checkboximplode) {?>
									  if(
										<?=$checkboximplode;?>
										) 
										{
											$(".minselecterror<?=$qryitem[id];?>").fadeIn('slow')
											return false;
										}
										<? }?>
									  if(
										$('#size<?=$qryitem[id]?> :selected').text() == '<?=$lngchoosesize?>'
										)
										{
											$(".sizeerror<?=$qryitem[id]?>").fadeIn('slow')
											return false;
										}
									return true;
								});
							</script>
                                <div data-role="collapsible" data-theme="d" data-content-theme="d" align="center" style="width:85% !important; margin-left:50px !important">
   <h3><?=$lngspecinstruct?></h3>
   <p><fieldset data-role="controlgroup">
                                    <legend></legend>
                                    <textarea cols="40" rows="8" name="instructions" id="instructions" data-mini="true"></textarea>
                                 </fieldset></p>
</div>
                     
                                <div>
                                <fieldset>
                                        <div><button type="submit"  id="action" name="action" value="addtocart" data-theme="c" data-mini="true"><?=$lngadditem?></button></div>
                                </fieldset>
                                </div>
                                </p>
                                <? 
		}
		else
		{?>
		<style>
			table { width:100%; }
			table caption { text-align:left;  }
			table thead th { text-align:left; border-bottom-width:1px; border-top-width:1px; }
			table th, td { text-align:left; padding:6px;} 
		</style>
        <table summary="Item not available.">
		  <caption>Item is not available at this moment</caption>
		  <thead>
		    <tr>
		      <th scope="col"><?=$lngdays?></th>  
		      <th scope="col"><?=$lngfrom?></th>  
		      <th scope="col"><?=$lngto?></th>  
		    </tr>
		  </thead>
		  <tfoot>
		    <tr>
		      <td colspan="5"><? 
			  if($qryrest[brief_desc])
			  { 
			  echo "<strong>$lngdetail: </strong>".nl2br($qryitem["description"])."<br><br>";
			  }
			  if($qryrest[cuisine_type])
			  {
			  echo "<strong>$lngcuisine: </strong>".$qryrest[cuisine_type]."<br><br>";
			  }
			  ?>
              </td>
		    </tr>
		  </tfoot>
		  <tbody>
		  <?
		  $items_open_times = get_item_serve_time($conn, $qryitem[id], $qryrest[id]);
			foreach ($items_open_times as $itemtime)
			{
				  $otime=minstotime($itemtime[open_time]);
				  $ctime=minstotime($itemtime[close_time]);
				  if($otime==$ctime)
				  {
					  $otime = "-";
					  $ctime = "-";
				  }
				  switch ($itemtime[day]) {
					case "Monday":
						$itemtime[day]=$lngmon;
						break;
					case "Tuesday":
						$itemtime[day]=$lngtue;
						break;
					case "Wednesday":
						$itemtime[day]=$lngwed;
						break;
					case "Thursday":
						$itemtime[day]=$lngthu;
						break;
					case "Friday":
						$itemtime[day]=$lngfri;
						break;
					case "Saturday":
						$itemtime[day]=$lngsat;
						break;
					case "Sunday":
						$itemtime[day]=$lngsun;
						break;
				  }
			  ?>
			  <tr>
				<th scope="row"><?=$itemtime[day]?></th>
				<td><?=$otime;?></td>
				<td><?=$ctime;?></td>
			  </tr>
		<?	$item_moretimeday = json_decode($itemtime[more_time]);
			foreach ($item_moretimeday as $key => $val)
			{ 
				  $otime=minstotime($val[0]);
				  $ctime=minstotime($val[1]);?>
			  <tr>
				<th scope="row"></th>
				<td><?=$otime;?></td>
				<td><?=$ctime;?></td>
			  </tr>
			<?
			}
			} ?>
          
		  </tbody>
		</table>
      	<?
		}?>
                    </div></form>
				<?
		} ?>  <? }else echo "We are setting up the tables. Please get back soon!"?>
			</nav>
		</div>
	</div>
    <?
		}
		else
		{
			include_once("time_widget.php");
		}
	?>
	<div data-role="footer" class="footer-docs" data-theme="c">
			<p>Copyright &copy; <?=date('Y')." ".$_SESSION['rest_name'];?>, all rights reserved.</p>
	</div>	
	
</div>


<?php
if($_REQUEST[action]=='checkout') {
	//-----------------------------------------------------------------------------
	//---------------- Check Out --------------------------------------------------
	//-----------------------------------------------------------------------------
	
	if(isset($_SESSION['orderstr'])) {
		$_SESSION['totalcharge'] = $_REQUEST['total'];
		$_SESSION['totaltax'] = $_REQUEST['totaltax'];
		
		// ---------------------------------------------------------------
		// Create new record with order_type = 0, for tax details...
		// ---------------------------------------------------------------
		
		$tax_detail_id = "";
		$sql = "SELECT id FROM orders WHERE order_type = '0' AND order_str = '".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"];
		$res_sql = mysql_query($sql, $conn);
		if(mysql_num_rows($res_sql) > 0) {
			$tax_detail_id = mysql_result($res_sql, 0, 0);
		}
		$tax_details_post = array();
		$tax_details_post = unserialize(base64_decode($data[order_tax_details]));
		//echo 'TEST ZAFAR'.base64_decode($data[order_tax_details]);
		if($data[convenience_fee])
		{
								$tax_details_post[] = array(
									"name" => "Convenience Fee",
									"cost" => $data[convenience_fee],
									"currency" => $qryrest["currency_sign"],
								);
		}?>
        <script>
		console.log('delivery_fee <?=$data[delivery_fee]?> deliver yes/no? <?=$_REQUEST[deliver].' '.$data[deliver].' '.$_REQUEST[deliver]?>');
		</script>
        <?
		if($data[delivery_fee] && $_REQUEST[deliver] == "1")
		{
								$tax_details_post[] = array(
									"name" => "Delivery Fee",
									"cost" => $data[delivery_fee],
									"currency" => $qryrest["currency_sign"],
								);
		}
		//base64_encode(addslashes(serialize($tax_details_post)));
		$set_v = " SET 
			order_str='$_SESSION[orderstr]', 
			tab_id='$data[tab_id]', 
			order_detail='".serialize($tax_details_post)."',
			app_id='$app_id',
			item_id='0', order_total='0', order_note='', order_state='3', loc_id='0', order_type='0'
			";
		if(intval($tax_detail_id) > 0) {
			$sql = "UPDATE orders ".$set_v." WHERE id='$tax_detail_id'";
		} else {
			$sql = "INSERT INTO orders ".$set_v;
		}
		$res_sql = mysql_query($sql, $conn);
		
		// ---------------------------------------------------------------
		// Delivery Address ...
		// ---------------------------------------------------------------
		if($data[deliver] == "1") {
			$da_sql = " SET 
				user_id = 0, 
				first_name	= '$data[fname]', 
				last_name = '$data[lname]',
				address1 = '$data[addr1]',
				address2 = '$data[addr2]', 
				country = '',
				city = '$data[city]', 
				zipcode = '$data[zipcode]', 
				state = '$data[state]', 
				company = '', 
				fax = '', 
				type = '1', 
				phone = '$data[pnum]',
				email = '$data[email]' ";	
		} else {
			$da_sql = " SET 
				user_id = 0, 
				first_name	= '$data[fname]', 
				last_name = '$data[lname]',
				address1 = '',
				address2 = '', 
				country = '',
				city = '', 
				zipcode = '', 
				state = '', 
				company = '', 
				fax = '', 
				type = '1', 
				phone = '$data[pnum]',
				email = '$data[email]' ";
		}
		$_SESSION['orderemail'] = $data[email];
		$sql = "SELECT delivery_address_id FROM orders WHERE order_type > 0 AND order_str = '".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"];
		
		$res_sql = mysql_query($sql, $conn);
		if(mysql_num_rows($res_sql) > 0) {
			$da_detail_id = mysql_result($res_sql, 0, 0);
		}
		if(intval($da_detail_id) > 0) {
			$da_sql = "UPDATE app_users_address ".$da_sql." WHERE id='$da_detail_id'";
			$res_sql = mysql_query($da_sql, $conn);
		} else {
			$da_sql = "INSERT INTO app_users_address ".$da_sql;
			$res_sql = mysql_query($da_sql, $conn);
			$da_detail_id = mysql_insert_id($conn);
		}
		
		// ---------------------------------------------------------------
		// Update orders type and location....
		// ---------------------------------------------------------------
		$sqlupdate = 	"UPDATE orders 
						SET 
						order_type = '$_REQUEST[deliver]',
						loc_id= '$_REQUEST[location]',
						delivery_address_id = '$da_detail_id',
						placed_on = '".date("Y-m-d H:i:s")."'			
						WHERE
						order_type > 0 AND order_str = '".$_SESSION['orderstr']."' and tab_id=".$qryrest[tab_id];
		
		$resupdate = mysql_query($sqlupdate, $conn);
						$_SESSION['family_name'] = $_REQUEST[lname];
						$_SESSION['first_name'] = $_REQUEST[fname]; 
						$_SESSION['phone_number'] = $_REQUEST[pnum];
						$_SESSION['street1'] = $_REQUEST[addr1];
						$_SESSION['street2'] = $_REQUEST[addr2];
						$_SESSION['city'] = $_REQUEST[city];
						$_SESSION['state'] = $_REQUEST[state]; 
						$_SESSION['postal_code'] = $_REQUEST[zipcode];
						$_SESSION['email'] = $_REQUEST[email];
		//header("Location: paypal/SetExpressCheckout.php");
		if($data["payment"] == '1')
		{?>
			<script type="text/javascript">
			parent.location = "paypal/SetExpressCheckout.php?fww=on&fwwtk=<?=$qryrest[tk]; ?>&orderstr=<?=$_SESSION['orderstr']; ?>&app_code=<?=$app_info[code];?>&tab_id=<?=$qryrest[tab_id];?>";
			</script>
		<?	exit;
			//echo "checkout";
		}
		else if($data["payment"] == '4')
		{
			$locs = get_tab_location($conn, $data["loc_id"], 'id');
			//print_r($locs);
			date_default_timezone_set('GMT');
			$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
			$object = json_decode(file_get_contents($jsonObject));
			$ordertimesec = time() + ($locs[0]["timezone_value"] * 60 * 60)+$object->dstOffset;
			$orderhour = date("G",$ordertimesec);
			$ordermin = date("i",$ordertimesec);
			$ordersec = date("s",$ordertimesec);
			//echo "TIME NOW IS $orderhour:$ordermin ".date('Y-m-d H:i:s',$ordertimesec);
			$dateplaced = date_create();
			//echo date('Y-m-d H:i:s',$ordertimesec);
			$sqlorder = 	"UPDATE orders 
							SET
							order_state = '0',
							checkout_method = '4',
							loc_id = '$data[loc_id]',
							placed_on =  '".date('Y-m-d H:i:s',$ordertimesec)."'					
							WHERE order_str = '".$_SESSION['orderstr']."' and tab_id=".$data["tab_id"];
							$resorder = mysql_query($sqlorder, $conn);
							include_once("ordering_base_params.php");?>
			<script type="text/javascript">
			window.location = "web-widget.php?action=successful&<?=$PASS_PARAMS?>";
			</script>
			<? exit;
		}
		else if($data["payment"] == '2')
		{ ?>
			<script type="text/javascript">
			parent.location = "google/googlecheckout.php?fww=on&<?=$PASS_PARAMS?>";
			</script>
		<? exit;
		}
		
	} else {
			unset($_SESSION['orderstr']); 
			unset($_SESSION['totalcharge']); 
			header("Location: web-widget.php");
			exit;
	}
}
?>

<?php
if($action == "successful") {
	//-----------------------------------------------------------------------------
	//---------------- Check Out Done ---------------------------------------------
	//-----------------------------------------------------------------------------
	
	if($_SESSION['orderstr'] || $data['orderstr']) {
		if($_SESSION['orderstr'] == "") $_SESSION['orderstr'] = $data['orderstr'];
		//$data['orderstr'] = $_SESSION['orderstr'];
		//$data[tab_id] = $_SESSION[tab_id];
		$sql = "SELECT  * FROM  `orders` WHERE order_str = '".$_SESSION['orderstr']."' and order_state = '0' and paid = '1' LIMIT 0,1";
		$res = mysql_query($sql, $conn);
		$qry = mysql_fetch_array($res);
		
		include_once("order_checkoutdone_email.php");
		include_once("order_print.php");
		include_once("ordering_base_init.php");
		
		$_SESSION["action_addtocart"] = "3";
	}
	
} else if($action == "checkoutfailure") {
	//-----------------------------------------------------------------------------
	//---------------- Check Out Fail ---------------------------------------------
	//-----------------------------------------------------------------------------
	
	if($_SESSION['orderstr'])
	{
		$sql = "UPDATE orders SET order_state = '5' WHERE order_str = '".$_SESSION['orderstr']."' AND tab_id='$data[tab_id]'";
		$res = mysql_query($sql, $conn);
		
		$_SESSION["action_addtocart"] = "4";

	}
}
?>


<?php
/**********************************************************************
 * Message Process Part
***********************************************************************/
if($_SESSION["action_addtocart"] == "1") {
	$_SESSION["action_addtocart"] = "";
?>
<script type="text/javascript">
$(document).ready(function(){
	$('#addedtocart h3').html("<?=$lngorderadd?>"); 
	$('#addedtocart').fadeIn(2000).delay(2000).slideUp(2000);
}); 
</script>
<? 
} else if($_SESSION["action_addtocart"] == "3") {
	$_SESSION["action_addtocart"] = "";
?>
<script type="text/javascript">
$(document).ready(function(){
	$('#addedtocart h3').html("<?=$lngordersuccess?>"); 
	$('#addedtocart').fadeIn(2000).delay(2000).slideUp(2000);
}); 
</script>
<? 
} else if($_SESSION["action_addtocart"] == "4") {
	$_SESSION["action_addtocart"] = "";
?>
<script type="text/javascript">
$(document).ready(function(){
	$('#addedtocart h3').html("<?=$lngorderfail?>"); 
	$('#addedtocart').fadeIn(2000).delay(2000).slideUp(2000);
}); 
</script>
<? 
}
/**********************************************************************/
?>


<?php	
if($_SESSION['orderstr'])
	 {
		 $sqlorders = "SELECT * FROM `orders` WHERE order_type > 0 AND order_str='".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"]." GROUP BY order_detail";
		$resorders = mysql_query($sqlorders, $conn);
	?>
     <div data-role="dialog" id="cart" data-theme="b">
		<style>
	#cart .ui-btn-left{ background:url(img/addcart.png) 100% 100%; background-size:cover !important; width:30px; height:30px; -webkit-border-radius:none !important}
	.ui-btn-inner { padding: .5em 11px;}
	.unitprice,.currsign { font-size:9px; font-style:italic;}
	</style>
	<div data-role="header" data-theme="a" style="opacity:0.8;">
    				<div class="ui-block-b header-grid" data-role="controlgroup" data-type="horizontal" style="margin-left: 10px;">
						<a href="web-widget.php"  style="width:100px;" rel="external" data-role="button"><?=$lnghome?></a>
						<a href="#" style="width:100px;" data-role="button"><?=$lngcart?></a>
					</div>
		<h1><? echo $qryrest['restaurant_name']?></h1>
    </div><!-- /header -->
	<div data-role="content" style="background:#FFF; opacity:0.8;">
        	<ul data-role="listview">
				<li data-role="list-divider" style="text-align:center"><?=$lngcart?></li>
           	</ul><br>
            <p>
            <?php if (mysql_num_rows($resorders) !=0) { ?>
        	<div class="ui-grid-c">
                <div class="ui-block-a" style="width:55%;"><strong><?=$lngitem?></strong></div>
                <div class="ui-block-b" style="width:15%; text-align:center;"><strong><?=$lngprice." ($qryrest[currency_sign])"?></strong></div>
                <div class="ui-block-c" style="width:10%; text-align:center;"><strong><?=$lngquantity?></strong></div>
                <div class="ui-block-d" style="width:20%; text-align:center;"><strong><?=$lngaction?></strong></div>

                <script type="text/javascript">
								$(".button").bind('change blur',
								function() {
									//console.log("I am changed");
									var totalamount =0;
									var button = $(this);
									var oldValue = button.find("#select-choice-mini").val();
									var orderno = button.parent().find(".orderno").text();
									var uprice = button.parent().parent().find(".unitprice").text().replace(/[^\d.]/g, '');
									//var oldtotalamount = $("#totalamount").text().replace(/[^\d.]/g, '');
									var newVal = button.find("#select-choice-mini").val();
									if(newVal > 0)
									{
									$.ajax({
										  type: "POST",
										  url: "quantitychk.php",
										  data: "quantity="+newVal+"&orderno="+orderno+"&uprice="+uprice,
										  success: function(html)
										  		{
												newVal = $(html).find("span#quantitychk").text();
												}
											})
									orderprice  = (parseFloat(uprice)*newVal).toFixed(2);
									button.parent().parent().find(".ui-block-b").text(orderprice);
									var totaltaxrate = 0;
											$(".cart-main").find(".ui-block-b").each(function () {
												var orderamount = parseFloat($(this).text().replace(/[^\d.]/g, ''));
												totalamount = totalamount + orderamount;
												console.log("totalamount 1 > " + totalamount);
											});
											$(".taxamount0").each(function () {
												var taxrate = parseFloat($(this).parent().find(".taxrate").text());
												totaltaxrate =  taxrate + totaltaxrate;
												$(this).text((totalamount*taxrate).toFixed(2));
												console.log("totalamount 2 > " + totalamount + "tax rate " + taxrate + " totaltaxrate >"+totaltaxrate);
											});
											$("input#totaltax").val(totalamount*totaltaxrate);
											var newtotaltax = totalamount*totaltaxrate;
											totalamount = totalamount+newtotaltax;
											console.log("newtotaltax 1> " + newtotaltax + " totalamount 1> " + totalamount);
											var fixtaxchange = 0;
											$(".taxamount1").each(function () {
												var fixtax = parseFloat($(this).text().replace(/[^\d.]/g, ''));
												console.log("fixtax > " + fixtax)
												fixtaxchange = fixtaxchange + fixtax;
												totalamount = totalamount+fixtaxchange;
											});
											console.log("fixtaxchange 2> " + fixtaxchange + " totalamount2 > " + totalamount);
											console.log("totalamount > " + totalamount);
									totalamount = totalamount.toFixed(2);	
									$("#totalamount strong").text(totalamount);
									$("input#total").val(totalamount);
									}
									if($("input[name$='deliver']:checked").val()=='1') 
									{
										console.log("CHECK");
										var free_delivery_amount = parseFloat(<?=$qryrest["free_delivery_amount"]?>);
										if(totalamount > free_delivery_amount)
										{
											deliveryfee = 0;
										}
										else
										{
											var deliveryfee = parseFloat(<?=$qryrest["delivery_fee"]?>);
										}
										$("#deliveryfee").html(deliveryfee.toFixed(2));
										totalamount = deliveryfee + totalamount;
									}
									else
									{
										var deliveryfee = 0;
										$("#deliveryfee").html(deliveryfee.toFixed(2));
									}
									totalamount = totalamount.toFixed(2);
									$("#totalamount strong").text(totalamount);
									$("input#total").val(totalamount);
									
								});
								
								var countnum= <?=mysql_num_rows($resorders)?>;
								$(".delbutton").click(function() {
									var button = $(this);
									totalitemprice=parseFloat(button.parent().parent().find(".ui-block-b").text()).toFixed(2);
									var orderno = button.parent().find(".orderno").text();
									$.ajax({
										  type: "POST",
										  url: "deletecartwidget.php",
										  data: "orderno="+orderno,
										  success: function(html)
										  		{
												newVal = $(html);
												}
											})
									button.parent().parent().slideUp(2000);
									var totalamount = parseFloat($("#totalamount").text().replace(/[^\d.]/g, ''));
									totalamount=totalamount-totalitemprice;
									var deliveryfee = parseFloat(<?=$qryrest["delivery_fee"]?>);
									var free_delivery_amount = parseFloat(<?=$qryrest["free_delivery_amount"]?>);
										if(totalamount > free_delivery_amount)
										{
											$deliveryfee = 0;
										}
									$("#deliveryfee").html(deliveryfee.toFixed(2));
									totalamount = totalamount.toFixed(2);
									$("#totalamount strong").text(totalamount);
									$("input#total").val(totalamount);
									countnum--;
									if (countnum < 1)
									{
										//$(".ui-content").slideUp(2000);
										$(".ui-content").html("<br><p style='margin: 0px; text-align: center;'><?=$lngnoorder?></p><br><br><a href='./web-widget.php' rel='external'><div align='center'><img src='images/addcart.png' /></div></a>");
									}
								});
					 	</script>
                <?php
					while($qryorders = mysql_fetch_array($resorders))
					{
						$sqlitem = "SELECT * FROM  `restaurant_item` where id=".$qryorders["item_id"];
						$resitem = mysql_query($sqlitem, $conn);
						$qryitem = mysql_fetch_array($resitem); 
						?>
						<div class="cart-line" style="width:100%">
							<div class="cart-main">
								<div class="ui-block-a" style="width:55%;"><?=$qryitem["item_name"]; ?><br><span class="unitprice">(<?=$lngunit_price?> <span class="currsign"><?=$qryrest["currency_sign"]?></span><?=number_format($qryorders["order_total"],2)?>)</span></div>
								<div class="ui-block-b" style="width:15%; text-align:center"><?=number_format($qryorders["order_total"]*$qryorders["quantity"],2); ?></div>
                                <div class="ui-block-c" style="width:10%; text-align:center">
                                	<div class="orderno" style="display:none"><?=$qryorders["id"]?></div>
                                	<div class="quantity button"><select name="select-choice-mini" id="select-choice-mini" data-theme="c" data-mini="true" data-inline="true">
                                    <? 
									$count=0; while($count <15)
									{
										$count++?>
                                        <option value="<?=$count?>" <? if($count==$qryorders["quantity"]) {?> selected<? }?>><?=$count?></option>
								<? 	}?>                                        
</select></div>	
                                </div>
								<div class="ui-block-d delbutton" style="width:20%; text-align:center"><img src="images/cart_rem.png" width="25" /></div>
							</div>
							<?php
							$ord_detail = unserialize($qryorders[order_detail]);
							if(is_array($ord_detail)) { ?>
							<div class="cart-detail">
							<?php	foreach($ord_detail AS $od) { ?>
									<div class="ui-block-a" style="width:55%;"><?=htmlspecialchars_decode($od["name"]); ?></div>
									<div class="ui-block-b" style="width:15%; text-align:center"><?=number_format($od["cost"],2); ?></div>
									<div class="ui-block-c" style="width:10%; text-align:center"></div>
                                    <div class="ui-block-d" style="width:20%; text-align:center"></div>
									<div class="clear"></div>
							<?php } ?>
							</div>							
							<?php } ?>
							<?php
							
							$totalorder =	round(($qryorders["order_total"]*$qryorders["quantity"]),2)+$totalorder;
						
							if($qryitem["tax_exempted"]=='1') {$qryorders["order_total"]=0;}
							$totalorderfortax =  round(($qryorders["order_total"]*$qryorders["quantity"]),2) + $totalorderfortax;
							
							?>
						</div>
						
					<?php } ?>
						
					<?php
					$tax_details = array();					
					?>	
              <div class="ui-block-a" style="width:55%; height:30px"><?=$lngconvfee?></div>
			  <div class="ui-block-b taxamount1" style="width:15%; height:30px; text-align:center"><?=number_format($qryrest["convenience_fee"],2)?></div>
       		  <div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
              <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
                <?php		
                		$totaltax1 = 0;
                		
                		$sqltax = "SELECT * FROM  `tax` where tab_id=$data[tab_id]";
						$restax = mysql_query($sqltax, $conn);
						while ($qrytax = mysql_fetch_array($restax)) { 
							if($qrytax["tax_type"]=='1'){
								$tax=$qrytax["flat_amount"];
							}
							else
							{
								$tax=$qrytax["tax_rate"]*$totalorderfortax/100;
								$tax=round($tax,2);
							}
							if($tax>0) {
								
								$tax_details[] = array(
									"name" => $qrytax["tax_name"],
									"cost" => $tax,
									"currency" => $qryrest["currency_sign"],
								);
								
							?> 
		                        <div class="ui-block-a" style="width:55%; height:30px"><?=$qrytax["tax_name"]; ?></div>        
                                <span class="taxrate" style="display:none"><?=$qrytax["tax_rate"]/100?></span>                
								<div class="ui-block-b taxamount<?=$qrytax[tax_type]?>" style="width:15%; height:30px; text-align:center"><?=number_format($tax,2) ?></div>
		                		<div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
                                <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
	                        <? } 
	                        $totaltax1 = $tax + $totaltax1;
						}?>
						
                        <? if ($qryrest[is_delivery]==1 && $qryrest[takeout]==0 && $qryrest[dinein]==0) {?>
                        <script>
										var totalorder = parseFloat($("#totalamount strong").text());
										var free_delivery_amount = parseFloat(<?=$qryrest["free_delivery_amount"]?>);
										var deliveryfree = parseFloat(<?=$qryrest["delivery_fee"]?>);
										var totalamount;
										if(totalorder > free_delivery_amount)
										{
											deliveryfree = 0;
											$("#commentabove").text('(<?=$lngfreeabove." ".$qryrest["currency_sign"]." ".number_format($qryrest["free_delivery_amount"], 2); ?>)');
										}
										$("#deliveryfee").html(deliveryfree.toFixed(2));
										totalamount = deliveryfree + totalorder;
										$("#deliveryfees").val(<?=$qryrest["delivery_fee"]?>);
									
									$("#total").val(totalamount.toFixed(2));
									$("#totalamount strong").text(totalamount.toFixed(2));
						</script>
                        <div class="deliverycheck" style="width:100%;">
	                		<div class="ui-block-a" style="width:55%"><?=$lngdeliverycharge?> 
								<? if($totalorder >= $qryrest["free_delivery_amount"]){?>
                                    <span style="font-size:10px" id="commentabove">(<?=$lngfreeabove." ".$qryrest["currency_sign"]." ".number_format($qryrest["free_delivery_amount"], 2); ?>)</span>
                                <? } ?>
	                		</div>
							<div class="ui-block-b taxamount1" id="deliveryfee" style="width:15%; text-align:center"></div>
	                		<div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
                            <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
                     	</div>
                     <? } 
					 else if ($qryrest["is_delivery"]) {?>
                        <script>
								$("input[name$='deliver']").click(function(){
									var radio = $(this).val();
									var totalamount = parseFloat($("#totalamount strong").text());
									var deliveryfee = parseFloat(<?=$qryrest["delivery_fee"]?>);
									if(radio=='1') {
										var oldradio='1';
										$(".deliverycheck").show();
										var free_delivery_amount = parseFloat(<?=$qryrest["free_delivery_amount"]?>);
										var totalamount;
										if(totalamount > free_delivery_amount)
										{
											deliveryfee = 0;
										}
										$("#deliveryfee").html(deliveryfee.toFixed(2));
										$("#deliveryfees").val(deliveryfee);
										totalamount = deliveryfee + totalamount;
									}
									else
									{
										$(".deliverycheck").hide();
										if(oldradio='1') { totalamount = totalamount - deliveryfee;}
										oldradio='0';
									}
									$("#total").val(totalamount.toFixed(2));
									$("#totalamount strong").text(totalamount.toFixed(2));
								});
						</script>
                        <div class="deliverycheck" style="width:100%; display:none">
	                		<div class="ui-block-a" style="width:55%"><?=$lngdeliverycharge?>
                                    <span style="font-size:10px" id="commentabove">(<?=$lngfreeabove." ".$qryrest["currency_sign"]." ".number_format($qryrest["free_delivery_amount"], 2); ?>)</span>
	                		</div>
							<div class="ui-block-b" id="deliveryfee" style="width:15%; text-align:center">
								<? 
                                /*$tax_details[] = array(
                                    "name" => "Delivery Fee",
                                    "cost" => $qryrest["delivery_fee"],
                                    "currency" => $qryrest["currency_sign"],
                                );*/
                                ?>
							</div>
	                		<div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
                            <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
                     	</div>
                     <? } 
						else
						{
							$total=$totalorder + $qryrest["convenience_fee"] + $totaltax1; 
						}
						$total=$totalorder + $qryrest["convenience_fee"] + $totaltax1; ?>
	                        <div class="ui-block-a" style="width:55%;"><em><strong><?=$lngtotcharges?> (<?=$qryrest["currency_sign"]?>)</strong></em></div>
							<div class="ui-block-b" id="totalamount" style="width:15%; text-align:center"><em><strong><?=number_format($total,2)?></strong></em></div>
	                		<div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
                            <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
                            <div class="ui-block-a" style="width:55%; height:30px"><?=$lnglocations?></div>
                            <? $city = get_tab_location($conn, $data["loc_id"], 'id');?>
                            <div class="ui-block-b" style="width:15%; height:30px; text-align:center"><?=$city[0][city]?></div>
                            <div class="ui-block-c" style="width:10%; height:30px; text-align:center"></div>
                            <div class="ui-block-d" style="width:20%; height:30px; text-align:center"></div>
                        
          </div><!-- /grid-b -->
          <form action="web-widget.php" method="get" id="cartform" data-ajax="false">
            	<input type="hidden" name="location" value="<?=$city[0][city]?>" >
                <input type="hidden" name="order_tax_details" value="<?=base64_encode(serialize($tax_details));?>" >
                <?
				/*print_r($tax_details);
				echo '<br>Serialize'.serialize($tax_details);
				echo '<br><br>Encode'.base64_encode(serialize($tax_details));
				echo '<br><br>Encode and slass'.base64_encode(addslashes(serialize($tax_details)))
				$testtax=base64_encode(serialize($tax_details));
				echo '<br><br>TEST ZAFAR'.unserialize(base64_decode($testtax));
				echo '<br><br>Return array';
				print_r(unserialize(base64_decode($testtax)));*/
		
				if($qryrest["convenience_fee"])
					{
				?>
                <input type="hidden" name="convenience_fee" value="<?=$qryrest["convenience_fee"]?>" >
				<?
					}
				?>
                <?
				if($qryrest["delivery_fee"])
					{
				?>
                <input type="hidden" name="delivery_fee" id="deliveryfees" value="<?=$qryrest["delivery_fee"]?>" >
				<?
					}
				?>
				<?php echo $POST_PARAMS; ?>
                <? if(($qryrest["takeout"] && $qryrest["is_delivery"] && $qryrest["dinein"]) || ($qryrest["takeout"] && $qryrest["is_delivery"]) ||($qryrest["takeout"] && $qryrest["dinein"]) || ($qryrest["is_delivery"] && $qryrest["dinein"])) {?>
                <div data-role="fieldcontain">
                    <fieldset data-role="controlgroup">
                    <legend><?=$lnggetorder?></legend>
                    <? if($qryrest["dinein"]){?>
                            <input type="radio" name="deliver" id="dinein" value="3" checked />
                            <label for="dinein"><?=$lngdinein?></label>
                    <? } ?>
                    <? if($qryrest["takeout"]){?>
                            <input type="radio" name="deliver" id="takeout" value="2" checked />
                            <label for="takeout"><?=$lngtakeout?></label>
                    <? } ?>
                    <? if($qryrest["is_delivery"]){?>
                            <input type="radio" name="deliver" id="is_delivery" value="1" />
                            <label for="is_delivery"><?=$lngdelivery?></label>
                    <? } ?>
                    </fieldset>
				</div>
                <? } else if ($qryrest["takeout"]) {?>
                <p><? printf($lngoptonly1,$lngtakeout);?></p>
                <input type="hidden" name="deliver" id="deliver" value="2"  />
                <? } else if ($qryrest["dinein"]) {?>
                <p><? printf($lngoptonly1,$lngdinein);?></p>
                <input type="hidden" name="deliver" id="deliver" value="3"  />
                <? } else if ($qryrest["is_delivery"]) {?>
               	<p><? printf($lngoptonly1,$lngdelivery);?></p>
                <input type="hidden" name="deliver" id="deliver" value="1"  />
                <? } ?>
                 <div data-role="fieldcontain">
                    <fieldset data-role="controlgroup">
                    <legend><?=$lngpayorder?></legend>
          			<?
					$sqlpayment = "SELECT gateway_type,is_main FROM mst_options WHERE app_id=".$app_id." and tab_id=".$data["tab_id"]." AND is_main ='1' order by gateway_type desc";
					$respayment = mysql_query($sqlpayment, $conn);
					$respayment2 = mysql_query($sqlpayment, $conn);
					$gatewayid = mysql_result($respayment2, 0, 0);
					if(mysql_num_rows($respayment) > 1) 
					{
						while($qrypayment = mysql_fetch_array($respayment))
						{
							if($qrypayment["gateway_type"]=='4'){?>
                            <input type="radio" name="payment" id="cash" value="4" checked />
                            <label for="cash"><?=$lngcash?></label>
                    	<? 	} 
							if($qrypayment["gateway_type"]=='1'){?>
                            <input type="radio" name="payment" id="paypal" value="1" checked />
                            <label for="paypal"><?=$lngpaypal?></label>
                    <? 		} 
							if($qrypayment["gateway_type"]=='2'){?>
                            <input type="radio" name="payment" id="google" value="2" checked />
                            <label for="google"><?=$lnggcheckout?></label>
                    <? 		} 
						}
					} else if($gatewayid=='4'){?>
                            <input type="hidden" name="payment" value="4"/>
                            <label><?=$lngcash?></label>
                <?  } else if($gatewayid=='1'){?>
                            <input type="hidden" name="payment" value="1"/>
                            <label><?=$lngpaypal?></label>
                <?  } else if($gatewayid=='2'){?>
                            <input type="hidden" name="payment" value="2"/>
                            <label><?=$lnggcheckout?></label>
                <?  } ?>
                    </fieldset>
				</div>
                <input type="hidden" name="total" id="total" value="<?=$total?>"/>
                <input type="hidden" name="totaltax" id="totaltax" value="<?=$totaltax1?>"  />
                <div data-role="fieldcontain">
                 <label for="fname"><?=$lngfirstname?></label>
                 <input type="text" name="fname" id="fname" required placeholder="<?=$lngfirstname?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="lname"><?=$lnglastname?></label>
                 <input type="text" name="lname" id="lname" required placeholder="<?=$lnglastname?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="pnum"><?=$lngphoneno?></label>
                 <input type="text" name="pnum" id="pnum" required placeholder="<?=$lngphoneno?>" value=""  />
                </div>
                <? //if (($data['deliver']=='yes' && !$data['dinein'] )  || (!$qryrest["takeout"] && !$qryrest["dinein"])) {?>
                <div class="deliverycheck" style="display:none">
                    <div data-role="fieldcontain">
                     <label for="addr1"><?=$lngstreetadd1?></label>
                     <input type="text" name="addr1" id="addr1" placeholder="<?=$lngstreetadd1?>" value=""  />
                    </div>
                    <div data-role="fieldcontain">
                     <label for="addr2"><?=$lngstreetadd2?></label>
                     <input type="text" name="addr2" id="addr2" placeholder="<?=$lngstreetadd2?>" value=""  />
                    </div>
                    <div data-role="fieldcontain">
                     <label for="city"><?=$lngcity?></label>
                     <input type="text" name="city" id="city" placeholder="<?=$lngcity?>" value=""  />
                    </div>
                    <div data-role="fieldcontain">
                     <label for="state"><?=$lngstate?></label>
                     <input type="text" name="state" id="state" placeholder="<?=$lngstate?>" value=""  />
                    </div>
                    <div data-role="fieldcontain">
                     <label for="zipcode"><?=$lngpostcode?></label>
                     <input type="text" name="zipcode" id="zipcode" placeholder="<?=$lngpostcode?>" value=""  />
                    </div>
                <? //} ?>
                </div>
                	<div data-role="fieldcontain">
                     <label for="email"><?=$lngemail?></label>
                     <input type="email" name="email" id="email" placeholder="<?=$lngemail?>" value=""  />
                    </div>
                <div>
                <fieldset>
                        <div><button type="submit"  id="action" name="action" value="checkout" data-theme="a"><?=$lngcheckout?></button></div>
                </fieldset>
                </div>
                </form>
                
                

                
			<?	} else { 
				unset($_SESSION['orderstr']);
				unset($_SESSION['totalcharge']);?>
                <br><p style="text-align:center"><?=$lngnoorder?></p><br><br>
                <a href="./web-widget.php" rel="external"><div align="center"><img src="images/addcart.png" /></div></a>
			<? 	}?>
			</p>
     </div>
    <? }?>
	<script type='text/javascript'>
	$("#closenotice").click(function ( event ) {
      event.preventDefault();
      $("div #addedtocart").slideUp('slow', function() {
    // Animation complete.
  });
    });
	</script>
</body>
</html>
<?
}
else
{
	echo "PLEASE ENABLE BROWSER COOKIES TO PLACE AN ORDER";
}?>

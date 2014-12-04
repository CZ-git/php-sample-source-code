
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
						jAlert("<?=$lngtoomany?>", 'Alert Dialog');
						this.checked=false
						}
				}
			}
		}
						var x=document.getElementById("loc");
						function latlong()
						{
							
							if (navigator.geolocation)
                            {
                            	navigator.geolocation.getCurrentPosition(showPosition);
                            }
                            else
							{
								x.innerHTML="<?=$lnggeonot?>";
							}
						}
						function showPosition(position)
  						{
                            x.innerHTML="<input type=\"hidden\" name=\"latitude\" id=\"latitude\" value="+ position.coords.latitude +"  /><br><input type=\"hidden\" name=\"longitude\" id=\"longitude\" value="+ position.coords.longitude +"  />";
						}						
                        </script>       
             
	<? include_once("header.php"); 
	date_default_timezone_set('GMT');
	$locs = get_tab_location($conn, $data["loc_id"], 'id');
	//print_r($item);
	$locs = get_tab_location($conn, $data["loc_id"], 'id');
	$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
	//echo $jsonObject;
	$object = json_decode(file_get_contents($jsonObject));
	$locs[0][timezone_value] = time() + ($locs[0][timezone_value] * 60 * 60);
	$timeinmins = (date("G",$locs[0][timezone_value]))*60+date("i",$locs[0][timezone_value])+$object->dstOffset/60;
	//echo "time in mins ".date("G",$locs[0][timezone_value]).":".date("i",$locs[0][timezone_value])."    DST Offset: ".$object->dstOffset/60;
	$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
	$object = json_decode(file_get_contents($jsonObject));
	$ordertimesec = time() + ($order["timezone_value"] * 60 * 60);
	$orderhour = date("G",$ordertimesec)+$object->dstOffset/3600;
	$ordermin = date("i",$ordertimesec);
	$paid_details_html .= "<p><b>Order Created on >".$orderhour.":".$ordermin.":00</b></p>";
	$sql = "SELECT open_time, close_time, more_time FROM `restaurant_time` where item_id=$item[id] and day = '".date("l",$locs[0][timezone_value])."'";
	//log_info(__FILE__, 'Restaurant time query for item id and day: ' . $sql , 'TESTING');
	$res = mysql_query($sql, $conn);
	//echo $sql;
	$item_opentime =  mysql_result($res, 0, 0);
	$item_closetime =  mysql_result($res, 0, 1);
	$item_moretime =  json_decode(mysql_result($res, 0, 2));
	$_SESSION[itemopen] = false;
	if ($item[is_available]=="1")
	{
		$_SESSION[itemopen] = true;
	}
	else if ($item[is_available]=="0")
	{
		$_SESSION[itemopen] = false;
	}
	else if ($timeinmins >= $item_opentime && $timeinmins < $item_closetime)
	{
		$_SESSION[itemopen] = true;
	}
	else
	{
	//"More Time ";
	//print_r($item_closetime);
		foreach ($item_moretime as $key => $val)
		{
			if ($timeinmins >= $val[0] && $timeinmins < $val[1])
			{
				$_SESSION[itemopen] = true;
			}
        }
	}?>
		<div data-role="content" style="background-color: rgba(255,255,255,0.8);">
            <div class="content-primary">
            <ul data-role="listview" data-divider-theme="d">
				<li data-role="list-divider" style="height:20px">
					<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
						<a href="#popupMenu" class="ui-bar-d" data-role="button"><i class="fa fa-list"></i></a>
					</div>
					<span class="divtext"><?=$lngdetail?></span>
					<div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
						<a href="?p=cart&<?=$PASS_PARAMS;?>" class="ui-bar-d" data-role="button"><i class="fa fa-shopping-cart"></i></a>
					</div>
				</li>
			</ul>
            <?
			if ($_SESSION[itemopen] == true)
			{?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" id="itemform">
			
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            <?php echo $POST_PARAMS; ?>
            <input type="hidden" name="p" value="order" >
			<h3 align="center"><?=htmlentities($item["item_name"], ENT_QUOTES | ENT_IGNORE, "UTF-8");?></h3>
            <div align="center">
            <?php
            $uid = uniqid();
            $img_url = "";
			if(preg_match("/^http:\/\/(.*)$/", $item["image_url"]) || preg_match("/^http:\/\/(.*)$/", $item["image_url"])) {
				$img_url = '<img width="300" src="/images_online.php?name='.urlencode($item["image_url"]).'&'.$uid.'" alt="Image" width="300" />';	
			} else {
				$dir = findUploadDirectory($app_id) . "/ordering/".$item["image_url"];
				//echo $dir;
				if(file_exists($dir) && !is_dir($dir)) {
					$img_url = '<img width="300" src="/custom_images/'.$data[app_code].'/ordering/'.$item["image_url"].'?'.$uid.'" />';
				}
			}
			echo $img_url;
            ?>
            </div><br>
            <? if ($item["description"]) {?>
            	<strong><?=$lngdetail?> </strong><br><?=nl2br($item["description"]) ?><br>
            <? } ?>
            
            <? if(count($sz) > 0) { ?>
            	<div class="ui-field-contain">
			    	<fieldset data-role="controlgroup" >	
                    	<legend><?=$lngchoosesize?></legend>
						<?php
						$i = 0; 
						foreach($sz AS $qrysize) {
							$i++;
							$qrysize["price"]=number_format($qrysize["price"],2);
							$regex = '#[^a-z0-9_]#i'; $sizeform = preg_replace($regex, '', $qrysize["size"]);
							?>
                        	<input type="radio" name="size" id="o<?=$sizeform?><?=$i?>" value="<?=$qrysize["id"]?>" <? if($i==1) {?>checked="checked"<? }?> />
			         		<label for="o<?=$sizeform?><?=$i?>"><?=$qrysize["size"]?> (<?php echo $item["currency_sign"]." ".$qrysize["price"]?>)</label>
                     <?	} ?>
                    </fieldset>
            	</div>
            <? } else { 
				$item["price"]=number_format($item["price"],2);
			?>
            	<strong><?=$lngprice?>:</strong><br><?php echo $item["currency_sign"]." ".$item["price"]?><br>
            <? }?>
            <div class="ui-field-contain">
                <label for="quantity" class="select"><?=$lngquantity?>:</label>
                <select name="quantity" id="quantity">
                	<? 	$quantity = 1;
						while ($quantity < 16)
						{
							echo "<option value='$quantity'>$quantity</option>";
							$quantity++;
						}
					?>
                </select>
            </div>
            <?php 
            $sqloptgrp = "SELECT option_group,min_selection,max_selection FROM `restaurant_item_options` where item_id=".$item["id"]." Group by option_group order by seq desc";
			$resoptgrp = mysql_query($sqloptgrp, $conn);
			if (mysql_num_rows($resoptgrp) != 0) { ?>
				<div class="ui-field-contain">
				<? $optnum = 0;
					while ($qryoptgrp = mysql_fetch_array($resoptgrp)) { $optnum++;
								echo "<p class='error minselecterror$optnum'>".sprintf ($lngminselect, $qryoptgrp["min_selection"]);
								if($qryoptgrp["option_group"]) { echo " ".$lngfor." ".$qryoptgrp["option_group"]; }
								echo "</p>"; ?>
					<fieldset data-role="controlgroup">
						<legend><?=$lngchooseoption?> <?=$qryoptgrp["option_group"]?></legend>
						<?php 
							$option_group = mysql_real_escape_string($qryoptgrp["option_group"]);
							$sqlopt = "SELECT * FROM `restaurant_item_options` where item_id=".$item["id"]." and option_group='".$option_group."' order by seq desc"; //echo $sqlopt;
							$resopt = mysql_query($sqlopt, $conn);
							$i = 0;
							while ($qryopt = mysql_fetch_array($resopt)) { $i++;
								$qryopt["option_name"] = urlencode($qryopt["option_name"]);
								$qryopt["option_charges"]=number_format($qryopt["option_charges"],2);
								if (intval($qryopt["max_selection"]) == 0) {$qryoptgrp["max_selection"] = mysql_num_rows($resopt);}
								$regex = '#[^a-z0-9_]#i'; $optiongrp = preg_replace($regex, '', $qryoptgrp["option_group"]);
							?>
								<input type="checkbox" name="option<?=$optiongrp.$optnum?>[]" id="<?=$qryopt["option_name"]?>" value="<?=$qryopt["id"]?>" />
								<label for="<?=$qryopt["option_name"]?>"><?=urldecode($qryopt["option_name"])?> (<? if($qryopt["option_charges"]){?><?=$item["currency_sign"]." ".$qryopt["option_charges"]?><? } else { echo $lngnoextra; }?>)</label>
						 <?	} ?>
                         		<script type="text/javascript">
									checkboxlimit($('[name^=option<?=$optiongrp.$optnum;?>]'), <?=$qryoptgrp["max_selection"]?>)
								</script>
					</fieldset>
					<? 		$checkbox[] = "$('[name^=option".$optiongrp."]:checked').length < ".$qryoptgrp["min_selection"];
							//print_r($checkbox);
							//$checkboximplode = implode("|| ",$checkbox);
							//echo "checkboximplode ".$checkboximplode;
				 }?>	
                 			<script>
							$("#itemform").submit(
								function() { 
									<? 	$optnum = 0;
										foreach ( $checkbox as $maxoptioncheck )
										{ $optnum++?>
											if(<?=$maxoptioncheck?>) 
											{
												$(".minselecterror<?=$optnum;?>").fadeIn('slow');
												var i=1;
											}
											else
											{
												$(".minselecterror<?=$optnum;?>").fadeOut('slow');
											}
									<? 	}?>
									if(i==1)
									{
										return false;
									}
									else
									{
										return true;
									}
								});
							</script>
                </div>
			<? }?>
            <div>
            	<script type="text/javascript"> latlong()</script>
            	<fieldset data-role="controlgroup">
                	<legend><?=$lngspecinstruct?></legend>
					<textarea cols="40" rows="8" name="instructions" id="instructions" ></textarea>
                </fieldset>
			</div>
            <div>
            	<span id="loc"></span>
                <fieldset>
                	<div><button type="submit"  id="action" name="action" value="submit" data-theme="a"><?=$lngadditem?></button></div>
                </fieldset>
                <br><br><br>
            </div>
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
			  if($main_info[brief_desc])
			  { 
			  echo "<strong>$lngdetail: </strong>".nl2br($item["description"])."<br><br>";
			  }
			  if($main_info[cuisine_type])
			  {
			  echo "<strong>$lngcuisine: </strong>".$main_info[cuisine_type]."<br><br>";
			  }
			  echo "<strong>$lngweoffer: </strong><br>";
			  if ($main_info[dinein]){echo "$lngdinein<br>";}
			  if ($main_info[takeout]){echo "$lngtakeout<br>";}
			  if ($main_info[is_delivery]){ echo "$lngdelivery<br>";}
			  ?>
              </td>
		    </tr>
		  </tfoot>
		  <tbody>
		  <?
		  $items_open_times = get_item_serve_time($conn, $item[id], $main_info[id]);
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
		}
		?>
       	</div>
	</div><!-- /content -->
    
    <script type="text/javascript">

	$(document).ready(function(){
		$('#action').click(function() {
				$('.ui-header').hide();
			});
	});
	</script>
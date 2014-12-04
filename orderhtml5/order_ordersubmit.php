<?php
	$optionid = "";
	foreach ($data as $key => $valid){
		
		if(substr($key,0,6) == "option") {
			foreach($valid AS $idv) {
				if(intval($idv) > 0) {
					if($optionid != "") $optionid .= ",";
					$optionid .= $idv;
				}
			} 
		}
	}
	$sqloptgrp = "SELECT option_group,min_selection FROM  `restaurant_item_options` where item_id=".$item["id"];
	/*echo "sqloptgrp=".$sqloptgrp;
	echo "<br>";*/
	$resoptgrp = mysql_query($sqloptgrp, $conn);
	$resoptgrp1 = mysql_query($sqloptgrp, $conn);
	if (mysql_num_rows($resoptgrp) != 0) {
		while($qryoptionsgrp = mysql_fetch_array($resoptgrp))
		{
			$count=0;
			if(!empty($optionid)) {
				$sqloptions = "SELECT option_group,min_selection FROM  `restaurant_item_options` where id IN ($optionid) and option_group='".$qryoptionsgrp["option_group"]."'";
				//echo "sqloptions=".$sqloptions;
				$resoptions = mysql_query($sqloptions, $conn);
				//echo $qryoptionsgrp["min_selection"];
				if (mysql_num_rows($resoptions) != 0) { 
					while($qryoptions = mysql_fetch_array($resoptions)) {
						$count++;
					}
					if($count < $qryoptionsgrp["min_selection"]) {
						$minalarm=true;
					}
				} else {
					if($qryoptionsgrp["min_selection"]!='0') {
						$minalarm=true;
					}
				}
			}
		}
	}
	
?>          
	<? include_once("header.php"); ?>
		<div data-role="content" style="background:#FFF; opacity:0.8;">
            <div class="content-primary ordersubmit">
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" name="test">
				
				<?php echo $POST_PARAMS; ?>
				<input type="hidden" name="p" value="order" >
				
            	<ul data-role="listview" data-divider-theme="d">
                <li data-role="list-divider" style="height:20px; /*display: -webkit-box;*/">
                	<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
						<a href="#popupMenu" class="ui-bar-d" data-role="button"><i class="fa fa-list"></i></a>
					</div>
                	<span class="divtext" style="/*display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;
width: 189px;*/"><?=$lngconfirmitem?></span>
					<div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
						<a href="?p=cart&<?=$PASS_PARAMS;?>" class="ui-bar-d" data-role="button"><i class="fa fa-shopping-cart"></i></a>
					</div>
				</li>
            	</ul>
                    
                    <p><br>
                    <strong><?=$lngyouchoose?></strong>
                    <div class="ui-grid-a">
                        <div class="ui-block-a"><strong><?=$lngitem?></strong></div>
                        <div class="ui-block-b" style="text-decoration:underline"><strong><?=htmlentities($item["item_name"], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?></strong></div>
                    </div><!-- /grid-a -->
                    <div class="ui-grid-a">
                        <div class="ui-block-a"> <strong><?=$lngdetail?></strong></div>
                        <div class="ui-block-b"><?=nl2br($item["description"]) ?></div>
                    </div><!-- /grid-a -->
                    <? if ($data["size"])
					{
						$sqlsize = "SELECT * FROM  `restaurant_item_size` where id = ".$data["size"];
						//echo $sqlsize;
						$ressize = mysql_query($sqlsize, $conn);
						$qrysize = mysql_fetch_array($ressize);
					?>
                    <div class="ui-grid-a">
                        <div class="ui-block-a"><strong><?=$lngsize?></strong></div>
                        <div class="ui-block-b"><?php echo $qrysize["size"]; ?></div>
                    </div><!-- /grid-a -->
                    <div class="ui-grid-a">
                        <div class="ui-block-a"><strong><?=$lngprice?></strong></div>
                        <div class="ui-block-b"><?php echo $item["currency_sign"].number_format($qrysize["price"],2); ?></div>
                    </div><!-- /grid-a -->
                    <? $total=$qrysize["price"];
					} else {?>
                    <div class="ui-grid-a">
                        <div class="ui-block-a"><strong><?=$lngprice?></strong></div>
                        <div class="ui-block-b"><?php echo $item["currency_sign"].number_format($item["price"],2); ?></div>
                    </div><!-- /grid-a -->
                    <? $total=$item["price"]; } ?> 
					<?php if ($data[quantity]) { ?>
                    <div class="ui-grid-a">
                        <div class="ui-block-a"><strong><?=$lngquantity?></strong></div>
                        <div class="ui-block-b"><?=$data["quantity"] ?></div>
                    </div><!-- /grid-a -->
                    <? }?>
					<?php
                    $detail_array = array();
                    $detail_array[] = array(
						"name" =>(($data["size"])?$item["item_name"]."($qrysize[size])":$item["item_name"]),
						"cost" =>(($data["size"])?$qrysize["price"]:$item["price"]),
						"currency" =>$item["currency_sign"],   
					);
					?>
                    <?php if ($optionid != "") { ?>
                    <div class="ui-grid-a">&nbsp;</div>
                    <?php
						//echo $optionid;
                    	$sqloptions = "SELECT * FROM  `restaurant_item_options` where id IN ($optionid) ORDER BY option_group";
						$resoptions = mysql_query($sqloptions, $conn);
						if(mysql_num_rows($resoptions) > 0) {
							$last_opt_group = "";
							while($qryoptions = mysql_fetch_array($resoptions)) { 
								$detail_array[] = array(
									"name" => $qryoptions["option_group"]." - ".htmlspecialchars($qryoptions["option_name"], ENT_QUOTES),
									"cost" =>$qryoptions["option_charges"],
									"currency" =>$item["currency_sign"],
								);
								
								?>
								<?php 
			                        if($last_opt_group != $qryoptions["option_group"]) {
			                        	 $last_opt_group = $qryoptions["option_group"];
			                        ?>
			                        <strong><?php echo $qryoptions["option_group"]?></strong>
			                        <?php } ?>
								<div class="ui-grid-a">
			                        <div class="ui-block-a"></div>
			                        <div class="ui-block-b"><?php echo $qryoptions["option_name"]; ?> | <?php echo $item["currency_sign"].number_format($qryoptions["option_charges"],2); ?></div>
			                    </div><!-- /grid-a -->
		                    
					<?php	
								$total=$qryoptions["option_charges"]+$total;
								$option1=$qryoptions["option_name"]." | ".$option1;	
							}
						}
                    }
                    
					?>
					
					<?php if ($data[instructions]) { ?>
                    <br>
                    <strong><?=$lngspecinstruc?> </strong><br><?php echo nl2br(str_replace('\r\n', "\n", ($data[instructions])));?>
                     <input type="hidden" name="instruction" value="<?php echo $data[instructions]?>" >
                    <br>
                    <br>
                    <? }?>
                    
                    <input type="hidden" name="order" value="<?php echo base64_encode(addslashes(serialize($detail_array)));?>" >
                    <input type="hidden" name="quantity" value="<?=$data["quantity"];?>" >
                    <input type="hidden" name="total" value="<?php echo $total?>" >
                    <input type="hidden" name="item_id" value="<?php echo $item["id"]; ?>" >
                        <a data-role="button" data-rel="back"><?=$lngorderback?></a>
                        <? if(isset($minalarm) && $minalarm==true) {
							?> <p><? while($qryoptionsgrp = mysql_fetch_array($resoptgrp1))
							{
								echo sprintf ($lngminselect, $qryoptionsgrp["min_selection"]);?> <? if($qryoptionsgrp["option_group"]!="") { echo $lngfor." ".$qryoptionsgrp["option_group"]; }?><br />
                        <? 	}?></p>
                        <button type="submit" id="action" name="action" value="add_to_cart" disabled="disabled" data-theme="b"><?=$lngtocart?></button>
						<? } else {?>
                        <button type="submit" id="action" name="action" value="add_to_cart" data-theme="b"><?=$lngtocart?></button>
                       	<? }?>
			</form>
			<br><br><br>
            </div>
        </div>
	
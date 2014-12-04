<? include_once("header.php"); ?>
    <style>
    #tooltip
    {
            font-family: Ubuntu, sans-serif;
            font-size: 0.875em;
            text-align: center;
            text-shadow: 0 1px rgba( 0, 0, 0, .5 );
            line-height: 1.5;
            color: #fff;
            background: #333;
            background: -webkit-gradient( linear, left top, left bottom, from( rgba( 0, 0, 0, .6 ) ), to( rgba( 0, 0, 0, .8 ) ) );
            background: -webkit-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
            background: -moz-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
            background: -ms-radial-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
            background: -o-linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
            background: linear-gradient( top, rgba( 0, 0, 0, .6 ), rgba( 0, 0, 0, .8 ) );
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            border-top: 1px solid #fff;
            -webkit-box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
            -moz-box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
            box-shadow: 0 3px 5px rgba( 0, 0, 0, .3 );
            position: absolute;
            z-index: 100;
            padding: 15px;
    }
    #tooltip:after /* triangle decoration */
    {
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-top: 10px solid #111;
        content: '';
        position: absolute;
        left: 50%;
        bottom: -10px;
        margin-left: -10px;
    }
        #tooltip.top:after
        {
            border-top-color: transparent;
            border-bottom: 10px solid #111;
            top: -20px;
            bottom: auto;
        }
 
        #tooltip.left:after
        {
            left: 10px;
            margin: 0;
        }
 
        #tooltip.right:after
        {
            right: 10px;
            left: auto;
            margin: 0;
        }
        .ui-bar{ padding-top:18px };
        form div{ overflow: hidden; margin: 0 0 5px 0; }
        .dec { cursor: pointer; margin-left:50%; text-align: center; }
        .inc { cursor: pointer; margin-left:50%; text-align: center; }
        
        .unitprice,.currsign { font-size:9px; font-style:italic;}
        .ui-content {/*padding:15px 5px 5px 5px;*/ font-size:12px}
    </style>
    <script type='text/javascript'>//<![CDATA[ 
$(function(){
$.tools.validator.fn("[data-date]", function (input, value) {
        var av = input.attr("data-date");
        return /^(([1-9])|(0[1-9])|(1[0-2]))\/(([0-9])|([0-2][0-9])|(3[0-1]))\/(([0-9][0-9])|([1-2][0,9][0-9][0-9]))$/.test(value) ? true : "Invalid date. Please use format dd/mm/yy";
    });

    $.tools.validator.localize("en", {
            '*'            : '<?=$lngerrormsg?>',
            ':email'      : '<?=$lngerroremail?>',
            '[required]'    : '<?=$lngerrormsg?>'
        });
});//]]>  
$( document ).ready( function()
{
    var targets = $( '[rel~=tooltip]' ),
        target  = false,
        tooltip = false,
        title   = false;
 
    targets.bind( 'touchstart', function()
    {
        target  = $( this );
        tip     = target.attr( 'title' );
        tooltip = $( '<div id="tooltip"></div>' );
 
        if( !tip || tip == '' )
            return false;
 
        target.removeAttr( 'title' );
        tooltip.css( 'opacity', 0 )
               .html( tip )
               .appendTo( 'body' );
 
        var init_tooltip = function()
        {
            if( $( window ).width() < tooltip.outerWidth() * 1.5 )
                tooltip.css( 'max-width', $( window ).width() / 2 );
            else
                tooltip.css( 'max-width', 340 );
 
            var pos_left = target.offset().left + ( target.outerWidth() / 2 ) - ( tooltip.outerWidth() / 2 ),
                pos_top  = target.offset().top - tooltip.outerHeight() - 20;
 
            if( pos_left < 0 )
            {
                pos_left = target.offset().left + target.outerWidth() / 2 - 20;
                tooltip.addClass( 'left' );
            }
            else
                tooltip.removeClass( 'left' );
 
            if( pos_left + tooltip.outerWidth() > $( window ).width() )
            {
                pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
                tooltip.addClass( 'right' );
            }
            else
                tooltip.removeClass( 'right' );
 
            if( pos_top < 0 )
            {
                var pos_top  = target.offset().top + target.outerHeight();
                tooltip.addClass( 'top' );
            }
            else
                tooltip.removeClass( 'top' );
 
            tooltip.css( { left: pos_left, top: pos_top } )
                   .animate( { top: '+=10', opacity: 1 }, 50 );
        };
 
        init_tooltip();
        $( window ).resize( init_tooltip );
 
        var remove_tooltip = function()
        {
            tooltip.fadeOut(2000, function() { $(this).remove(); }); 
            target.attr( 'title', tip );
        };
        target.bind( 'mouseleave', remove_tooltip );
        target.bind( 'touchend', remove_tooltip );
        target.bind( 'click', remove_tooltip );
        tooltip.bind( 'mouseleave', remove_tooltip );
        tooltip.bind( 'click', remove_tooltip );
        tooltip.bind( 'touchend', remove_tooltip );
    });
});
</script>
    <div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
            <ul data-role="listview" data-divider-theme="d">
                <li data-role="list-divider" style="height:20px">
                    <div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
                        <a href="#popupMenu" class="ui-bar-d" data-role="button"><i class="fa fa-list"></i></a>
                    </div>
                </li>
               </ul><br>
            <p>
            <div class="ui-grid-c">
            <?php if (mysql_num_rows($resorders) !=0) { ?>
                <div class="ui-block-a" style="width:40%; font-size:12px"><strong><?=$lngitem?></strong></div>
                <div class="ui-block-b" style="width:15%; text-align:center; font-size:12px"><strong><?=$lngprice?></strong></div>
                <div class="ui-block-c" style="width:25%; text-align:center; font-size:12px"><strong><?=$lngquantity?></strong></div>
                <div class="ui-block-d" style="width:20%; text-align:center; font-size:12px"><strong><?=$lngaction?></strong></div>

                <script type="text/javascript">
                            $(function() {
                                $(".button").bind('change blur',function() {
                                    var totalamount;
									var totaltaxamount;
                                    var button = $(this);
                                    var orderno = button.parent().find(".orderno").text();
                                    var uprice = button.parent().parent().find(".unitprice").text().replace(/[^\d.]/g, '');
                                    var currsign = button.parent().parent().find(".currsign").text();
                                    var newVal = button.find("#select-choice-mini").val();
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
                                    button.parent().parent().find(".ui-block-b").text(currsign+orderprice);
                                    totalamount =0;
                                            $(".cart-main").find(".ui-block-b").each(function () {
                                                var orderamount = parseFloat($(this).text().replace(/[^\d.]/g, ''));
                                                totalamount = totalamount + orderamount;
                                            });
											$(".cart-main").find(".notax").each(function () {
                                                var notaxamount = parseFloat($(this).text().replace(/[^\d.]/g, ''));
                                                totaltaxamount = totalamount - notaxamount;
                                            });
                                            var totaltaxrate = 0;
                                            $(".taxamount0").each(function () {
                                                var taxrate = parseFloat($(this).find(".taxrate").text());
                                                totaltaxrate =  taxrate + totaltaxrate;
                                                $(this).find(".taxcompute").text((totaltaxamount*taxrate).toFixed(2));
                                            });
                                            $("input#totaltax").val(totaltaxamount*totaltaxrate);
                                            var newtotaltax = totaltaxamount*totaltaxrate;
                                            totalamount = totalamount+newtotaltax;
                                            $(".taxamount1").each(function () {
                                                var fixtax = parseFloat($(this).text().replace(/[^\d.]/g, ''));
                                                var fixtaxchange = 0;
                                                fixtaxchange = fixtaxchange + fixtax;
                                                totalamount = totalamount+fixtaxchange;
                                            });
                                    totalamount = totalamount.toFixed(2);    
                                    $("#totalamount strong").text(currsign+totalamount);
                                    $("input#total").val(totalamount);
                                    
                                });
                            });
                         </script>
                <?php
                    while($qryorders = mysql_fetch_array($resorders))
                    {
                        $sqlitem = "SELECT * FROM `restaurant_item` where id=".$qryorders["item_id"];
                        $resitem = mysql_query($sqlitem, $conn);
                        $qryitem = mysql_fetch_array($resitem);
                        ?>
                        <div class="cart-line" style="width:100%">
                            <div class="cart-main">
                                <div class="ui-block-a" style="width:40%;padding: 10px 0;"><?php
            if(preg_match("/^http:\/\/(.*)$/", $qryitem["image_url"]) || preg_match("/^http:\/\/(.*)$/", $qryitem["image_url"])) {
                $img_url = '<img src="/images_online.php?name='.urlencode($qryitem["image_url"]).'&height=60" alt="Image"/>';    
            } else {
                $dir = findUploadDirectory($app_id) . "/ordering/".$qryitem["image_url"];
                //echo $dir;
                if(file_exists($dir) && !is_dir($dir)) {
                    $img_url = '<img src="/custom_images/'.$data[app_code].'/ordering/'.$qryitem["image_url"].'?height=60" />';
                }
            }
            echo $img_url;
            ?><br><?=htmlentities($item["item_name"], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?><br><span class="unitprice">(<?=$lngunit_price?> <span class="currsign"><?=$qryrest["currency_sign"]?></span><?=number_format($qryorders["order_total"],2)?>)</span></div>
                                <div class="ui-block-b <? if($qryitem[tax_exempted]=='1') { echo "notax"; }?>" style="width:15%; text-align:center"><?=$qryrest["currency_sign"].number_format($qryorders["order_total"]*$qryorders["quantity"],2); ?></div>
                                <div class="ui-block-c" style="width:25%; text-align:center">
                                    <div class="orderno" style="display:none"><?=$qryorders["id"]?></div>
                                    <div class="quantity button"><select name="select-choice-mini" id="select-choice-mini" data-mini="true" data-inline="true">
                                    <? 
                                    $count=0; while($count <15)
                                    {
                                        $count++?>
                                        <option value="<?=$count?>" <? if($count==$qryorders["quantity"]) {?> selected<? }?>><?=$count?></option>
                                <?     }?>                                        
</select></div>
                                </div>
                                <div class="ui-block-d orderdelete" style="width:20%; text-align:center"><a data-ajax="false" href="?<?php echo $PASS_PARAMS; ?>&p=cart&delete=yes&id=<?=$qryorders["id"]; ?>"><img src="images/cart_rem.png" width="25" /></a></div>
                            </div>
                            <?php
                            $ord_detail = unserialize($qryorders[order_detail]);
                            if(is_array($ord_detail)) { ?>
                            <div class="cart-detail">
                            <?php    foreach($ord_detail AS $od) { ?>
                                    <div class="ui-block-a" style="width:60%;"><?=htmlspecialchars_decode($od["name"]); ?></div>
                                    <div class="ui-block-b" style="width:20%; text-align:center"><?=$qryrest["currency_sign"].number_format($od["cost"],2); ?></div>
                                    <div class="ui-block-c" style="width:10%; text-align:center"></div>
                                    <div class="ui-block-d" style="width:10%; text-align:center"></div>
                                    <div class="clear"></div>
                            <?php } ?>
                            </div>                            
                            <?php } ?>
                            <?php
                            $totalorder =    round(($qryorders["order_total"]*$qryorders["quantity"]),2)+$totalorder;
                            if($qryitem[tax_exempted]=='1') {$qryorders["order_total"]=0;}
                            $totalorderfortax =  round(($qryorders["order_total"]*$qryorders["quantity"]),2) + $totalorderfortax;
                            ?>
                        </div>
                        
                    <?php } ?>
                        
                    <?php
                    $tax_details = array();
                    if($qryrest["convenience_fee"]) {
                        $tax_details[] = array(
                            "name" => "Convenience Fee",
                            "cost" => $qryrest["convenience_fee"],
                            "currency" => $main_info["currency_sign"],
                        );
                    }
                    
                    ?>    
              <div class="ui-block-a" style="width:60%; height:30px"><?=$lngconvfee?></div>
              <div class="ui-block-b taxamount1" style="width:20%; height:30px; text-align:center"><?=$qryrest["currency_sign"].number_format($qryrest["convenience_fee"],2)?></div>
                 <div class="ui-block-c" style="width:20%; height:30px; text-align:center"></div>
                <?php        
                        $totaltax1 = 0;
                        
                        $sqltax = "SELECT * FROM `tax` where tab_id=$data[tab_id]";
                        $restax = mysql_query($sqltax, $conn);
						
                        while ($qrytax = mysql_fetch_array($restax)) 
						{ 
                            if($qrytax["tax_type"]=='1')
							{
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
                                    "currency" => $main_info["currency_sign"],
                                );
                                
                            ?> 
                                <div class="ui-block-a" style="width:60%; height:30px"><?=$qrytax["tax_name"]; ?></div>               
                                <div class="ui-block-b taxamount<?=($qrytax[tax_type] && $qryitem["tax_exempted"]!='1')?"1":"0";?>" style="width:20%; height:30px; text-align:center"><span class="taxrate" style="display:none"><?=$qrytax["tax_rate"]/100?></span><span class="taxcompute"><?=$qryrest["currency_sign"].number_format($tax,2) ?></span></div>
                                <div class="ui-block-c" style="width:20%; height:30px; text-align:center"></div>
                            <? } 
                            $totaltax1 = $tax + $totaltax1;
                        }?>
                        
                        <? if (($data['deliver']=='yes' && !$data['dinein'] )  || (!$qryrest["takeout"] && !$qryrest["dinein"])) {?>
                            <div class="ui-block-a" style="width:60%;"><?=$lngdeliverycharge?> 
                            <? if($totalorder >= $qryrest["free_delivery_amount"] && $qryrest["free_delivery_amount"] !="0"){?>
                                <span style="font-size:10px">(<?=$lngfreeabove." ".$qryrest["currency_sign"]." ".number_format($qryrest["free_delivery_amount"], 2); ?>)</span>
                            <? } ?>
                            </div>
                            <div class="ui-block-b taxamount1" style="width:20%; text-align:center">
                            <? 
                            if($totalorder < $qryrest["free_delivery_amount"] || $qryrest["free_delivery_amount"] == "0"){ 
                                echo $qryrest["currency_sign"].$qryrest["delivery_fee"]; 
                            } else { 
                                $qryrest["delivery_fee"]='0'; echo $qryrest["currency_sign"].$qryrest["delivery_fee"]; 
                            }
                            
                            $tax_details[] = array(
                                "name" => "Delivery Fee",
                                "cost" => $qryrest["delivery_fee"],
                                "currency" => $main_info["currency_sign"],
                            );
                            ?>
                            </div>
                            <div class="ui-block-c" style="width:20%; height:30px; text-align:center"></div>
                            <div class="ui-block-a" style="width:60%;"><em><strong><?=$lngtotcharges?> (<?=$qryrest["currency"]?>)</strong></em></div>    
                            <div class="ui-block-b" id="totalamount" style="width:20%; text-align:center"><em><strong><? $total=$qryrest["delivery_fee"] + $totalorder + $qryrest["convenience_fee"] + $totaltax1; $total=round($total,2); echo $qryrest["currency_sign"].number_format($total,2);?></strong></em></div>
                            <div class="ui-block-c" style="width:20%; height:30px; text-align:center"></div>
                        <? } else {?>
                            <div class="ui-block-a" style="width:60%;"><em><strong><?=$lngtotcharges?> (<?=$qryrest["currency"]?>)</strong></em></div>
                            <div class="ui-block-b" id="totalamount" style="width:20%; text-align:center"><em><strong><? $total=$totalorder + $qryrest["convenience_fee"] + $totaltax1; $total=round($total,2); echo $qryrest["currency_sign"].number_format($total,2);?></strong></em></div>
                            <div class="ui-block-c" style="width:20%; height:30px; text-align:center"></div>
                        <? } ?>
          </div><!-- /grid-b -->
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" id="cartform" data-ajax="false">
                  
                  <input type="hidden" name="order_tax_details" value="<?php echo base64_encode(serialize($tax_details));?>" >
                  <?php echo $POST_PARAMS; ?>
                  <input type="hidden" name="p" value="cart" >

                  
                <? if(($qryrest["takeout"] && $qryrest["is_delivery"] && $qryrest["dinein"]) || ($qryrest["takeout"] && $qryrest["is_delivery"]) ||($qryrest["takeout"] && $qryrest["dinein"]) || ($qryrest["is_delivery"] && $qryrest["dinein"])) {?>
                <div data-role="fieldcontain">
                    <fieldset data-role="controlgroup" data-type="horizontal">
                    <legend><?=$lnggetorder?></legend>
                    <? if($qryrest["dinein"]){?>
                            <input type="radio" name="deliver" id="dinein" onClick="parent.location='<?php echo $_SERVER['PHP_SELF']; ?>?dinein=yes&p=cart&<?php echo $PASS_PARAMS; ?>'" value="3" <? if ($data['dinein']=='yes' || !$data['deliver']) {?>checked<? }?> />
                            <label for="dinein"><?=$lngdinein?></label>
                    <? } ?>
                    <? if($qryrest["takeout"]){?>
                            <input type="radio" name="deliver" onClick="parent.location='<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $PASS_PARAMS; ?>&p=cart'" id="takeout" value="2" <? if (!$data['deliver'] && !$data['dinein']) {?>checked<? }?> />
                            <label for="takeout"><?=$lngtakeout?></label>
                    <? } ?>
                    <? if($qryrest["is_delivery"]){?>
                            <input type="radio" name="deliver" id="is_delivery" onClick="parent.location='<?php echo $_SERVER['PHP_SELF']; ?>?deliver=yes&<?php echo $PASS_PARAMS; ?>&p=cart'" value="1" <? if ($data['deliver']=='yes') {?>checked<? }?> />
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
                <? if($notinrange == 1) { ?> <p style="border-radius:3px; background-color:#000; color:#fff; padding:5px"><?=$lngoorange;?></p><? }?>
                 <div data-role="fieldcontain" class="paymentinput">
                    <fieldset data-role="controlgroup">
                    <legend><?=$lngpayorder?></legend>
                 <?
                    $sqlpayment = "SELECT gateway_type,is_main FROM mst_options WHERE app_id=".$app_id." and tab_id=".$data["tab_id"]." AND is_main ='1' order by gateway_type desc";
                    $respayment = mysql_query($sqlpayment, $conn);
                    $respayment2 = mysql_query($sqlpayment, $conn);
                    $gatewayid = mysql_result($respayment2, 0, 0);
                    //echo $gatewayid." Gateway id".mysql_num_rows($respayment);
                    if(mysql_num_rows($respayment) > 1) 
                    {
                        while($qrypayment = mysql_fetch_array($respayment))
                        {
                            if($qrypayment["gateway_type"]=='4'){?>
                            <input type="radio" name="payment" id="cash" value="4" checked />
                            <label for="cash"><?=$lngcash?></label>
                        <?     } 
                            if($qrypayment["gateway_type"]=='1'){?>
                            <input type="radio" name="payment" id="paypal" value="1" checked />
                            <label for="paypal"><?=$lngcreditcard?></label>
                    <?         } 
                        }
                    } else if($gatewayid=='4'){?>
                              <input type="hidden" name="payment" value="4"/>
                            <?=$lngcash?>
                <?  } else if($gatewayid=='1'){?>
                            <input type="hidden" name="payment" value="1"/>
                            <?=$lngcreditcard?>
                <?  } ?>
                    </fieldset>
                </div>
                <input type="hidden" name="total" id="total" value="<?=$total?>"  />
                <input type="hidden" name="totaltax" id="totaltax" value="<?=$totaltax1?>"  />
                <div data-role="fieldcontain">
                 <label for="fname"><?=$lngfirstname?> <span style="color: red">*</span></label>
                 <input type="text" name="fname" id="fname" required placeholder="<?=$lngfirstname?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="lname"><?=$lnglastname?> <span style="color: red">*</span></label>
                 <input type="text" name="lname" id="lname" required placeholder="<?=$lnglastname?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="pnum"><?=$lngphoneno?> <span style="color: red">*</span></label>
                 <input type="text" name="pnum" id="pnum" required placeholder="<?=$lngphoneno?>" value=""  />
                </div>
                <div class="deliverycheck">
                <? if (($data['deliver']=='yes' && !$data['dinein'] )  || (!$qryrest["takeout"] && !$qryrest["dinein"])) {?>
                <div data-role="fieldcontain">
                 <label for="addr1"><?=$lngstreetadd1?> <span style="color: red">*</span></label>
                 <input type="text" name="addr1" id="addr1" required placeholder="<?=$lngstreetadd1?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="addr2"><?=$lngstreetadd2?></label>
                 <input type="text" name="addr2" id="addr2" placeholder="<?=$lngstreetadd2?>" value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="city"><?=$lngcity?> <span style="color: red">*</span></label>
                 <input type="text" name="city" id="city" placeholder="<?=$lngcity?>" required value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="state"><?=$lngstate?> <span style="color: red">*</span></label>
                 <input type="text" name="state" id="state" placeholder="<?=$lngstate?>" required value=""  />
                </div>
                <div data-role="fieldcontain">
                 <label for="zipcode"><?=$lngpostcode?> <span style="color: red">*</span></label>
                 <input type="text" name="zipcode" id="zipcode" required placeholder="<?=$lngpostcode?>" value=""  />
                </div>
                <? } ?>
                <div data-role="fieldcontain">
                 <label for="email"><?=$lngemail?> <span style="color: red">*</span></label>
                 <input type="email" name="email" id="email" required placeholder="<?=$lngemail?>" value=""  />
                </div>
                </div>
                <div>
                <fieldset>
                        <div><button type="submit"  id="action" name="action" value="submit" data-theme="a"><?=$lngcheckout?></button></div>
                </fieldset>
                </div>
                </form>
                
            <?    } else { ?>
                <a href="?p=ordermenu&<?php echo $PASS_PARAMS; ?>"><div align="center"><p style="font-size:12px; text-decoration:none"><?=$lngnoorder?></p><br><img src="images/addcart.png" /></div></a>
            <?     }?>
            </p>
            <script>
            $('input[type=text]').blur(function(){
                $(this).val($.trim($(this).val()));
            });
            </script>
            <script type="text/javascript">
			$(document).on("pageshow", "#orderingsystem", function() {
				$( "#cartform" ).validate({
					errorPlacement: function( error, element ) {
						error.insertAfter( element.parent() );
					}
				});
			});
			</script>
        
        </div>
       </div>
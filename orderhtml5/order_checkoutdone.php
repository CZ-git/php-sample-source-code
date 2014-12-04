<?php
//echo $_SERVER["QUERY_STRING"];
if($_SESSION['orderstr'] || $data['orderstr']) {
	if($_SESSION['orderstr'] == "") $_SESSION['orderstr'] = $data['orderstr'];
	 
	$sql = "SELECT  * FROM  `orders` WHERE order_str = '".$_SESSION['orderstr']."' and order_state = '0' LIMIT 0,1";
	$res = mysql_query($sql, $conn);
	$qry = mysql_fetch_array($res);
	
	include_once("order_checkoutdone_email.php");
	include_once("order_print.php");
	include_once("ordering_base_init.php");
	
?>
	

	<div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
        	<ul data-role="listview">
				<li data-role="list-divider" style="text-align:center"><?=$lngordersuccess?></li>
                <? if($qry["paid"]=='1') {?>
				<li data-role="list-divider" style="text-align:center"><?=$lngtotpaid." ".$qry["currency"]; ?> <?php echo $qry["paid_amount"]; ?></li>
				<li data-role="list-divider" style="text-align:center"><?=$lngtransacid." ".$qry["transaction_id"]; ?></li>
                <? } ?>
           	</ul>
            <br />
            <br />
            <a href="?p=ordermenu&<?php echo $PASS_PARAMS; ?>" data-role="button" style="white-space:normal;"><? printf($lngthankmsg,$main_info["lead_time"]);?></a>
		</div>
    </div>
<? } else {?>
    
	<div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
        	<ul data-role="listview" data-divider-theme="d">
				<li data-role="list-divider" style="text-align:center"><?=$lngorderunsuccess?></li>
           	</ul>
            <a href="?p=ordermenu&<?php echo $PASS_PARAMS; ?>" data-role="button"><?=$lngorderfresh?></a>
		</div>
    </div>
<? } ?>    
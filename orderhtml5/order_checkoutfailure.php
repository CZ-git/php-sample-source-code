<?php
//echo $_SERVER["QUERY_STRING"];
if($_SESSION['orderstr']) {
	$sql = "UPDATE orders SET order_state = '5' WHERE order_str = '".$_SESSION['orderstr']."' AND tab_id='$data[tab_id]'";
	$res = mysql_query($sql, $conn);
}
?>
    <div data-role="header" data-theme="a" style="opacity:0.8;">
	<h1><? echo $main_info['restaurant_name'];?></h1>
    </div><!-- /header -->

	<div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
        	<ul data-role="listview" data-dividertheme="d">
				<li data-role="list-divider" style="text-align:center"><?=$lngorderfail?></li>
           	</ul>
            <a href="?p=cart&<?php echo $PASS_PARAMS; ?>" data-role="button"><?=$lngtryagain?></a>
		</div>
    </div>
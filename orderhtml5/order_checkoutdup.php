<?php
if($_SESSION['orderstr'])
{
	$sql = "UPDATE orders SET order_state = '5' WHERE order_str = '".$_SESSION['orderstr']."' AND tab_id='$data[tab_id]'";
	$res = mysql_query($sql, $conn);
	
	include_once("ordering_base_init.php"); 
}
?>
	<div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
        	<ul data-role="listview" data-dividertheme="d">
				<li data-role="list-divider" style="text-align:center"><?=$lngorderalready?></li>
           	</ul>
            <a href="?p=ordermenu&<? echo $PASS_PARAMS; ?>" data-role="button"><?=$lngorderfresh?></a>
		</div>
    </div>
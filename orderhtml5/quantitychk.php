<?php
	include_once("dbconnect.inc");
	$quantity = $_REQUEST['quantity'];
	$orderno = $_REQUEST['orderno'];
	$query = mysql_query("UPDATE `orders` SET `quantity`=$quantity WHERE `id` = $orderno");
	echo "<span id='quantitychk'>$quantity</span>";
?>


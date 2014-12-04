<?php
	include_once("dbconnect.inc");
	session_start("restwidg_id");
	$orderno = $_REQUEST['orderno'];
	if(isset($_SESSION['orderstr'])) {
		$sqldel = "delete from orders where	id = '$orderno' AND order_str='$_SESSION[orderstr]'";
		$resdel = mysql_query($sqldel, $conn);
	}
?>


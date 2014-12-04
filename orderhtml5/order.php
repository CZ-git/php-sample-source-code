<?
if($action == "submit") {
	include_once("order_ordersubmit.php");
} else if($action == "add_to_cart")	{
	include_once("order_add2cart.php");
} else if($action == "successful") {
	include_once("order_checkoutdone.php");
} else if($action == "checkoutfailure") {
	include_once("order_checkoutfailure.php");
} else if($action == "orderduplicate") {
	include_once("order_checkoutdup.php");	 
} else if($action == "cancelorder") {
?>
	<script language="javascript">
		document.location.href = "?p=cart&<?=$PASS_PARAMS; ?>";
	</script>
<?
}
else {
		include_once("order_itemdetail.php");
}
?>
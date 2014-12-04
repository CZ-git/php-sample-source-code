<?php	include_once("logic.php");	?>

<!DOCTYPE html>
<html> 

<head><?php include_once("ordering_cssjs.php");?></head>

<body> 

<div data-role="page"  <? if (isset($BACKGROUND_IMAGE) && $BACKGROUND_IMAGE !='') {?>style="background:url(<?php echo $BACKGROUND_IMAGE; ?>)" <? }?> id="orderingsystem">
<?php
	include_once "header.php";
	switch($page) {
	
		//	NEW PAGE TEST CASE
		case "orderloc" :
			include "orderloc.php";
			break;
		//	ENDS
		
		case "ordermenu" :
			include "ordermenu.php";
			break;
		//	ENDS
		
		case "cart" :
			include "cart.php";
			break;
		//	ENDS
		
		case "error" :
			include "error.php";
			break;
		//	ENDS
		
		case "order" :
			include "order.php";
			break;
		// ENDS
		
		case "orderitem" :
			include "orderitem.php";
			break;
		// ENDS
		
		default :
			include "orderloc.php";
			break;
	}
	?>
	<!-- /content -->

</div><!-- /page -->

</body>
</html>
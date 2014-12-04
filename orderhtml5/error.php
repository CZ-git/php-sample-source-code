<?php
	
	$error_title = "";
	$error_message = "";
	
	if($data[type] == "1") {
		$error_title = "Service is not ready.";
		$error_message = "<p>We are sorry.</p><p>The tab is not fully ready for online-service yet.</p>";
	}
	
	
?>
<!DOCTYPE html> 
<html> 
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<title>Sorry. Service is not ready yet</title> 
	<?php include "ordering_cssjs.php" ?>
	</head> 
<body> 
<div data-role="page" id="orderingsystem">   
    <div data-role="header" data-theme="a" style="opacity:0.8;">
		<h1><?php echo $error_title; ?></h1>
	</div><!-- /header -->

	<div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
			<?php echo $error_message; ?>
     	</div>
	</div><!-- /content -->
	
</div><!-- /page -->

</body>
</html>
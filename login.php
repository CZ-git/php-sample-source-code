<?php
	session_start("mob_app");
	if(isset($_SESSION['appcode'])) { session_unset("mob_app"); header("Location: login.php");/*session_start("mob_app");*/}
	$path = pathinfo($_SERVER['PHP_SELF']);
	$_SESSION['path'] = $path['dirname'];
	$path = $_SESSION['path'];
	/*if(isset($_SESSION['appcode'])) { header("Location: $path"); } else {*/
    if (isset($_GET['action']) && $_GET['action'] != "")
        $action = htmlspecialchars($_GET['action'], ENT_QUOTES);
    else
        $action = "";
?>
<?php if ($action == "submit" && isset($_GET['appcode']) && $_GET['appcode'])
	{
			include_once "dbconnect.inc";
			// CHECK THE APP RECORD
			
			$appname = $_GET['appcode'];
			$sql = "select id,is_active,code from apps where code = '".addslashes($appname)."' LIMIT 1";
			$res = mysql_query($sql, $conn);
			if (mysql_fetch_array($res)) 
			{
				session_start("mob_app"); 
				$_SESSION['appcode'] = $appname; // store session data
				$_SESSION['appid'] = mysql_result($res, 0, 0); // store session data
				//echo "views = ". $_SESSION['appcode'] ." ". $_SESSION['appid'];
			  	header("Location: $path");
  			 	exit;
			} 
			else
			{
				header("Location: login_once.php");
  			 	exit;
			}
	}
	else
	{
?>
<!DOCTYPE html> 
<html> 
<head> 
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1"> 
	<title>Business App</title>
	<link rel="stylesheet"  href="//code.jquery.com/mobile/1.4.3/jquery.mobile-1.4.3.min.css" />
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script src="//code.jquery.com/mobile/1.4.3/jquery.mobile-1.4.3.min.js"></script>
    </head> 
<body> 
<div data-role="page" style="background:#fff" > 
	
	<div data-role="header" data-position="fixed"> 
		<h1>Login to the HTML5 Website</h1> 
	</div> 
	
	<div data-role="content"> 
	<p>Enter the code that you have been given below to view the demo</p> 
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" data-ajax="false"> 
				<fieldset> 
					<div data-role="fieldcontain" style="border-bottom:none !important"> 
						<label for="App" class="select" style="text-align:center">App Code</label> 
						<input type="text" name="appcode" id="appcode" value=""/>
					</div> 
					<button type="submit" data-theme="c" name="action" value="submit">Load Demo</button> 
				</fieldset> 
			</form> 
    <p>Once viewing a demo you can return to this app by shaking your device</p> 
	</div> 
	
	<div data-role="footer" data-position="fixed" data-theme="a"> 
		<div data-role="navbar"> 
			<ul> 
                <li><a href="#" class="ui-btn-active ui-state-persist" data-icon="gear" data-iconpos="top">Preview App</a></li> 
			</ul> 
		</div><!-- /navbar --> 
	</div><!-- /footer --> 
</div><!-- /page --> 
<?php } /*}*/?>
</body> 
</html>

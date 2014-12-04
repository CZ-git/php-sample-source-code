<?php
	include_once("dbconnect.inc");
	include_once("ray_model/common.php");
	// Restaurant Open/Close Check
	date_default_timezone_set('GMT');
	$locationid = $_REQUEST['location'];
	$restid = $_REQUEST['restaurantid'];
	$locs = get_tab_location($conn, $locationid, 'id');
	$locs[0][timezone_value] = time() + ($locs[0][timezone_value] * 60 * 60);
	$timeinmins = (date("G",$locs[0][timezone_value]))*60+date("i",$locs[0][timezone_value]);
	$sql = "SELECT open_time, close_time, more_time FROM `restaurant_time` where restaurant_id=$restid and day = '".date("l",$locs[0][timezone_value])."'";
	//echo $locs[0][timezone_value].' Time in mins'.$timeinmins.
	$res = mysql_query($sql, $conn);
	$rest_opentime =  mysql_result($res, 0, 0);
	$rest_closetime =  mysql_result($res, 0, 1);
	$rest_moretime =  json_decode(mysql_result($res, 0, 2));
	//echo "rest opentime".$rest_opentime."rest closetime".$rest_closetime."rest moretime".$rest_moretime;
	$_SESSION[restaurantopen] = false;
	if ($timeinmins >= $rest_opentime && $timeinmins < $rest_closetime)
	{
		$_SESSION[restaurantopen] = true;
	}
	else
	{
	//"More Time ";
	//print_r($rest_moretime);
		foreach ($rest_moretime as $key => $val)
		{
			if ($timeinmins >= $val[0] && $timeinmins < $val[1])
			{
				$_SESSION[restaurantopen] = true;
			}
		}
	}
						if ($_SESSION[restaurantopen] == false)
						{
							echo "close";
						}
						else
						{
							echo "open";
						}
	/*
	$quantity = $_REQUEST['quantity'];
	$orderno = $_REQUEST['orderno'];
	$query = mysql_query("UPDATE `orders` SET `quantity`=$quantity WHERE `id` = $orderno");
	echo "<span id='quantitychk'>$quantity</span>";*/
?>


<?php
// PHP LOGIC FILE
// IT CONTAIN ALL FILES LOGIC, THERE VARIABLE OR FUNCTION USED FROM INCLUDE
// OR SQL QUERIES

$page = ( isset($_REQUEST['p']) ) ? $_REQUEST['p'] : '' ;
session_start("rest_id");
include_once "dbconnect.inc";
include_once "app.inc";
include_once "ray_model/common.php";
include_once "ray_model/ordering.php";
$data = make_data_safe($_REQUEST);
if ($data['label'] !='')
{
	$_SESSION['label_rest'] = $data['label'];
}
include_once "ordering_base.php";
$action = htmlspecialchars($data['action'], ENT_QUOTES);

switch($page) {

	//	NEW PAGE TEST CASE
	case "orderloc" :
		$uid = uniqid();
		if (!$data['orderstr'])
		{
			$data['orderstr'] = chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122));
		}
		$_SESSION['appcodeorder'] = $data[app_code];
		$_SESSION[orderbg] = $BACKGROUND_IMAGE;
		$locs = get_tab_location($conn, $data["tab_id"]);
		$_SESSION[loccount] = count($locs);
		if(count($locs) < 2 && $data['action'] != "cancelorder") {
		$loc_id = intval($locs[0]["id"]);
		header("Location: ?p=ordermenu&".$PASS_PARAMS."&loc_id=".$loc_id);
		exit; 
		}
		$_SESSION['rest_id'] = $main_info["id"];
		$_SESSION['rest_name'] = $main_info["restaurant_name"];
	break;
	//	ENDS
	
	case "ordermenu" :
		date_default_timezone_set('GMT');
		$locs = get_tab_location($conn, $data["loc_id"], 'id');
		$jsonObject="https://maps.googleapis.com/maps/api/timezone/json?location=".$locs[0][latitude].",".$locs[0][longitude]."&timestamp=".time()."&sensor=false";
	
		$object = json_decode(file_get_contents($jsonObject));
		$locs[0][timezone_value] = time() + ($locs[0][timezone_value] * 60 * 60);
		$timeinmins = (date("G",$locs[0][timezone_value]))*60+date("i",$locs[0][timezone_value])+$object->dstOffset/60;
		$sql = "SELECT open_time, close_time, more_time FROM `restaurant_time` where restaurant_id=".$main_info[id]." and day = '".date("l",$locs[0][timezone_value])."'";
		$res = mysql_query($sql, $conn);
		$rest_opentime =  mysql_result($res, 0, 0);
		$rest_closetime =  mysql_result($res, 0, 1);
		$rest_moretime =  json_decode(mysql_result($res, 0, 2));
		$_SESSION[restaurantopen] = false;
		
		if ($timeinmins >= $rest_opentime && $timeinmins < $rest_closetime)
		{
			$_SESSION[restaurantopen] = true;
		}
		else
		{
			foreach ($rest_moretime as $key => $val)
			{
				if ($timeinmins >= $val[0] && $timeinmins < $val[1])
				{
					$_SESSION[restaurantopen] = true;
				}
			}
		}
		$menus = get_ordering_menus($conn, $app_id, $data[tab_id], "1,2");
	break;
	
	case "cart" :
		$rest_id = $main_info["id"];
    		$qryrest = $main_info;

    if(empty($_GET[loc_id]))
    {
    	header("Location: ?".$PASS_PARAMS);
    }
    if ($qryrest["is_delivery"] == 1 && $qryrest[delivery_radius] > 0)
    {
        $sql = "SELECT latitude, longitude FROM tab_locations WHERE id = ".$_GET[loc_id];
        $res = mysql_query($sql, $conn);
          $locs[latitude] = mysql_result($res, 0, 0);
        $locs[longitude] = mysql_result($res, 0, 1);
        $userradius = distVincenty($_SESSION[latitude], $_SESSION[longitude], $locs[latitude], $locs[longitude]);
        
        if ($userradius > ( $qryrest[delivery_radius] * 1000 ) )
        {
            $qryrest["is_delivery"] = 0;
            $notinrange = 1;
        }
    }
    if($data[delete]=='yes')
    {
        $sqldel = "delete from orders where    id = ".$data[id];
        $resdel = mysql_query($sqldel, $conn);
    }
    
    if($action=='submit')
    {
        if(isset($data['orderstr'])) {
            
            $_SESSION['totalcharge'] = $data['total'];
            $_SESSION['totaltax'] = $data['totaltax'];
            
            if ( $_SESSION['orderstr'] == '' )
                $_SESSION['orderstr'] = $data['orderstr'];
            
            // ---------------------------------------------------------------
            // Create new record with order_type = 0, for tax details...
            // ---------------------------------------------------------------
            
            $tax_detail_id = "";
            $sql = "SELECT id FROM orders WHERE order_type = '0' AND order_str = '".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"];
            $res_sql = mysql_query($sql, $conn);
            if(mysql_num_rows($res_sql) > 0) {
                $tax_detail_id = mysql_result($res_sql, 0, 0);
            }
            
            $set_v = " SET 
                order_str='$_SESSION[orderstr]', 
                tab_id='$data[tab_id]', 
                order_detail='".base64_decode($data[order_tax_details])."',
                app_id='$app_id',
                item_id='0', order_total='0', order_note='', order_state='3', loc_id='0', order_type='0'
                ";
            if(intval($tax_detail_id) > 0) {
                $sql = "UPDATE orders ".$set_v." WHERE id='$tax_detail_id'";
            } else {
                $sql = "INSERT INTO orders ".$set_v;
            }
            $res_sql = mysql_query($sql, $conn);
            
            
            // Delivery Address ...
            if(($data[deliver] == "1") || !($data[deliver])){
                $da_sql = " SET 
                    user_id = 0, 
                    first_name    = '$data[fname]', 
                    last_name = '$data[lname]',
                    address1 = '$data[addr1]',
                    address2 = '$data[addr2]', 
                    country = '',
                    city = '$data[city]', 
                    zipcode = '$data[zipcode]', 
                    state = '$data[state]', 
                    company = '', 
                    fax = '', 
                    type     = '1', 
                    phone = '$data[pnum]',
                    email = '$data[email]' ";    
            } else {
                $da_sql = " SET 
                    user_id = 0, 
                    first_name    = '$data[fname]', 
                    last_name = '$data[lname]',
                    address1 = '',
                    address2 = '', 
                    country = '',
                    city = '', 
                    zipcode = '', 
                    state = '', 
                    company = '', 
                    fax = '', 
                    type = '1', 
                    phone = '$data[pnum]',
                    email = '$data[email]' ";
            }
            $_SESSION['orderemail'] = $data[email];
            
            $sql = "SELECT delivery_address_id FROM orders WHERE order_type > 0 AND order_str = '".$_SESSION['orderstr']."' AND tab_id=".$data["tab_id"];
            $res_sql = mysql_query($sql, $conn);
            if(mysql_num_rows($res_sql) > 0) {
                $da_detail_id = mysql_result($res_sql, 0, 0);
            }
            if(intval($da_detail_id) > 0) {
                $da_sql = "UPDATE app_users_address ".$da_sql." WHERE id='$da_detail_id'";
                $res_sql = mysql_query($da_sql, $conn);
            } else {
                $da_sql = "INSERT INTO app_users_address ".$da_sql;
                $res_sql = mysql_query($da_sql, $conn);
                $da_detail_id = mysql_insert_id($conn);
            }
            
            // ---------------------------------------------------------------
            // Update Order type and delivery address...
            // ---------------------------------------------------------------
            $sqlupdate =     "UPDATE orders 
                            SET 
                            order_type = '$data[deliver]', 
                            delivery_address_id = '$da_detail_id'
                            WHERE
                            order_type > 0 AND order_str = '".$_SESSION['orderstr']."' and tab_id=".$data["tab_id"];
            $resupdate = mysql_query($sqlupdate, $conn);
                    
            header("Location: ?p=checkout&".$PASS_PARAMS."&payment=".$data["payment"]);
            exit;
        
        } else { 
            unset($_SESSION['totalcharge']); 
            header("Location: p=order&".$PASS_PARAMS."action=cancelorder");
            exit;
        }
    }

    
    $sqlorders = "SELECT * FROM `orders` WHERE order_type > 0 AND order_str='".$data['orderstr']."' AND tab_id=".$data["tab_id"]." group by order_detail,order_note";
    $resorders = mysql_query($sqlorders, $conn);
    if (mysql_num_rows($resorders) < 1) {
        include_once("ordering_base_init.php");
    }
		break;
	//	ENDS
	
	case "error" :
		include "include1.php";
		$data = make_data_safe($_GET);
	
		$error_title = "";
		$error_message = "";
		
		if($data[type] == "1") {
			$error_title = "Service is not ready.";
			$error_message = "<p>We are sorry.</p><p>The tab is not fully ready for online-service yet.</p>";
		}
		break;
	//	ENDS
	
	case "order" :
		
		if( isset($_GET[latitude]) && isset($_GET[longitude]))
		{
			$_SESSION["latitude"] = $_GET["latitude"];
			$_SESSION["longitude"] = $_GET["longitude"];
		}
		
		$item = get_ordering_item($conn, $data["item_id"]);
		$sz = get_ordering_item_sizes($conn, $item[id]);
		
		$menu = get_ordering_menu($conn, $item["menu_id"]);
		$item_open_times = get_item_serve_time($conn, $item["id"], $main_info["id"]);
		
		$main_info = get_restaurant_information($conn, $app_id, $data["tab_id"]);
		$main_open_times = get_restaurant_time($conn, $main_info["id"]);
		
		break;
	// ENDS
	
	case "orderitem" :
		$items = get_ordering_items($conn, $data["menu_id"], "menu_id", "1,2");
		$menu = get_ordering_menu($conn, $data["menu_id"]);
		break;
	// ENDS
	
	case "checkout" :
		
		$main_info = get_restaurant_information($conn, $app_id, $data["tab_id"]);
		$main_open_times = get_restaurant_time($conn, $main_info["id"]);
		
		$_SESSION['rest_id'] = $main_info["id"];
		$_SESSION['orderstr'] = $data["orderstr"];
		
		$qryrest = $main_info;
		
		if($data["payment"] == '1')
		{
			header("Location: paypal/SetExpressCheckout.php?".$PASS_PARAMS);
			echo "paypal/SetExpressCheckout.php?".$PASS_PARAMS;
			exit;
		}
		else if($data["payment"] == '3')
		{
			header("Location: anet_php_sdk/Authorizepayment.php?".$PASS_PARAMS);
			exit;
		}
		else if($data["payment"] == '4')
		{
			$dateplaced = date_create();
			$sqlorder = 	"UPDATE orders 
							SET
							order_state = '0',
							checkout_method = '4',
							placed_on =  '".date_format($dateplaced, 'Y-m-d H:i:s')."'					
							WHERE order_str = '".$_SESSION['orderstr']."' and tab_id=".$data["tab_id"];
			$resorder = mysql_query($sqlorder, $conn);
			header("Location: ?p=order&".$PASS_PARAMS."&action=successful");
			exit;
		}
		else if($data["payment"] == '2')
		{
			header("Location: google/googlecheckout.php?".$PASS_PARAMS);
			exit;
		}
		else
		{
			if(isset($data['orderstr'])) {
				$sql = "UPDATE orders SET order_state = '2' WHERE order_str = '".$_SESSION['orderstr']."'";
				$res = mysql_query($sql, $conn);
			}
			
			unset($_SESSION['orderstr']);
			unset($_SESSION['totalcharge']);
					
			unset($data['orderstr']);
			include("ordering_base_params.php");
			
			header("Location: index.php?p=ordermenu&".$PASS_PARAMS);
			exit;
		}
		
		break;
		
	default :
		include "content-orderloc.php";
		break;
}

<?php
//ini_set ("display_errors", "1"); // At the top of the page for page errors.
//error_reporting(E_ALL); // At the top of the page for page errors 
include "dbconnect.inc";
include "app.inc";

function array_search2d($needle, $haystack) {
    for ($i = 0, $l = count($haystack); $i < $l; ++$i) {
        if (in_array($needle, $haystack[$i])) return $i;
    }
    return false;
}

function array_search2d_byfield($field, $needle, $haystack) {
    for ($i = 0, $l = count($haystack); $i < $l; ++$i) {
        if($haystack[$i][$field] == $needle) return $i;
    }
    return false;
}

function ViewControllerMapping($qry, $data) {
		$this_item_home = array("ViewController" => $qry["view_controller"]);
		
		global $conn;
		global $app_id;

		//Make the restaurant exactly same with Webviews
		if ($qry["view_controller"] == "RestaurantBookingViewController") {
			$qry["view_controller"] = "WebViewController";	
			$this_item_home["ViewController"] = "WebViewController";
			$this_item_home["Main_ViewController"] = "RestaurantBookingViewController";
		}

		if ($qry["view_controller"] == "WuFooViewController") {
			$qry["view_controller"] = "WebViewController";	
			$this_item_home["ViewController"] = "WebViewController";
			$this_item_home["Main_ViewController"] = "WuFooViewController";
		}
		
		if ($qry["view_controller"] == "InfoSectionViewController") {  // This is now redundant for 1-tier; if 2-tier is set it kicks in though
			$sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
			$res2 = mysql_query($sql, $conn);
			if (mysql_num_rows($res2) == 1) {
				$this_item_home["ViewController"] = "InfoItemsViewController";
				$this_item_home["Main_ViewController"] = "InfoSectionViewController";
				$this_item_home["section_id"] = mysql_result($res2, 0, 0);
			}
			else {
				$this_item_home["tab_id"] = $qry["id"];
			}
		} elseif ($qry["view_controller"] == "InfoItemsViewController") {
                        $sql = "select id from info_categories where tab_id = '$qry[id]' order by seq, id";
                        $res2 = mysql_query($sql, $conn);
                        if (mysql_num_rows($res2) == 1) {
                                $this_item_home["section_id"] = mysql_result($res2, 0, 0);
			}
		} elseif ($qry["view_controller"] == "InfoDetailViewController") {
                        $sql = "select i.id, i.description from info_categories c, info_items i
                                where c.id = i.info_category_id 
                                and c.tab_id = '$qry[id]'
				order by c.seq, c.id, i.seq, i.id";
                        $res2 = mysql_query($sql, $conn);
                        if (mysql_num_rows($res2) == 1) {
                                $this_item_home["item_id"] = mysql_result($res2, 0, 0);
			}
		} elseif ($qry["view_controller"] == "LocationViewController") {
			$sql = "select * from app_locations
				where app_id = '$app_id'";
			$res2 = mysql_query($sql, $conn);
			if (mysql_num_rows($res2) > 1) {
				$this_item_home["ViewController"] = "LocationListViewController";
				$this_item_home["Main_ViewController"] = "LocationViewController";
			}
		} elseif ($qry["view_controller"] == "WebViewController") {
			$sql = "select url from web_views
                                where app_id = '$app_id'
                                and tab_id = '$qry[id]'
                                order by seq, url";
            $res2 = mysql_query($sql, $conn);
			if (mysql_num_rows($res2) > 1) {
					$this_item_home["ViewController"] = "WebTierViewController";
					$this_item_home["Main_ViewController"] = "WebViewController";
					$this_item_home["tab_id"] = $qry["id"];
			} else if (mysql_num_rows($res2)) {
				$this_item_home["URL"] = mysql_result($res2, 0, 0);
				if((strpos($this_item["URL"], "http://") === false) && (strpos($this_item["URL"], "https://") === false)) {
					$this_item_home["URL"] = "http://" . $this_item["URL"];
				}
			} else {
				$this_item_home["URL"] = "http://www.google.com/";
			}

		} elseif ($qry["view_controller"] == "RSSFeedViewController") {
			$this_item_home["tab_id"] = $qry["id"];
		} else if ($qry["view_controller"] == "HomeViewController") {
			$a = get_app_record($conn, $app_id);
			/*$keyinfo = array(
				"CallButton" => "onbtncall", 
				"DirectionButton" => "onbtndirection",  
				"TellFriendButton" => "onbtntell"
			);
			foreach($keyinfo AS $key => $val) {
				$this_item_home[$key] = ($a[$val] == '1')?'YES':'NO';
			}*/
		} else if ($qry["view_controller"] == "GalleryCoverFlowViewController") {
			// Okay, just go ahread.
		} else if ($qry["view_controller"] == "GalleryViewController") {
			// we need to check if this is for coverflow or grid view
			
			$v3 = unserialize($qry['value3']);
			if(is_array($v3)) {
				$type = $v3['type'];
				$isdesc = $v3['isdesc'];
			} else {
				$type = $qry['value3'];
				$isdesc = "0";
			}

			if($type == '1') {
				$this_item_home["ViewController"] = "GalleryCoverFlowViewController";
				$this_item_home["Main_ViewController"] = "GalleryViewController";
			}
			
			$v2 = unserialize($qry['value2']);
			if(!is_array($v2)) $v2 = array();
			
			
			foreach($v2 AS $k => $v) {
				/*
				$more_this_item_el = array();
				foreach($this_item AS $tk => $tv) {
					$more_this_item_el[$tk] = $tv;
				}
				*/
				if(($v["active"] == "1") && ($k == "flickr")) {
					$this_item_home["ViewController"] = "FlickrViewController";
					$this_item_home["Main_ViewController"] = "GalleryViewController";
					/*
					$more_this_item_el["ViewController"] = "FlickrViewController";
					$more_this_item_el["tab_id"] = $qry["id"];
					$more_this_item[] = $more_this_item_el;
					*/
				} else if(($v["active"] == "1") && ($k == "picasa")) {
					$this_item_home["ViewController"] = "PicasaViewController";
					$this_item_home["Main_ViewController"] = "GalleryViewController";
					/*
					$more_this_item_el["ViewController"] = "FlickrViewController";
					$more_this_item_el["tab_id"] = $qry["id"];
					$more_this_item[] = $more_this_item_el;
					*/
				}
			}
		}
		
		// Add tab_id to everything now, even though not every view controller may support it
		$this_item_home["tab_id"] = $qry["id"];

		return $this_item_home;
}


$data = make_data_safe($_GET);
$a = get_app_record($conn, $app_id);

	include("home_v4.php");
	exit;
	

$sql = "select *
        from app_locations l, apps a
        where l.app_id = a.id and l.app_id = '$app_id'
        order by l.seq";
$res = mysql_query($sql, $conn);
$res1 = mysql_query($sql, $conn);
//geting the data for even and odd row colour to send in the home sub tabs
$qry_colour1 = mysql_fetch_array($res1);

$odd_row_col= $qry_colour1['odd_row_color'];
$even_row_col= $qry_colour1['even_row_color'];

/*
if($_REQUEST['device']!='' and $_REQUEST['device']=='iphone') {
	$image_file = "../../uploads/images/$app_id/home.jpg";
} else if ($_REQUEST['device']!='' and $_REQUEST['device']=='ipad') {
	$image_file = "../../uploads/images/$app_id/ipad/home.jpg";
	if (file_exists($image_file)) {
	//do nothing
	} else {
		$image_file = "../../uploads/images/$app_id/home.jpg";
	}
} else {
	$image_file = "../../uploads/images/$app_id/home.jpg";
}
*/
while ($qry = mysql_fetch_array($res)) {
  if (!$add) $add = $qry;
  $addresses[] = array("address_1" => $qry["address_1"],
                       "address_2" => $qry["address_2"],
                       "city" => $qry["city"],
                       "state" => $qry["state"],
                       "zip" => $qry["zip"],
                       "telephone" => $qry["telephone"],
                       "email" => $qry["email"],
                       "website" => $qry["website"],
                       "latitude" => $qry["latitude"],
                       "longitude" => $qry["longitude"]);
}
//////////////////
$sql = "select t.*,
                        vc.value1_field,
                        vc.value2_field,
                        vc.value3_field
		from app_tabs t
        	left join view_controllers vc
        	on t.view_controller = vc.name
		where t.app_id = '$app_id'
		and t.is_active > 0
		order by t.seq";
$res = mysql_query($sql, $conn);

if ( mysql_num_rows($res) == 0 ) {  // Let's hope this never happens
	$feed[] = array("tab_label" => "No tabs");
} else {
	  while ($qry = mysql_fetch_array($res)) {

		foreach ($qry as $key => $val) {
			// $qry[$key] = preg_replace('/[^(\x20-\x7F)]*/','', $val);
			$qry[$key] = preg_replace('/[(\x0-\x1F)]*/','', $val);
		}
		$itemhome = ViewControllerMapping($qry, $data);

		 $feed_home[] = $itemhome;
	  }
	}
 
 ///////////////
//geting the subtab record
if(isset($data[tab_id]) && ($data[tab_id] != '')) {
	$sql_homesub = "SELECT sub.*, a.tab_label, a.view_controller AS actual_view FROM home_sub_tabs as sub LEFT JOIN app_tabs AS a ON sub.link_tab_id = a.id WHERE sub.app_id = '$app_id' and sub.is_active<>'0' AND sub.tab_id = $data[tab_id]  order by sub.seq limit 6";
} else {
	$sql_homesub = "SELECT sub.*, a.tab_label, a.view_controller AS actual_view FROM home_sub_tabs as sub LEFT JOIN app_tabs AS a ON sub.link_tab_id = a.id WHERE sub.app_id = '$app_id' and sub.is_active<>'0' AND sub.tab_id <> 0  order by sub.seq limit 6";
}
  
$res_homesub = mysql_query($sql_homesub, $conn);
 
if(mysql_num_rows($res_homesub)>0) {
	
	while ($qry_homesub = mysql_fetch_array($res_homesub)) {
	 	
		$myArray = $feed_home;
		$searchTerm = $qry_homesub["link_tab_id"]; 
		//$qry_homesub["actual_view"];
		
		echo $qry_homesub["link_tab_id"];
		print_r($myArray);


		if (false !== ($pos = array_search2d_byfield("tab_id", $searchTerm, $myArray))) {
		   // echo $searchTerm . " found at index " . $pos."----".$feed_home[$pos]['ViewController']."<br>";
		   $vcontroller=$feed_home[$pos]['ViewController'];
		}  
		 
		  //Mike Function ends/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		  			 
		 
		  $home_sub_tabs[] = array(
							  "tab_id" => $qry_homesub["link_tab_id"],
		  					  "link_tab_id" => $qry_homesub["tab_id"],	
		                      "is_active" => $qry_homesub["is_active"],
		                      "ViewController" => $vcontroller, //$qry_homesub["actual_view"],
		                      "TabLabelFont" => $qry_homesub["TabLabelFont"],
		                      "TabLableTextColor" => $qry_homesub["TabLableTextColor"],
		                      "TabLabelText" => $qry_homesub["TabLabelText"],
		                      "TabImage" => $qry_homesub["TabImage"],
							  "OddRowColor" => $odd_row_col,
							  "EvenRowColor" => $even_row_col,
		                      "NavigationController" => $qry_homesub["NavigationController"],
		                      "LastUpdated" => $qry_homesub["LastUpdated"],
							  "Custom_Icon" => $qry_homesub["Custom_Icon"],
							  "TabLableTextBackgroundColor" => $qry_homesub["TabLableTextBackgroundColor"],
		                      "seq" => $qry_homesub["seq"] 
		 					  );
						  
	}

 

}
 
// Retrieve subtab options
$t = get_app_tab_record($conn, $data["tab_id"]);
$v3 = unserialize($t["value3"]);
if(!is_array($v3)) {
	$v3 = array('ipad' => '0');
}
if(!isset($v3['ipad'])) {
	$v3['ipad'] = '0';
}
// - end
 

if ((!$add["app_store_url"])||($add["app_store_url"] == '')) {
	$add["app_store_url"] = "http://www.appstore.com";
}
  
if(mysql_num_rows($res_homesub)>0) {
  
	$this_item = array("name" => $add["name"],
				"telephone" => $add["telephone"],
				"latitude" => $add["latitude"],
				"longitude" => $add["longitude"],
				"app_store_url" => $add["app_store_url"],
				"addresses" => $addresses,
				"only4ipad" => ($v3['ipad']=='1')?'1':'0',
				"home_sub_tabs" => $home_sub_tabs,
				"facebook_key" => "f3204916c19fe3a3649f3ac322c53f13",
				"facebook_secret" => "27829fd8a3993c2823e351764888e395",
				"twitter_key" => "w7v7fdJfJic6Xk8OmLSM0g",
				"twitter_secret" => "ymoXT5sdZlhppJH0nI3dNl48JubUaQoLtU3w6mAk");
} else {
	$this_item = array("name" => $add["name"],
				"telephone" => $add["telephone"],
				"latitude" => $add["latitude"],
				"longitude" => $add["longitude"],
				"app_store_url" => $add["app_store_url"],
				"addresses" => $addresses,
				"facebook_key" => "f3204916c19fe3a3649f3ac322c53f13",
				"facebook_secret" => "27829fd8a3993c2823e351764888e395",
				"twitter_key" => "w7v7fdJfJic6Xk8OmLSM0g",
				"twitter_secret" => "ymoXT5sdZlhppJH0nI3dNl48JubUaQoLtU3w6mAk");
}

// if ($qry["custom_button"]) {
  $button_file = "../../uploads/images/$app_id/button.png";
  if (file_exists($button_file)) {
    $image = base64_encode(file_get_contents($button_file));
    $this_item["CustomButton"] = $image;
  }
  
  
// Retrieve mobile bgs
$homeURL = "http://appsomen.com";
$mobile_images = array();
$sql = "SELECT * FROM images_bg WHERE device_type='0' AND detail_type='0' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
$res = mysql_query($sql, $conn);
$dir = "../../uploads/images/$app_id";

while ($qry = mysql_fetch_array($res)) {
	if(file_exists($dir."/".$qry["name"])) {
		$mobile_images[] = array(
			"path" => $dir."/".$qry["name"],
			"url" => $homeURL."/custom_images/".$data["app_code"]."/".$qry["name"]
		);
	}
}

if ($_REQUEST['device']!='' and $_REQUEST['device']=='ipad') {
	$ipad_images = array();
	$sql = "SELECT * FROM images_bg WHERE device_type='1' AND detail_type='0' AND detail_id='0' AND app_id='$app_id' AND is_removed = 0 ORDER BY seq DESC";
	$res = mysql_query($sql, $conn);
	$dir = "../../uploads/images/$app_id/ipad";

	while ($qry = mysql_fetch_array($res)) {
		if(file_exists($dir."/".$qry["name"])) {
			$ipad_images[] = array(
				"path" => $dir."/".$qry["name"],
				"url" => $homeURL."/custom_images/".$data["app_code"]."/ipad/".$qry["name"]
			);
		}
	}
	if(count($ipad_images) > 0) {
		$mobile_images = $ipad_images;
	}
}

if(count($mobile_images) > 0) {
	$image = base64_encode(file_get_contents($mobile_images[0]["path"]));
} else {
	$image = "";
}

	$this_item["image"] = $image;
	
	if(count($mobile_images) > 1) {
		$this_item["manyImages"] = "YES";
		$this_item["imagesInOrder"] = array();
		
		$limit = (count($mobile_images) > 5)? 5:count($mobile_images);
		for($i=0; $i<$limit; $i++) {
			$this_item["imagesInOrder"][] = $mobile_images[$i]["url"];
		}
	} else {
		$this_item["manyImages"] = "NO";
		$this_item["imagesInOrder"] = array();
	}


// }

$feed[] = $this_item;

$json = json_encode($feed);
header("Content-encoding: gzip");
echo gzencode($json);

?>

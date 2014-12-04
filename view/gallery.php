<?
$sql = "SELECT * from gallery_images
        WHERE app_id = '$app_id'
        and tab_id = '$tab_detail[id]'
        ORDER BY seq";
		//echo $sql;
		$res = mysql_query($sql, $conn); 
		$dir = findUploadDirectory($app_id) . "/gallery";
		$sql = "SELECT * FROM `app_tabs` where id=$tab_detail[id]";
		$res1 = mysql_query($sql, $conn); 
		$t = mysql_fetch_array($res1);
		$sett = unserialize($t['value2']);
		//print_r($sett);
		if($sett[flickr][active]==1)
		{
		$_SESSION[userid] = $sett[flickr][userid];
		$_SESSION[apikey] = $sett[flickr][apikey];
		include ("flickr.php");
		}
		else if($sett[picasa][active]==1)
		{
		$_SESSION[userid] = $sett[picasa][userid];
		include ("picasa.php");
		} else {
			$list_sql = "SELECT * from galleries
                WHERE app_id = '".$app_id."'
                and tab_id = '".$tab_detail[id]."'
                ORDER BY seq ASC";
            $list_res = mysql_query($list_sql, $conn);
            $gallery_list_rows = mysql_num_rows($list_res);
			if ($gallery_list_rows == "1")
			{
				$list_qry = mysql_fetch_array($list_res);
				$tab_detail[cat_id] = $list_qry["id"];
			}
			if(!empty($tab_detail[cat_id]))
			{
			include_once "gallery_images.php";
			}
			else
			{
			include_once "gallery_list.php";
			}
		}
?>
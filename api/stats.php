<?php

include_once "dbconnect.inc";
include_once "app.inc";

include_once "common_functions.php";
include_once "app_functions.php";


$data = make_data_safe($_REQUEST);

$data["timezone"] = doubleval($data["timezone"]);

if($data["action"] == "1") { // overal analytics
	// Should padd following param
	// id, tk
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$a = fetch_app($conn, 
										array(
											"id = " => "'$data[id]'",
										));
		$itune_id = $a["app_store_id"];
		
		$android_sku = $a['android_url'];
		if(preg_match("/(=)(.*)(layout)/", $android_sku, $matches)) {
			$android_sku = substr($matches[0], 1);
		} else {
			$android_sku = "";
		}
		
		$html5_id = "web".$a["id"];
		
		
		$lastweek = date("Y-m-d", strtotime(date("Y-m-d") . " - 7 day"));
		$lastmonth = date("Y-m-d", strtotime(date("Y-m-d") . " - 1 month"));
		$search_date = date("Y-m-d", strtotime(date("Y-m-d") . " - " . $days . " day"));
		
		$m_table = "app_figures";
		
		$sql = 'select IFNULL(SUM(downloads), 0) as week_downlaods from '.$m_table.' where itunes_id="'.$itune_id.'" and day >= "'.$lastweek.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$week_total = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as month_downlaods from '.$m_table.' where itunes_id="'.$itune_id.'" and day >= "'.$lastmonth.'"';
		$result = mysql_query($sql);
		$row = mysql_fetch_row($result);
		$month_total = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as all_time_downloads from '.$m_table.' where itunes_id="'.$itune_id.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$total = ($row[0])?$row[0]:"0";
		
		// Android
		$sql = 'select IFNULL(SUM(downloads),0) as week_downlaods from '.$m_table.' where itunes_id="'.$android_sku.'" and day >= "'.$lastweek.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$week_total_android = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as month_downlaods from '.$m_table.' where itunes_id="'.$android_sku.'" and day >= "'.$lastmonth.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$month_total_android = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as all_time_downloads from '.$m_table.' where itunes_id="'.$android_sku.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$total_android = ($row[0])?$row[0]:"0";
	
		// HTML5
		$sql = 'select IFNULL(SUM(downloads),0) as week_downlaods from '.$m_table.' where itunes_id="'.$html5_id.'" and day >= "'.$lastweek.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$week_total_html5 = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as month_downlaods from '.$m_table.' where itunes_id="'.$html5_id.'" and day >= "'.$lastmonth.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$month_total_html5 = ($row[0])?$row[0]:"0";
		
		$sql = 'select IFNULL(SUM(downloads),0) as all_time_downloads from '.$m_table.' where itunes_id="'.$html5_id.'"';
		$result = mysql_query($sql); 
		$row = mysql_fetch_row($result);
		$total_html5 = ($row[0])?$row[0]:"0";
		
		// Retrieve min, max Year-Date
		$sql = "SELECT day FROM $m_table WHERE itunes_id IN ('$itune_id', '$android_sku', '$html5_id') AND NOT(itunes_id = '') ORDER BY day LIMIT 1";
		$row = mysql_fetch_row(mysql_query($sql, $conn));
		if($row) {
			$min_date = substr($row[0], 0, 7);
		} else {
			$min_date = gmdate("Y-m");
		}
		
		$sql = "SELECT day FROM $m_table WHERE itunes_id IN ('$itune_id', '$android_sku', '$html5_id') AND NOT(itunes_id = '') ORDER BY day DESC LIMIT 1";
		$row = mysql_fetch_row(mysql_query($sql, $conn));
		if($row) {
			$max_date = substr($row[0], 0, 7);
		} else {
			$max_date = gmdate("Y-m");
		}
		
		
		$item = array(
			"error" => "0",
			"itune" => array($week_total, $month_total, $total),
			"android" => array($week_total_android, $month_total_android, $total_android),
			"html5" => array($week_total_html5, $month_total_html5, $total_html5),
			"min" => $min_date,
			"max" => $max_date,
		);
		
	} else {
		$item["error"] = "9";		
	}
	
} else if($data["action"] == "2") { // Details
	// Should padd following param
	// id, tk, year, month
	
	if(check_token($conn, $data["id"], $data["tk"])) {
		
		$a = fetch_app($conn, 
										array(
											"id = " => "'$data[id]'",
										));
		$itune_id = $a["app_store_id"];
		
		$android_sku = $a['android_url'];
		if(preg_match("/(=)(.*)(layout)/", $android_sku, $matches)) {
			$android_sku = substr($matches[0], 1);
		} else {
			$android_sku = "";
		}
		
		$html5_id = "web".$a["id"];
		
		$m_table = "app_figures";
		
		$dataArray_iphone = array();
		$dataArray_android = array();
		$dataArray_html5 = array();
		
		if($data["year"] && $data["month"]) {
			// Daily Stat for the month
			$range = getMonthRange($data["year"] . "-" . $data["month"]);
			
			$sql_iphone = 'SELECT day, downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $itune_id . '" AND day >= "' . $range[0] . '" AND day <= "' . $range[1] . '"';
			$sql_android = 'SELECT day, downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $android_sku . '" AND day >= "' . $range[0] . '" AND day <= "' . $range[1] . '"';
			$sql_html5 = 'SELECT day, downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $html5_id . '" AND day >= "' . $range[0] . '" AND day <= "' . $range[1] . '"';
			
			$result_iphone = mysql_query($sql_iphone);
			$result_android = mysql_query($sql_android);
			$result_html5 = mysql_query($sql_html5);

			// Empty array compose
			$dataempty = array();
			$d = intval(end(explode("-", $range[1])));
			$ym = substr($range[1], 0, 8);
			for($i = 1; $i <= $d; $i ++) {
				if($i < 10) {
					$dataempty[] = $ym . "0" . $i;
				} else {
					$dataempty[] = $ym . $i;
				}
			}
			
		} else if ($data["year"]) {
			// Monthly stat for the year
			$sql_iphone = 'SELECT SUBSTRING(day, 1, 7) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $itune_id . '" AND SUBSTRING(day, 1, 4)="' . $data["year"] . '" GROUP BY SUBSTRING(day, 1, 7)';
			$sql_android = 'SELECT SUBSTRING(day, 1, 7) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $android_sku . '" AND SUBSTRING(day, 1, 4)="' . $data["year"] . '" GROUP BY SUBSTRING(day, 1, 7)';
			$sql_html5 = 'SELECT SUBSTRING(day, 1, 7) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $html5_id . '" AND SUBSTRING(day, 1, 4)="' . $data["year"] . '" GROUP BY SUBSTRING(day, 1, 7)';
			
			$result_iphone = mysql_query($sql_iphone);
			$result_android = mysql_query($sql_android);
			$result_html5 = mysql_query($sql_html5);
			
			
			// Empty array compose
			$dataempty = array();
			for($i = 1; $i <= 12; $i ++) {
				if($i < 10) {
					$dataempty[] = $data["year"] . "-0" . $i;
				} else {
					$dataempty[] = $data["year"] . "-" . $i;
				}
			}
			
		} else {
			// Annual stat
			$sql_iphone = 'SELECT SUBSTRING(day, 1, 4) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $itune_id . '" GROUP BY SUBSTRING(day, 1, 4)';
			$sql_android = 'SELECT SUBSTRING(day, 1, 4) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $android_sku . '" GROUP BY SUBSTRING(day, 1, 4)';
			$sql_html5 = 'SELECT SUBSTRING(day, 1, 4) AS day, sum(downloads) AS downloads FROM ' . $m_table . ' WHERE itunes_id = "' . $html5_id . '" GROUP BY SUBSTRING(day, 1, 4)';
			
			$result_iphone = mysql_query($sql_iphone);
			$result_android = mysql_query($sql_android);
			$result_html5 = mysql_query($sql_html5);
			
			
			// Retrieve min, max Year-Date
			$sql = "SELECT day FROM $m_table WHERE itunes_id IN ('$itune_id', '$android_sku', '$html5_id') AND NOT(itunes_id = '') ORDER BY day LIMIT 1";
			$row = mysql_fetch_row(mysql_query($sql, $conn));
			if($row) {
				$min_year = substr($row[0], 0, 4);
			} else {
				$min_year = gmdate("Y");
			}
			
			
			// Empty array compose
			$dataempty = array();
			for($i = intval($min_year); $i <= intval(gmdate("Y")); $i ++) {
				$dataempty[] = "" . $i;
			}
			
			
		}
		
		if ($result_iphone) {
			while ($row = mysql_fetch_assoc($result_iphone)) {
				$day = $row["day"];
				$downloads = $row["downloads"];
				
				$dataArray_iphone[$day] = $downloads;
			}
		}
		
		if ($result_android) {
			while ($row = mysql_fetch_assoc($result_android)) {
				$day = $row["day"];
				$downloads = $row["downloads"];
				
				$dataArray_android[$day] = $downloads;
			}
		}
		
		if ($result_html5) {
			while ($row = mysql_fetch_assoc($result_html5)) {
				$day = $row["day"];
				$downloads = $row["downloads"];
				
				$dataArray_html5[$day] = $downloads;
			}
		}
		
		
		$iphone = array();
		$android = array();
		$html5 = array();
		
		$max = 0;
		
		for($i = 0; $i < count($dataempty); $i ++) {
			if(isset($dataArray_iphone[$dataempty[$i]])) {
				if($max < intval($dataArray_iphone[$dataempty[$i]])) $max = $dataArray_iphone[$dataempty[$i]];
				$iphone[$dataempty[$i]] = $dataArray_iphone[$dataempty[$i]];
			} else {
				$iphone[$dataempty[$i]] = 0;
			}
			
			if(isset($dataArray_android[$dataempty[$i]])) {
				if($max < intval($dataArray_android[$dataempty[$i]])) $max = $dataArray_android[$dataempty[$i]];
				$android[$dataempty[$i]] = $dataArray_android[$dataempty[$i]];
			} else {
				$android[$dataempty[$i]] = 0;
			}
			
			if(isset($dataArray_html5[$dataempty[$i]])) {
				if($max < intval($dataArray_html5[$dataempty[$i]])) $max = $dataArray_html5[$dataempty[$i]];
				$html5[$dataempty[$i]] = $dataArray_html5[$dataempty[$i]];
			} else {
				$html5[$dataempty[$i]] = 0;
			}
		}

		/*
		$item = array(
			"x-axis" => $dataempty,
			"iphone" => $iphone,
			"android" => $android,
			"html5" => $html5,
			"max" => "".$max,
		);
		*/
		
		$stat = false;
		for($p = 0; $p < count($dataempty); $p ++) {
			$stat[] = array("".$dataempty[$p], "".$iphone[$dataempty[$p]], "".$android[$dataempty[$p]], "".$html5[$dataempty[$p]]);
		}
		
		if($max > 0) {
			// Lets calculate beautiful 4x value
			$c = intval(log10($max));
			$c10 = pow(10, $c);
			$pref = ceil($max / $c10);
			
			while(($pref * $c10 % 4) != 0) {
				$pref ++;
			}
			
			$max = $pref * $c10;
			
		}
		
		$item = array(
			"error" => "0",
			"max" => "".$max,
			"data" => $stat,
		);
		
	} else {
		$item["error"] = "9";		
	}
	
}else {
	$item["error"] = "44";
	
}


$feed[] = $item;

$json = json_encode($feed);
//-------------------------------------------------------------------
// Remove null
//-------------------------------------------------------------------
$json = str_replace('":null', '":""', $json);

header("Content-encoding: gzip");
echo gzencode($json);


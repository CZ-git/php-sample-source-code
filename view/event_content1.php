	<div data-role="content" style="background:url(<?=$data[0][background]; ?>) no-repeat;"> 
	<ul data-role="listview" data-divider-theme="d">
<?
    $sql = "SELECT event_date, FROM_UNIXTIME(event_date,'%m.%Y') AS monyr
			FROM `events` 
			WHERE `tab_id` ='".$_SESSION[tab][id]."' and `app_id`='".$_SESSION[app_id]."' and isactive > 0
			GROUP BY monyr";
    $res = mysql_query($sql, $conn);
    
	while ($data = mysql_fetch_array($res)) 
	{
		$langs=explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$choice = $langs[0];
		$choice=substr($value,0,2);
		$codetocountry = country_code_to_locale($choice).".UTF-8";
		$loclang = setlocale(LC_ALL, $codetocountry);
		$date = strftime("%B %Y", $data["event_date"]);
		$monyr = $data["monyr"];
	?>			
					<li data-role="list-divider"><?=strtoupper($date);?></li>
					<? 				
							$sql2 = "select * From events
							where tab_id = '".$_SESSION[tab][id]."' and `app_id`='".$_SESSION[app_id]."' and FROM_UNIXTIME(event_date,'%m.%Y')='$data[monyr]' and isactive > 0
							order by event_date,from_hour, from_min";
							$res2 = mysql_query($sql2, $conn);		
							$countrow=0;
							while ($data2 = mysql_fetch_array($res2)) {
								$countrow++;
								$month = strtoupper(strftime("%h", $data2["event_date"]));
								$day = strftime("%d", $data2["event_date"]);
						?>
							<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?controller=EventsViewController&tab_id=<?=$_SESSION[tab][id]?>&cat_id=<?=$data2[id] ?>"><img src="images/events.png" style="padding:15px" title="events" height="60" width="60"/> <span class="ui-li-day"><? echo $day ?></span><span class="ui-li-month"><? echo $month ?></span><h3><? echo $data2["name"] ?></h3>
                            <p><? echo strip_tags($data2["description"]) ?></p></a></li>					
					 											  <? }   
	} ?>
            </ul> 
	</div>
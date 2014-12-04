<div data-role="content" style="background:#FFF; opacity:0.8;">
			<style>
			table { width:100%; }
			table caption { text-align:left;  }
			table thead th { text-align:left; border-bottom-width:1px; border-top-width:1px; }
			table th, td { text-align:left; padding:6px;} 
		</style>
        <table>
		  <caption><?=$lngnotopen?></caption>
		  <thead>
		    <tr>
		      <th scope="col"><?=$lngdays?></th>  
		      <th scope="col"><?=$lngfrom?></th>  
		      <th scope="col"><?=$lngto?></th>  
		    </tr>
		  </thead>
		  <tfoot>
		    <tr>
		      <td colspan="5"><? 
			  if($qryrest[brief_desc])
			  { 
			  echo "<strong>$lngdetail: </strong>".$qryrest[brief_desc]."<br><br>";
			  }
			  if($qryrest[cuisine_type])
			  {
			  echo "<strong>$lngcuisine: </strong>".$qryrest[cuisine_type]."<br><br>";
			  }
			  echo "<strong>$lngweoffer: </strong><br>";
			  if ($qryrest[dinein]){echo "$lngdinein<br>";}
			  if ($qryrest[takeout]){echo "$lngtakeout<br>";}
			  if ($qryrest[is_delivery]){ echo "$lngdelivery<br>";}
			  ?>
              </td>
		    </tr>
		  </tfoot>
		  <tbody>
		  <?
		  $main_open_times = get_restaurant_time($conn, $qryrest["id"]);
		  
			foreach ($main_open_times as $restime)
			{
				  $otime=minstotime($restime[open_time]);
				  $ctime=minstotime($restime[close_time]);
				  if($otime==$ctime)
				  {
					  $otime = "-";
					  $ctime = "-";
				  }
				  switch ($restime[day]) {
					case "Monday":
						$restime[day]=$lngmon;
						break;
					case "Tuesday":
						$restime[day]=$lngtue;
						break;
					case "Wednesday":
						$restime[day]=$lngwed;
						break;
					case "Thursday":
						$restime[day]=$lngthu;
						break;
					case "Friday":
						$restime[day]=$lngfri;
						break;
					case "Saturday":
						$restime[day]=$lngsat;
						break;
					case "Sunday":
						$restime[day]=$lngsun;
						break;
				  }
			  ?>
			  <tr>
				<th scope="row"><?=$restime[day]?></th>
				<td><?=$otime;?></td>
				<td><?=$ctime;?></td>
			  </tr>
		<?	$rest_moretimeday = json_decode($restime[more_time]);
			foreach ($rest_moretimeday as $key => $val)
			{ 
				  $otime=minstotime($val[0]);
				  $ctime=minstotime($val[1]);?>
			  <tr>
				<th scope="row"></th>
				<td><?=$otime;?></td>
				<td><?=$ctime;?></td>
			  </tr>
			<?
			}
			} ?>
          
		  </tbody>
		</table>
	</div><!-- /content -->
	<? include_once("header.php"); 
	
	if($data['action'] == "cancelorder") 
	{?>
    	<script language="javascript">
		$(".menupage").live("pagecreate",function(){
        	$.mobile.changePage("?p=cart&<?php echo $PASS_PARAMS; ?>");
		});
		</script>
	<? } ?>
	<div data-role="content">
        <div class="content-primary">
        <?
		if ($_SESSION[restaurantopen] == true)
		{?>
			<ul data-role="listview" data-divider-theme="d">
            	<li data-role="list-divider" style="height:20px">
                	<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
						<? if($_SESSION[loccount]>1) {?>
                        <a class="ui-bar-d" href="#popupMenu" data-role="button"><i class="fa fa-list"></i></a>
						<? }?>
					</div>
                    <span class="divtext"><?=$lngcategories?></span>
                    <div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
                    <a class="ui-bar-d" href="?p=cart&<?=$PASS_PARAMS;?>" data-role="button"><i class="fa fa-shopping-cart"></i></a>
               		</div>
               	</li>
				<?php  
					$total = 0;
					$countrow=0;
					foreach($menus AS $menu) {
						$countrow++;
						if($menu[item_count] > 0) {
							$total ++; ?>
							<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?p=orderitem&<?php echo $PASS_PARAMS; ?>&menu_id=<?php echo $menu["id"]?>">
								<?php echo htmlentities($menu["label"], ENT_QUOTES | ENT_IGNORE, "UTF-8");?> (<?php echo $menu["item_count"]?>)
							</a></li>
							<?php
						}
					}
					if($total == 0) {
						echo $lngnomenu;
					}
						 
				?>
            </ul>
          <?
		}
		else
		{?>
        <ul data-role="listview" data-divider-theme="d">
            	<li data-role="list-divider" style="height:20px">
                	<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
						<? if($_SESSION[loccount]>1) {?>
                        <a class="ui-bar-d" href="#popupMenu" data-role="button"><i class="fa fa-list"></i></a>
						<? }?>
					</div>
                    <span class="divtext"><?=$lngcategories?></span>
                    <div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
                    <a class="ui-bar-d" href="?p=cart&<?=$PASS_PARAMS;?>" data-role="button"><i class="fa fa-shopping-cart"></i></a>
               		</div>
                    <span class="divtext"><?=$lngnotopent?></span>
               	</li>
			<style>
			table { width:100%; margin-top: 50px;}
			table caption { text-align:left;  }
			table thead th { text-align:left; border-bottom-width:1px; border-top-width:1px; }
			table th, td { text-align:left; padding:6px;} 
		</style>
        <table summary="This table lists all the JetBlue flights.">
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
			  if($main_info[brief_desc])
			  { 
			  echo "<strong>$lngdetail: </strong>".$main_info[brief_desc]."<br><br>";
			  }
			  if($main_info[cuisine_type])
			  {
			  echo "<strong>$lngcuisine: </strong>".$main_info[cuisine_type]."<br><br>";
			  }
			  echo "<strong>$lngweoffer: </strong><br>";
			  if ($main_info[dinein]){echo "$lngdinein<br>";}
			  if ($main_info[takeout]){echo "$lngtakeout<br>";}
			  if ($main_info[is_delivery]){ echo "$lngdelivery<br>";}
			  ?>
              </td>
		    </tr>
		  </tfoot>
		  <tbody>
		  <?
		  $main_open_times = get_restaurant_time($conn, $main_info["id"]);
		  
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
      	<?
		}
		?>
     	</div>
	</div><!-- /content -->
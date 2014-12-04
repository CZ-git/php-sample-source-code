<div data-role="content" class="maincontent">
	<?
	if($data[home][manyImages]=="YES")
	{
	?>
      <div class="touchslider" style="width:100%;  height:100% !important;">
	      	<div class="touchslider-viewport" style="width:100%;  height:100% !important; overflow:hidden; position:relative;">
	        	<div style="position:absolute; left:0px; height:100%; width:100%;">
	            <? 	$i=0;
	            	foreach($data[home][imagesInOrder] as $image) 
	               	{
						if ($data[home][linkedTabs][$i][tab_id] != "0")
						{
		                	echo "<div class='touchslider-item'><a href='index.php?tab_id=".$data[home][linkedTabs][$i][tab_id]."&controller=".$data[home][linkedTabs][$i][view]."'><img src='$image' width='100%' height='100%'></a></div>";
		                }
	                    else
	                    {
	                    	echo "<div class='touchslider-item'><img src='$image' width='100%' height='100%'></div>";
	                    }
						$i++;
	                } 
	       		?>
	            	</div>
	            </div>
	      </div>
      <?
	}
	?>
	<? 
	if ($data[settings][CallButton] == "YES" || $data[settings][DirectionButton] == "YES" || $data[settings][TellFriendButton] == "YES") 
	{?>
		<div data-role="navbar" class="homebar ui-responsive" data-iconpos="left">
	      	<ul>
			<? 
			if ($data[settings][CallButton] == "YES") 
		    {?>
	            	<li>
	            		<a href="#popcallus" data-role="button" data-position-to="window" data-inline="true" data-rel="popup" data-theme="b"><?=$lngcallus;?></a>
	            	</li>
		<? 	} 
			if ($data[settings][DirectionButton] == "YES") 
			{
				$sqldirection = "SELECT `id` , `city` , `state` FROM app_locations WHERE `app_id` ='".$data[settings][AppID]."'";
				$resdirection = mysql_query($sqldirection, $conn);
				if (mysql_num_rows($resdirection) == 1 )
				{
					$qrydirection = mysql_fetch_array($resdirection); ?>
					<li>
						<a href="directions.php?id=<? echo $qrydirection["id"] ?>" data-role="button" data-inline="true" data-theme="b"><?=$lngdirection?></a>
					</li>
			<? 	} 
				else 
				{?>
	            		<li>
	            			<a href="#popdirections" data-role="button" data-position-to="window" data-inline="true" data-rel="popup" data-theme="b"><?=$lngdirection?></a>
	            		</li>
	         	<? 	
				} ?>
	   <? 	} 
	         	if ($data[settings][TellFriendButton] == "YES") 
	         	{?>
	            	<li>
	            		<a href="#popshare" data-role="button" data-position-to="window" data-inline="true" data-rel="popup" data-theme="b"><?=$lngtellfriend ?></a>
	            	</li>
	         <? } ?>
	    		</ul>
		</div><!-- /navbar -->
<?	}?>
</div>
<? include_once "popup_mainpage_buttons.php";?>
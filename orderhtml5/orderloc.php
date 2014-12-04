<div data-role="content">
	<div class="content-primary">
		<ul data-role="listview" data-divider-theme="d">
			<li data-role="list-divider" style="height:20px">
            <? if(!empty($data[loc_id]))
    			{?>
				<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
					<a class="ui-bar-d icon" href="?p=cart&<?=$PASS_PARAMS;?>" data-role="button"><i class="fa fa-shopping-cart"></i></a>
				</div>
          	<?	}
			?>
				<span class="divtext"><?=$lnglocations?></span>
			</li><?	
			$countrow=0;
			foreach($locs AS $loc)
			{ 
				$countrow++;?>
				<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?p=ordermenu&<?php echo $PASS_PARAMS; ?>&loc_id=<?php echo $loc["id"]?>"><?php echo $loc["city"]?></a></li>
<?			}?>
		</ul>
	</div>
</div><!-- /content -->

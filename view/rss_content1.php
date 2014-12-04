	<div data-role="content" style="background:url(<?=$data[0][background] ?>); background-size: 100% 100%;"> 
 	   <ul class="ui-listview">
		<?  
			$count = 0;
			foreach ($data as $variables)
			{ 
			//print_r($rssfeed);
			if(!$sideimage)
			{
			$sideimage = $data[0][icon];
			}
			if($date != $variables[section])
			{
			$countrow=0;
			?>	
             <li class="ui-li-divider ui-bar-d"><? echo date("F d, Y", strtotime($variables[section]));?></li>
            <?
			}
			$countrow++;
			$date = $variables[section];
			?>
            <li id="id<?=$countrow?>" <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
            	<a href="#popshareloc<?=$id?>" <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><i style="z-index:1; position: absolute;top: 15px;left: 2px; font-size: 1.5em" class="fa fa-share-square-o fa-2x"></i></a>
            	<span class="btnlist ui-shadow ui-corner-all ui-icon-carat-d ui-btn-icon-notext ui-btn-inline"></span>
				<a href="<?=$variables[link]?>" target="_blank" class="ui-btn">
					<p class="shortlen"><img src="<?=$sideimage;?>" title="RSS Feed" />
						<?=$variables[title]."<br>".strip_tags($variables[summary]) ?><br><?=date('l t F Y',$variables[pubDate]) ?>
					</p>
				</a>
			</li>
			<?
			}
			?> 	
    	</ul>
    	<br><br><br>
    </div>
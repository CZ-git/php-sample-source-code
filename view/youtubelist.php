<div data-role="header" data-position="fixed"> 
    <h1><? echo $label; ?></h1> 
    <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
    <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div>
<div data-role="content" style="margin-left:auto; margin-right:auto" > 
    <ul data-role="listview" data-divider-theme="d">
        <?
		//print_r($data[0]);
		$countrow=0;
        foreach ($data as $variables)
        {
            $i++;
            $publishday=substr($variables[published],0,10);
            $newDate = date("F d, Y", strtotime($publishday));
			
            if (substr($data[$countrow][published],0,10)!=substr($data[$countrow-1][published],0,10))
            {
                $i=1;
                //echo "this date:".$publishday[$countrow]." last date".$publishday[$countrow-1];?>
                <li data-role="list-divider"><?=$newDate?></li>
                <?
            }
            //print_r($publishday);
            //echo $i;?>
            <div style="text-align:center" <?= $i%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                <a href="?controller=YoutubeViewController&tab_id=<?=$tab_detail[id]?>&vid_id=<?=$variables[id]?>"><img src="<?=$variables[imageurl]?>" width="320" height="200"/></a><br>
                <div style="text-overflow: ellipsis;white-space: nowrap; overflow: hidden; text-align:center !important;"><strong><?=$variables[title]?></strong></div>
                <div  class="ui-grid-b" style="margin-right:-2px; height:25px;">
                    <div class="ui-block-a"><div class="ui-bar" style="height:15px; font-size:11px"><img src="images/play.png" width="12" height="12" />   <?=$variables["yt:statistics_viewCount"]?></div></div>
                    <div class="ui-block-b"><div class="ui-bar" style="height:15px; font-size:11px"><img src="images/thumbs.png" width="12" height="12" />   <?=$variables["numlikes"];?></div></div>
                    <div class="ui-block-c"><div class="ui-bar" style="height:15px; font-size:11px"><img src="images/comment.png" width="12" height="12" />   <?=$variables["gd:feedlink_countHint"]?></div></div>
                </div><!-- /grid-b -->	
            </div> 
            <?	
            $countrow++;
		}
        ?> 
    </ul>
</div>

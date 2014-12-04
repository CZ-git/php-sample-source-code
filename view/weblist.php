<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%; -webkit-background-size: 100% 100%;">
    		<ul data-role="listview" data-divider-theme="d"> 
	<? 					$sql = "SELECT * FROM `web_views` WHERE `tab_id` ='$tab_detail[id]' order by seq asc";
						$res = mysql_query($sql, $conn);
						if ( mysql_num_rows($res) > 0 )
						{
							$countrow=0;
							while ($qry = mysql_fetch_array($res))
							{
								$countrow++;
								$url=$qry["url"];
								$urlleft = str_split($url, 4);
								if( $urlleft[0] != "http")
								{
								$url="http://".$url;
								}
						?>
								<li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                                <a <?=webredirection($url,$qry[id])?>>
                                <?
								$image_file = findUploadDirectory($app_id, "webview") . "/$qry[id].jpg";
								$image="/custom_images/$_SESSION[app_code]/$qry[id].jpg?extra=webview";
                            	if (file_exists($image_file)) {?>
                                <img src="<?=$image?>&width=80">
                                <?
								}?>
                    			<h2><? echo $qry["name"] ?></h2>
                                </a>
                                </li>
						<?
							} 
						} 
						?>
			</ul> 
	</div>
	<? include_once("header.php"); ?>

	<div data-role="content">
        <div class="content-primary">
        	<style>
			.ui-li .ui-btn-inner a.ui-link-inherit, .ui-li-static.ui-li {
				padding-top: 0.4em;
				padding-left: 75px;
				padding-bottom: 0.4em;
				display: block;
			}
			</style>
			<ul data-role="listview" data-divider-theme="d">
            	<li data-role="list-divider" style="height:20px; /*display: -webkit-box;*/">
            		<div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
						<a href="#popupMenu" class="ui-bar-d" data-role="button"><i class="fa fa-list"></i></a>
					</div>
					<span class="divtext" style="/*display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;width: 189px; text-align:left;*/"><?=$lngitemsfor." ".htmlentities($menu["label"], ENT_QUOTES | ENT_IGNORE, "UTF-8");?></span>
					<div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
						<a href="?p=cart&<?=$PASS_PARAMS;?>" class="ui-bar-d" data-role="button"><i class="fa fa-shopping-cart"></i></a>
					</div>
				</li>
				<?  
				$countrow=0;
				foreach($items AS $item) { 
				$countrow++;
				$img_url='';
				if(preg_match("/^http:\/\/(.*)$/", $item["image_url"]) || preg_match("/^http:\/\/(.*)$/", $item["image_url"])) {
				$img_url = "/images_online.php?name=".urlencode($item["image_url"]);
				} else {
					$dir = findUploadDirectory($app_id) . "/ordering/".$item["image_url"];
					//echo $dir;
					if(file_exists($dir) && !is_dir($dir)) {
						$img_url = "/custom_images/".$data[app_code]."/ordering/".$item["image_url"];
					}
				}?>
                <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?p=order&<?php echo $PASS_PARAMS; ?>&item_id=<?=$item["id"]?>"><? if (isset($img_url) && $img_url!='') { ?><img src="<?=$img_url;?>" height="60" style="height: 100%; padding-top: 1px;" width="80"/><? }?><?=htmlentities($item["item_name"], ENT_QUOTES | ENT_IGNORE, "UTF-8");?></a></li>
				<? }?>
				
				<?php
				if(count($items) < 1) {
					echo $lngnoitem;
				} 
				?>
            </ul>
     	</div>
	</div><!-- /content -->
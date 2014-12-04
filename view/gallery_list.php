<div data-role="page" data-add-back-btn="true" id="Gallery1" class="gallery-page">
	<div data-role="header" data-position="fixed"> 
		<h1><?=$label ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
    </div> 
	<div data-role="content">  
            <ul data-role="listview" data-divider-theme="d">
            </li>
                <?	
                $countrow=0;
                while ($list_qry = mysql_fetch_array($list_res))
                { 
                    $countrow++;
					$thumbnail = '';
					$dir = findUploadDirectory($app_id, "gallery_list");
					$filename = $list_qry['id'];
					if ( $list_qry['ext'] )
						$filename .= '.' . $list_qry['ext'];			
					
					if ( file_exists($dir."/".$filename) )
					{	$thumbnail = '/custom_images/'.$_SESSION["app_code"].'/'.$filename.'?extra=gallery_list';}
		
                ?>
                <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                	<a href="?controller=<?=$tab_detail[view_controller]?>&tab_id=<?=$tab_detail[id]?>&cat_id=<?=$list_qry["id"]?>">
						<? if (isset($thumbnail) && $thumbnail!='') { ?>
                        	<img src="<?=$thumbnail;?>" height="60" style="height: 100%; padding-top: 1px;" width="80"/>
						<? }?>
					<?=$list_qry['name'];?>
                   </a>
              </li>
    <?			}?>
            </ul>
    </div><!-- /content -->
<?
include_once "view/leftsidepanel.php"; ?>
</div>
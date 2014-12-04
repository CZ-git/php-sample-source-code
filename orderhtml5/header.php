<?	$locs = get_tab_location($conn, $data["tab_id"]);
if (isset($_SESSION['app_code'])) {?>
<div data-role="header" data-position="fixed" >
		<h1><?=$_SESSION['label_rest'];?></h1>
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
</div><!-- /header -->
	<!-- /Fix for HTML5 app for .ui-page background -->
	<style>
	#orderingsystem .ui-panel-content-wrap-closed,#orderingsystem .ui-panel-content-wrap-display-reveal, #orderingsystem .ui-panel-content-wrap-open
    {
    background:url(<?=$_SESSION[orderbg]?>);
    }
	</style>
<?php include_once("html5common.inc");include_once "../view/leftsidepanel.php"; ?> 
<? }
if (strpos($_SERVER['REQUEST_URI'],"cart")== NULL)
{include_once("order_view_cartlink.php"); }?>
    <div data-role="panel" id="popupMenu" data-theme="a">
				<ul data-role="listview" style="min-width:210px;" data-theme="a" data-divider-theme="d">
                <?
				if (count($locs) > 1)
				{?>
                <li data-role="list-divider"><?=$lnglocations?></li>
                            <?  $countord = 0;
								foreach($locs AS $loc)
								{ 
								$countord++;?>
                            	<li <?= $countord%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?p=ordermenu&<?php echo $PASS_PARAMS; ?>&loc_id=<?php echo $loc["id"]?>"><?php echo $loc["city"]?></a></li>
                                <?
								}?>
				<?
				}
				if(!strpos($_SERVER['REQUEST_URI'],"ordermenu") && $_SESSION[restaurantopen] == true)
				{
				$menus = get_ordering_menus($conn, $app_id, $data[tab_id], "1,2");
				?>
                <li data-role="list-divider"><?=$lngcategories?></li>
	            <?php		$countord = 0;
							foreach($menus AS $menuz) 
							{
								$countord++;
								if($menuz[item_count] > 0) 
								{ ?>
									<li <?= $countord%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>><a href="?p=orderitem&<?php echo $PASS_PARAMS; ?>&menu_id=<?php echo $menuz["id"]?>">
										<?php echo $menuz["label"]?> (<?php echo $menuz["item_count"]?>)
									</a></li>
							<?php
								}
							}						 
							?>
                        <?
				}?>
          </ul>
	</div>
    <script type="text/javascript">
			$(document).on('blur', 'input, textarea', function() {
				setTimeout(function() {
					window.scrollTo(document.body.scrollLeft, document.body.scrollTop);
				}, 0);
			});

	</script>
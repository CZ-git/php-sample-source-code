<?
$action = htmlspecialchars($_GET['submit'], ENT_QUOTES);
if ($action == "submit")
{
		$sqltab = "select * from app_tabs where id ='$tab_detail[id]' and view_controller = 'StatRecorderViewController'";
		$restab = mysql_query($sqltab, $conn);
		$total = mysql_fetch_array($restab);
		//echo $_SERVER['PHP_SELF'];
		$sql = "select *
		        from stat_fields
		        where app_id = '$app_id' 
		        and tab_id = '$tab_detail[id]'
		        order by seq";
		$res = mysql_query($sql, $conn);
		$to = $total["value1"];
		$subject = $label;
		while ($qry = mysql_fetch_array($res))
		{
			$name = $qry["name"];
			$seq  = $qry["id"];
			$count = $_GET["seq$seq"];
			$body = $name."\t\t\t".$count."%0A";
			$add = $add.$body;
		}
		$body = $total["value2"]."%0A%0A".$add."%0A%0ASent from Mobile";
		$mailto = "mailto:".$to."?subject=".$subject."&body=".$body;
		//echo $mailto;
		//header('Location: '.$mailto); 
		//echo "<br>".$body; 
}
?>
<body> 
<?
$sql = "select *
        from stat_fields
        where app_id = '$app_id' 
        and tab_id = '$tab_detail[id]'
        order by seq";
$res = mysql_query($sql, $conn); ?>
<div data-role="page"> 
	<style>
	.ui-bar{ padding-top:18px };
    form div{ overflow: hidden; margin: 0 0 5px 0; }
	.button{ cursor: pointer; width: 29px; height: 29px; float: left; text-align: center; border-radius:0.9em; padding-top:0.25em }
	</style>
	<div data-role="header" data-position="fixed"> 
		<h1><? echo $label; ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat; background-size: 100% 100%;" > 
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" data-ajax="false" method="get">
    <script type="text/javascript">
		$(function() {
			$(".button").click(function() {
				var $button = $(this);
				var oldValue = $button.parent().find(".ui-block-a input").val();
				if ($button.text() == "+") {
				  var newVal = parseFloat(oldValue) + 1;
				  // AJAX save would go here
				} else {
				  // Don't allow decrementing below zero
					  var newVal = parseFloat(oldValue) - 1;
					  // AJAX save would go here
				}
				$button.parent().find(".ui-block-a input").val(newVal);
				$button.parent().find(".ui-block-b").text(newVal);
			});
		});
	</script>
    <div class="ui-grid-c"> 
    <? 
    while ($qry = mysql_fetch_array($res)) { ?>
    <div id="name<?=$qry["seq"]?>" style="width:100%; height: 50px;">
            <div class="ui-block-a" style="width:40%; padding-top:7px">
                <?=$qry["name"]?>
                <input type="hidden" name="seq<?=$qry["id"]?>" value="0"/>
            </div>
            <div class="ui-block-b" style="width:20%; padding-top:7px">
                0
            </div>
            <div class="inc button ui-block-c ui-btn-b" style="width:20%">+</div>
            <div class="dec button ui-block-d ui-btn-b" style="width:20%">-</div>
    </div>       
            <? } ?>
            <input type="hidden" name="controller" value="StatRecorderViewController" />
            <input type="hidden" name="tab_id" value="<?=$tab_detail[id]?>" />
            <div class="ui-block-a" style="width:100%; font-size:10px;">
<button type="submit" data-theme="b" name="submit" value="submit"><?=$lngemail_results?></button> </div>
            </div> </form>
	</div>
	<?php  include_once "view/leftsidepanel.php";
	if ($action == "submit")
	{ ?>
		<script>
			window.location.href = "<?=$mailto;?>";
		</script>
		<?
	}
	?>
</div><!-- /page --> 
</body> 
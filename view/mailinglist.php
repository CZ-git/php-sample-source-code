<?
$action = htmlspecialchars($_GET['action'], ENT_QUOTES);
 if ($action == "submit") {
			$name = mysql_real_escape_string($_GET["name"]);
			$email = mysql_real_escape_string($_GET["email"]);
			$birthday = mysql_real_escape_string($_GET["birthday"]);
			$zip = mysql_real_escape_string($_GET["zip"]);
			$country = mysql_real_escape_string($_GET["country"]);
			$comment = mysql_real_escape_string($_GET["comment"]);
			$sql = "INSERT INTO `mailing_list`(`app_id`, `email`, `name`, `birthday`, `postalcode`, `country`, `comment`) VALUES ('$app_id','$email','$name','$birthday','$zip','$country','$comment')";
			$res = mysql_query($sql, $conn) or die(mysql_error());
			$new_subscriber_id=mysql_insert_id();
			$sql = "SELECT * FROM `mailing_list_categories` WHERE app_id ='$app_id' order by 'seq'";
			//echo $sql;
			$res1 = mysql_query($sql, $conn);
			$myI = 0;
			$query1="INSERT INTO mailing_list_subscriptions(subscriber_id,category_id)values";
			$query2="";
			while ($qry = mysql_fetch_array($res1)) 
			{ 
				if($_REQUEST["slider".$qry["id"]]=="on")
				{
					if($query2!="")
					{
						$query2.=",('".$new_subscriber_id."','".$qry["id"]."')";
					}
					else
					{
						$query2="('".$new_subscriber_id."','".$qry["id"]."')";
					}
				}
			}
			//echo $query1.$query2;
			$res2 = mysql_query($query1.$query2, $conn);
		  	$added=$lngmail_success;
}
$logo_img_file = findUploadDirectory($app_id) . "/mailing_list_logo.jpg";
$logo_img = '/images/theme_editor/no button.png';
if (file_exists($logo_img_file)) {
    $logo_img = '/custom_images/' . $_SESSION["app_code"] . '/mailing_list_logo.jpg';
} 

?>
<body> 
<div data-role="page" id="mailinglist">
	    
	<div data-role="header" data-position="fixed"> 
		<h1><? echo $_SESSION['labelj'] ?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
    </div> 
	<div data-role="content" style="background:url(<? echo $image_file; ?>) no-repeat;">
		<div class="abvcircle">
			<div class="top-circle"><img src="<?= $logo_img;?>" style="height: 99px;width:98px;-moz-border-radius: 50%;border-radius: 50%;" ></div>
			<?  $sql = "SELECT VALUE1 FROM `app_tabs` WHERE id =$tab_detail[id]";
			$res1 = mysql_query($sql, $conn);?>
            <br><div align="center" style="color:#<?=$qrydesignchk["feature_text"];?>; font-size: 12px; width: 80%; margin: 0 auto;"><?=mysql_result($res1, 0, 0);?></div>
           </div>
 <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" id="mailForm" name="mailForm">
		<div id="mailcontainer">
			<? if ($added) {?><h2 align="center" class="submittedmail"><?=$added?></h2><? } ?>
             <div class="fields">
				<div class='oddrowbackground'><span class="req-opt"><?=$lngname?></span></div>
				<label for="name" class="ui-hidden-accessible"><?=$lngname?></label>
				<input type="text" name="name" id="name" value=""  placeholder="<?=$lngrequired?>" class="required form_element" data-mini="true" />
				<input type="hidden" name="id" value="<?=$id?>"/>
				<div class="evenrowbackground"><span class="req-opt"><?=$lngemail?></span></div>
				<label for="email" class="ui-hidden-accessible"><?=$lngemail?></label>
				<input type="text" name="email" id="email" value="" placeholder="<?=$lngrequired?>" class="required email form_element" data-mini="true" />
				<div class='oddrowbackground'><span class="req-opt"><?=$lngbirthday?></span></div>
				<input type="date" value="" placeholder="<?=$lngoptional?>" name="birthday" />
				<div class="evenrowbackground"><span class="req-opt"><?=$lngzip?></span></div>
				<input type="number" value="" pattern="[0-9]*" placeholder="<?=$lngoptional?>" name="zip" />
				<div class='oddrowbackground'><span class="req-opt"><?=$lngcountry?></span></div>
				<!--input type="text" value="" placeholder="Optional" name="country" /-->
				
					
					<select name="country"> 
						<option value="Optional" selected="selected"><?=$lngoptional?></option>
						
						<?

							$sql1 = "SELECT country_name FROM countries";
							
							$result1 = mysql_query($sql1, $conn);
							
							
							while($row1 = mysql_fetch_array($result1)){ 
							
						?>
								
						<option class="blkcolor" value="<?=$row1['country_name']; ?>"> <?=$row1['country_name']; ?> </option>

						<? } ?>
						
					</select>
					
				
					
				<div class="evenrowbackground"><span class="req-opt"><?=$lngcomments?></span></div>
				<input type="text" value="" placeholder="<?=$lngoptional?>" name="comment" />
             </div>
             	<input type="hidden" name="controller" value="MailingListViewController" />
            	<input type="hidden" name="tab_id" value="<?=$tab_detail[id]?>" />
			<? 
			$sql = "SELECT * FROM `mailing_list_categories` WHERE app_id ='$app_id' order by 'seq'";
			//echo $sql;
			$res1 = mysql_query($sql, $conn);
			$myI = 0;
			while ($qry = mysql_fetch_array($res1)) 
			{ 
				$myI++;
                
                if($myI%2==0):
                    $bg_class = 'evenrowbackground';
                else:
                    $bg_class = 'oddrowbackground';
                endif;
                ?>
					<div class="<?= $bg_class ?>">
					<div class="ui-grid-a">
					<div class="ui-block-a <?= $bg_class ?>" style="padding-left: 5px;">				
						<?=$qry["name"];?>
	                	</div>
	                	<div class="ui-block-b">
		                    <div data-role="fieldcontain" class="ui-hide-label">
		                     <label for="slider<?=$qry["id"] ?>"><?=$qry["name"] ?></label>
		                     <select name="slider<? echo $qry["id"] ?>" id="slider<?=$qry["id"] ?>" data-theme="b" data-role="flipswitch"  data-track-theme="a" data-theme="a">
                                 <option value="off"><?=$lngno?></option>
                                 <option value="on" selected><?=$lngyes?></option>
		                     </select>
		                    </div>
	                	</div>
             		</div>
                    </div>
                     
                     
                     <!-- /grid-a -->
		<? 	} ?>
        </div> 
<!--        <input type="hidden" id="total_mailing_cat" name="" value="<? echo $myI;?>">
-->            <fieldset>
                	<div><button type="submit"  id="action" name="action" value="submit" data-theme="b"><?=$lngjoin?></button></div>
            </fieldset>
</form>
<br><br><br>
	</div> 
	<?php include_once "view/leftsidepanel.php";  ?>
    <script type="text/javascript">
            $(document).ready(function() {
                $('.form_element').tooltipster({ 
                    trigger: 'custom', // default is 'hover' which is no good here
                    onlyOne: false,    // allow multiple tips to be open at a time
                    position: 'top'  // display the tips to the right of the element
                });
            });
        </script>
    <script>
		$(document).on("pagecreate","#mailinglist",function(){
		  // $("#mailForm").validate();
			   $('#mailForm').validate({
					// any other options & rules,
					errorPlacement: function (error, element) {
						var val=$(error).text().replace(/^\s+/, '').replace(/\s+$/, '');
						if(val!="")
						{
							$(element).tooltipster('update', $(error).text());
							$(element).tooltipster('show');
						}
						else
						{
							$(element).tooltipster('hide');
						}
					},
					success: function (label, element) {
					}
				});
		});
	</script>
	</div><!-- /page --> 
    
</body> 
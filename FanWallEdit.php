<? 
include_once "dbconnect.inc";
include_once "html5common.inc";
include_once "language_device_detect.php";
//$id = make_data_safe($_GET['id']);
session_start("mob_app");
$id = $_SESSION['id_Fan'];
$label = $_SESSION['label_Fan'];
 // store session data
    $action = htmlspecialchars($_GET['action'], ENT_QUOTES);
	$comment = stripslashes(strip_tags($_GET['comment']));
//	echo "SESSIONs: > ".$_SESSION['twitfaceid'].$_SESSION['username'].$_SESSION['oauth_provider'].$_SESSION['imageurl'].$id.$_SESSION['fanid'];
	if ($action != "submit") 
	{
		$_SESSION[twitfaceid]=$_GET['twitfaceid'];
		$_SESSION[username]=$_GET['username'];
		$_SESSION[oauth_provider]= $_GET['oauth_provider'];
		$_SESSION[imageurl]= $_GET['imageurl'];
	}
	else if ($action == "submit") 
	{
			// CHECK THE APP RECORD
			//app_id 	tab_id 	parent_id 	created 	facebook_id 	twitter_id 	youtube_id youTube video id value	name 	comment
			if (isset($_SESSION['fanid']) && $_SESSION['fanid'] != "")
				$parentid=$_SESSION['fanid'];
			else
				$parentid=0;	
			
			$time = time(); 
			$datetime = date('Y-m-d H:i:s',$time);
			if($_SESSION[fanwallv2]==0)
			{
				if ($_SESSION['oauth_provider'] == "Twitter"){
					$sqlf = "INSERT INTO `fan_wall_comments` (app_id, tab_id, parent_id, twitter_id, name, comment,avatar, created) VALUES (".$app_id.", ".$id.", '$parentid', '".$_SESSION['twitfaceid']."', '".$_SESSION['username']."', '".$comment."','".$_SESSION['imageurl']."', '$datetime' )";
				}
				else{
					$sqlf = "INSERT INTO `fan_wall_comments` (app_id, tab_id, parent_id, facebook_id, name, comment, created) VALUES (".$app_id.", ".$id.", '$parentid', '".$_SESSION['twitfaceid']."', '".$_SESSION['username']."', '".$comment."', '$datetime' )";
				}
			}
			else
			{
				if ($_SESSION['oauth_provider'] == "Twitter"){
					$sqlf = "INSERT INTO `app_user_comments` (app_id, tab_id, detail_id, parent_id, user_type, user_id, name, comment,avatar, created) VALUES ($app_id, $id, $_SESSION[detail_id], $parentid,'2', '$_SESSION[twitfaceid]', '$_SESSION[username]', '$comment', '$_SESSION[imageurl]', '$datetime' )";
				}
				else{
					$sqlf = "INSERT INTO `app_user_comments` (app_id, tab_id, detail_id, parent_id, user_type, user_id, name, comment, created) VALUES ($app_id, $id, $_SESSION[detail_id], $parentid,'1', '$_SESSION[twitfaceid]', '$_SESSION[username]', '$comment', '$datetime' )";
				}
				
			}
//			echo $sqlf;
			$res = mysql_query($sqlf, $conn);
			if ($res)
			{
			unset($_SESSION['fanid']);
			unset($_SESSION['detail_id']);
			unset($_SESSION['twitfaceid']);
			unset($_SESSION['username']);
			unset($_SESSION['oauth_provider']);
			unset($_SESSION['imageurl']);
			?>
            <script> window.location = "<?=$path.'/'.$_SESSION[commentpage];?>"</script>
            <?
			//echo "SQL ADDED";
			}
	}
?>
<!DOCTYPE html> 
<html> 
<? include "header.php";?>
<body> 
<div data-role="page" id="fanWall_comment_page">
			<script>
            	var outp;
                $(document).ready(function() 
                {
                    $('#comment').keyup(function() {
                        var res=$('#comment').val().length;
                        outp=250-res;
                        //alert($('#comment').val()+" "+res);
                        $('#out').text(outp);
                        if (outp < 1)
							$('#comment').val($('#comment').val().substring(0, 249));
                    });
                    
                    event.stopImmediatePropagation();
                    return false;
                });
            </script>
	<div data-role="header" data-position="fixed"> 
		<h1><? echo $label ?></h1>
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
	</div> 
	<div data-role="content">
		<div class="content-primary">	
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" data-ajax="false" id="fanWall_comment_form">
			<div data-role="fieldcontain">
                <label for="comment"><?=$lngadd_comment?><br>(<span id="out">250</span> Characters left )</label>
                <textarea name="comment" id="comment"  class="required form_element"  rows="120" cols="90"></textarea>
			</div>

            <fieldset>
			<div><button type="submit" name="action" id="action" value="submit" data-theme="b"><?=$lngadd_comment?></button></div>
	    	</fieldset>
        </form>
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
			$(document).on("pagecreate","#fanWall_comment_page",function(){
			  // $("#fanWall_comment_form").validate();
			   $('#fanWall_comment_form').validate({
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
		</div><!--/content-primary -->		
   	</div>
    <?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 

</body>
</html>
	<script>
          $('#pageiframe').on('pageinit', function() {
			  var content = $(".content-wrapper");
			  var viewport_height = window.innerHeight;
			  var content_height = viewport_height - 44;
			  content_height -= (content.outerHeight() - content.height());
			  content.height(content_height);
		   });	  
	</script>
	<style>
	div.content-wrapper {
	position: relative;
	left: 0;
	height:100%;
	padding: 0px;
	}
</style>

	 <? 	$sql = "SELECT * FROM `web_views` WHERE `id` ='$tab_detail[cat_id]' and `app_id`='$app_id' Limit 0,1";
			$res = mysql_query($sql, $conn);
						if ( mysql_num_rows($res) > 0 )	
						{
								$qry = mysql_fetch_array($res);
								$url=$qry["url"];
						}
						else if (isset($_GET[url]))
						{
								$url=$_GET[url];
						}
								$urlleft= str_split($url, 4);
								if( $urlleft[0] != "http")
								{
								$url="http://".$url;
								}
						
						$label = $qry["name"]!=''?$qry["name"]:(isset($_GET[label])?$_GET[label]:"External Site");
						if(strposa($url, $_SESSION['non_iframe_needle']))
						{?>
							<script> window.location = "<?=$url;?>"</script>
						<? 
						}?>
	<div data-role="header" data-position="fixed"> 
		<h1><?=$label?></h1> 
        <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
        <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
    <div class="content-wrapper">
        <iframe sandbox="allow-same-origin allow-scripts allow-popups allow-forms" src="<?=$url?>" border="0" style="width: 100%; height:100%; border: 0px;"></iframe>
   	</div>
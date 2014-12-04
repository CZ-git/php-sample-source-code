<script>
         $('#pageiframe').on('pageshow', function() {
         	var browser=navigator.userAgent.toLowerCase();
			if(browser.indexOf('firefox') > -1 || browser.indexOf('chrome') > -1)
			{
			  var content = $(".content-wrapper");
			  var viewport_height = window.innerHeight;
			  var content_height = viewport_height - 44;
			  content_height -= (content.outerHeight() - content.height());
			  content.height(content_height);
			}
		   }); 
	</script>
<style>
	div.content-wrapper {
	position: relative;
	left: 0;
	height: 100%;
	padding: 0px;
	}
</style>
	<div data-role="header" data-position="fixed"> 
    	<a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
		<h1><?=$label?></h1> 
		<a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
	</div> 
    <? 
	if(strpos($_SERVER['HTTP_HOST'],"appsomen.com")) $stringaddress  = "/client";
	?>
    <div class="content-wrapper">
        <iframe src="<?=$stringaddress?>/form_build.php?tk=<?=$tk?>" border="0" style="width: 100%; height:100%; border: 0px;"></iframe>
   	</div>
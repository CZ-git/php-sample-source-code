<? $url=$_SESSION['domain']."&t=this is text i have added";?>
<script>
	$(document).ready(function() 
	{
		$( "#popupshareloc<? echo $id?>" ).enhanceWithin().popup();
	});
</script> 
<!--<a href="#popupshareloc<? echo $id?>" data-icon="share" class="ui-btn-right" data-rel="popup"></a>
--><div data-role="popup" id="popupshareloc<? echo $id?>" class="ui-content"><!-- popup starts-->
	<a href="#" class="ui-icon ui-icon-shadow"
      onclick="
        window.open(
          'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('<?php echo $url?>'), 
          'facebook-share-dialog', 
          'width=626,height=436'); 
        return false;">
      <img src="images/facebook.jpg" />
    </a>
    <br />
    <a href="https://twitter.com/intent/tweet" ><img src="images/twitter.png" /></a>
    <script >
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
    </script>
    <br />
    <a href="mailto:Who?subject=<?=$_SESSION['app_name'];?>&body=<?=$_SESSION['domain']?>" class="ui-icon ui-icon-shadow"><img src="images/email_icon.jpg" /></a>
</div><!-- popup ends-->
<style>

#popupshareloc<? echo $id?>-popup
{
	width:auto;
}
</style>
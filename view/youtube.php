<body>
<div data-role="page" id="youtube<?=$_GET[vid_id]?>"> 
	<?
	if(!empty($_GET[vid_id]))
	{
		include_once "youtubeframe.php";
	}
	else
	{
		include_once "youtubelist.php";
	}
	?>	
	<?php include_once "view/leftsidepanel.php";  ?>
</div><!-- /page --> 
</body> 
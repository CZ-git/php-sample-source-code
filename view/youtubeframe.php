<div data-role="header" data-position="fixed"> 
    <h1><?=$label?></h1> 
    <a href="#nav-panel" data-icon="bars" data-iconpos="notext">Menu</a> 
    <a href="#popshareloc<?=$id?>" data-icon="share" class="ui-btn-right"></a>
</div> 
<div class="youtube-view-wrapper" data-role="content">
    <iframe src="http://www.youtube.com/embed/<?=$_GET[vid_id]?>" border="0" style="width: 100%; height:100%; border: 0px;"></iframe>
</div>
<?
include_once("dbconnect.inc");
if ((isset($_SESSION[path]) && $_SESSION[path]!="") || $includedinorder!="1")
{
include_once("dbconnect.inc");
include_once("html5common.inc");	
header("Content-type: text/css; charset: UTF-8");
}
?>

/* Swatches */

.ui-header {
	opacity:1 !important;
}

.ui-loader .ui-icon-loading,.ui-loading .ui-loader, .ui-loader-textonly .ui-icon-loading {
display: none;
}
/* A
-----------------------------------------------------------------------------------------------------------*/
.ui-page-theme-a, .ui-page-theme-a .ui-btn, html .ui-bar-a .ui-btn, .ui-body-a, html .ui-body-a .ui-btn, html body .ui-group-theme-a .ui-btn, html head+body .ui-btn.ui-btn-a, .ui-page-theme-a .ui-btn:visited, html .ui-bar-a .ui-btn:visited, html .ui-body-a .ui-btn:visited, html body .ui-group-theme-a .ui-btn:visited, html head+body .ui-btn.ui-btn-a:visited, .ui-page-theme-a .ui-btn:hover, html .ui-bar-a .ui-btn:hover, html .ui-body-a .ui-btn:hover, html body .ui-group-theme-a .ui-btn:hover, html head+body .ui-btn.ui-btn-a:hover
	{
	text-shadow: none !important;
	}
.ui-page-theme-a .ui-listview .ui-btn, .ui-page-theme-a .ui-listview .ui-btn:hover, .locationdesc .ui-body-inherit{
		background:transparent;
		border: 0px!important;
		text-shadow: none !important;
	}

.ui-btn:after {
	color: #<?=$qrydesignchk["feature_button"]?>;
	font-family: FontAwesome;
	font-size: 2em;
}
	.ui-header .ui-btn:after, .ui-footer .ui-btn:after {
		color: #<?=$qrydesignchk[navigation_text_color]?>
	}

	.ui-listview .ui-page-theme-a .ui-bar-a, .ui-page-theme-a .ui-bar-inherit {
    <? if ($qrydesignchk["navigation_text_color"]) { ?> 
	color: 					#<? echo $qrydesignchk["navigation_text_color"]?> /*{a-bar-color}*/;
	<? } else { ?>
    color: 					#ffffff;
    <? } ?>
    <? if ($qrydesignchk["navigation_text_shadow_color"]) { ?> 
	text-shadow: 0 /*{a-bar-shadow-x}*/ 0px /*{a-bar-shadow-y}*/ 0px /*{a-bar-shadow-radius}*/ #<? echo $qrydesignchk["navigation_text_shadow_color"]?>;
    <? } else { ?>
    text-shadow: 0 /*{a-bar-shadow-x}*/ 0px /*{a-bar-shadow-y}*/ 0px /*{a-bar-shadow-radius}*/ #000000 /*{a-bar-shadow-color}*/;
    <? } ?>	
    <? if ($global_header) { 
	HexToRGB($qrydesignchk["global_header_tint"],$global_header,$qrydesignchk["global_header_tint_opacity"],"global".$app_id);
?>
	background-image: url(images/header/<?="global".$app_id.".png"?>);
	<? } else if ($qrydesignchk["navigation_bar_color"]) { ?>
	background-image: -webkit-gradient(linear,0% 0%,0% 100%,from(#ffffff),color-stop(55%,#<? echo $qrydesignchk["navigation_bar_color"] ?>),to(#<? echo $qrydesignchk["navigation_bar_color"] ?>)); /* Saf4+, Chrome */
	background-image:    -moz-linear-gradient(#ffffff /*{a-bar-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{a-bar-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#ffffff /*{a-bar-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{a-bar-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#ffffff /*{a-bar-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{a-bar-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#ffffff /*{a-bar-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{a-bar-background-end}*/);
	<? } else { ?>
	background-image: -webkit-gradient(linear, left top, left bottom, from( #ffffff /*{a-bar-background-start}*/), to( #2d3642));
	background-image: -webkit-linear-gradient(#ffffff /*{a-bar-background-start}*/, #2d3642 /*{a-bar-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient(#ffffff /*{a-bar-background-start}*/, #2d3642 /*{a-bar-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#ffffff /*{a-bar-background-start}*/, #2d3642 /*{a-bar-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#ffffff /*{a-bar-background-start}*/, #2d3642 /*{a-bar-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#ffffff /*{a-bar-background-start}*/, #2d3642 /*{a-bar-background-end}*/);
	<? } ?>
    border: 0px;
}
.ui-overlay-a, .ui-page-theme-a, .ui-page-theme-a .ui-panel-wrapper, .ui-panel
{
    background-image: -webkit-linear-gradient(#<?=$qrydesignchk["global_background_color"] ?>,#<?=$qrydesignchk["global_background_color"] ?>);
	//background: #<?$qrydesignchk["global_background_color"] ?> !important;  
}
.ui-mobile #mainpage .ui-footer li{
	border: none;
	font-weight: bold;
    background: transparent;
    text-shadow: none;
}

			<? $color = Hexconvert($qrydesignchk["tab_tint"]); $transparency = $qrydesignchk["tab_tint_opacity"]*.5/100;?>
			.ui-mobile #mainpage .ui-footer .ui-btn, .ui-mobile #mainpage .ui-footer .ui-btn:hover 
			{
				//background: rgba(<?//$color['r'].",".$color['g'].",".$color['b'].",".$transparency?>); 
				padding-top: 47px;
                //color: #<?//$qrydesignchk["tab_text"]?>;
			    font-size: 10px;
			    height: 100%;
			}
			#mainpage .ui-footer .ui-btn-icon-top:after {
				width: 40px!important; height: 35px!important;
                margin-left: -20px !important;
                background-size: 40px 35px !important;
			}
			#mainpage .ui-footer {
				background:none;
				bottom: 0px;
				left: 0px;
				text-align: center;
				width: 100%;
                <? if($qrydesignchk[with_moreview]) { ?>
				position: absolute;
                <? }?>
                border: none;
			}
			#mainpage .ui-footer{
				height: 65px;
			}

.ui-btn-a,
.ui-btn-a:hover,
.ui-btn-a-active {
	<? if ($isnewdesign=='5') { ?>font-family: <?=$qrydesignchk["tab_font"]?> /*{global-font-family}*/;<? } else {?>font-family: Helvetica, Arial, sans-serif;<? } ?>
    <? if ($isnewdesign=='1') { ?>color: #<?=$qrydesignchk["tab_text"]?>; <? }?>
}
.evenrowbackground, .locationdesc .ui-collapsible-content .evenrowbackground{
	<? $ec = Hexconvert(isset($qrydesignchk["even_row_color"])?$qrydesignchk["even_row_color"]:"fff" )?>
	background: rgba(<?=$ec[r]?>,<?=$ec[g]?>,<?=$ec[b]?>,0.5);
    color: #<? echo isset($qrydesignchk["even_row_text_color"])?$qrydesignchk["even_row_text_color"]:"000" ?> !important;
}
.evenrowbackground .ui-btn{
color: #<? echo isset($qrydesignchk["even_row_text_color"])?$qrydesignchk["even_row_text_color"]:"000" ?> !important;
}
.oddrowbackground, .locationdesc .ui-collapsible-content .oddrowbackground{
	<? $oc = Hexconvert(isset($qrydesignchk["odd_row_color"])?$qrydesignchk["odd_row_color"]:"fff" )?>
	background: rgba(<?=$oc[r]?>,<?=$oc[g]?>,<?=$oc[b]?>,0.5);
    color: #<? echo isset($qrydesignchk["odd_row_text_color"])?$qrydesignchk["odd_row_text_color"]:"000" ?> !important;
}
.oddrowbackground .ui-btn{
	color: #<? echo isset($qrydesignchk["odd_row_text_color"])?$qrydesignchk["odd_row_text_color"]:"000" ?> !important;
}

.oddrowbackground:active, .evenrowbackground:active, .ui-panel .evenrowbackground .ui-btn:active,.ui-panel .oddrowbackground .ui-btn:active,.ui-page-theme-a .ui-btn.ui-btn-active, html .ui-bar-a .ui-btn.ui-btn-active, html .ui-body-a .ui-btn.ui-btn-active, html body .ui-group-theme-a .ui-btn.ui-btn-active, html head+body .ui-btn.ui-btn-a.ui-btn-active, .ui-page-theme-a .ui-checkbox-on:after, html .ui-bar-a .ui-checkbox-on:after, html .ui-body-a .ui-checkbox-on:after, html body .ui-group-theme-a .ui-checkbox-on:after, .ui-btn.ui-checkbox-on.ui-btn-a:after, .ui-page-theme-a .ui-flipswitch-active, html .ui-bar-a .ui-flipswitch-active, html .ui-body-a .ui-flipswitch-active, html body .ui-group-theme-a .ui-flipswitch-active, html body .ui-flipswitch.ui-bar-a.ui-flipswitch-active, .ui-page-theme-a .ui-slider-track .ui-btn-active, html .ui-bar-a .ui-slider-track .ui-btn-active, html .ui-body-a .ui-slider-track .ui-btn-active, html body .ui-group-theme-a .ui-slider-track .ui-btn-active, html body div.ui-slider-track.ui-body-a .ui-btn-active, html body .ui-flipswitch.ui-bar-b.ui-flipswitch-active {
	color: #<?=$qrydesignchk["feature_button"]?>;
	background-color: #<?=$qrydesignchk["navbar_bg"]?>;
    border-color: #<?=$qrydesignchk["navbar_bg"]?>;
}

.ui-page-theme-a .ui-radio-on:after, html .ui-bar-a .ui-radio-on:after, html .ui-body-a .ui-radio-on:after, html body .ui-group-theme-a .ui-radio-on:after, .ui-btn.ui-radio-on.ui-btn-a:after
{
	 border-color: #<?=$qrydesignchk["navbar_bg"]?>;
}

.ui-bar-b {
background: rgba(115, 115, 115, 0.7);
border: 1px solid rgba(115, 115, 115, 0.7);
}
<?$color = Hexconvert($qrydesignchk["section_bar_color"]);?>
.ui-bar-d, .locationdesc h4, .locationdesc h4 a {
	border: 0px solid 		#ccc /*{d-bar-border}*/;
	background: 			#bbb /*{d-bar-background-color}*/;
    <? if ($qrydesignchk["section_text_color"]) { ?> 
	color: 					#<? echo $qrydesignchk["section_text_color"]?> !important;
    <? } else { ?>
    color: 					#333;
    <? } ?>
	text-shadow: none;
    <? if ($qrydesignchk["section_bar_color"]) { ?> 
	background: rgba(<?=$color[r].",".$color[g].",".$color[b].",0.5"?>);
    border: 0px solid 		#<? echo $qrydesignchk["section_bar_color"]?>;
    <? } else { ?>
	background-image: -webkit-gradient(linear, left top, left bottom, from( #ddd /*{d-bar-background-start}*/), to( #bbb /*{d-bar-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/);
	<? } ?>
}
.body { -webkit-backface-visibility: hidden; backface-visibility:hidden; -moz-backface-visibility:hidden; }
.ui-panel .ui-bar-d
{
	background: rgba(<?=$sc[r]?>,<?=$sc[g]?>,<?=$sc[b]?>,1);	
}
.ui-panel .evenrowbackground{
    <? $ec = Hexconvert(isset($qrydesignchk["even_row_color"])?$qrydesignchk["even_row_color"]:"fff" )?>
    background: rgba(<?=$ec[r]?>,<?=$ec[g]?>,<?=$ec[b]?>,1)!important;
    color: #<? echo isset($qrydesignchk["even_row_text_color"])?$qrydesignchk["even_row_text_color"]:"000" ?> !important;
}
.ui-panel .oddrowbackground{
	<? $oc = Hexconvert(isset($qrydesignchk["odd_row_color"])?$qrydesignchk["odd_row_color"]:"fff" )?>
	background: rgba(<?=$oc[r]?>,<?=$oc[g]?>,<?=$oc[b]?>,1) !important;
    color: #<? echo isset($qrydesignchk["odd_row_text_color"])?$qrydesignchk["odd_row_text_color"]:"000" ?> !important;
}

#nav-panel .ui-collapsible .ui-btn {
border: 0px;
}

		.nav-search .ui-collapsible img
		{
		margin: -6px;
		position: absolute;
		width:25px; 
		height:25px;
		}
		.nav-search .ui-collapsible .cat
		{
			margin-left: 28px;
		}
		.nav-search .ui-collapsible-content
		{
			padding: 0px;
		}
		#nav-panel .ui-collapsible {
        margin: 0px;
        }
		#nav-panel .ui-collapsible-content>.ui-listview {
			margin: 0px;
		}


/* -----------------------------------------------------------------------------------------------------------*/
<? if ($launcherheader)
{
	
	HexToRGB($qrydesignchk["header_tint"],$launcherheader,$qrydesignchk["header_tint_opacity"],"launcher".$app_id);
?>
.ui-mobile #mainheader
{
	background-image: url(images/header/<?="launcher".$app_id.".png"?>);
    border: none;
}
<? }?>

#mainheader a 
{
padding-left: 20% !important;
}

.ui-mobile #mainpage .ui-btn, .ui-mobile #mainpage .ui-btn:hover{
	background: transparent;
    border: none;
    font-size: 13px;
   	padding: .4em 4px .5em;
    text-shadow: none;
}
.ui-footer .ui-btn, .ui-footer .ui-btn:hover,.ui-footer .ui-btn:visited   
{
	color: #<?=$qrydesignchk["navigation_text_color"]?>;
	background: #<?=$qrydesignchk["global_header_tint"]?>;
    border-color: #<?=$qrydesignchk["global_header_tint"]?>;
}

.ui-li {
	border: none;
}

#mainpage .ui-btn, #mainpage .ui-btn:hover
{
	color: 					#<?=$qrydesignchk["navigation_text_color"]?>;
}

/* Structure */


#mainpage .ui-content{padding:0;}

.ui-content
	{
	background-size: 100% 100%;
    -webkit-background-size: 100% 100%;}
    
.touchslider {
			width: 100% !important;
			height: 100% !important;
            /*
			-webkit-animation-name: slideInRight;
			animation-name: slideInRight;
			-webkit-animation-duration: 4s;
            animation-duration: 4s;
			-webkit-animation-iteration-count: 1;
            animation-iteration-count: 1;
			-webkit-animation-direction: alternate;
            animation-direction: alternate;
            */
		}
		.touchslider-item {
			width: 100% !important;
			height: 100% !important;
		}
		.touchslider .touchslider-viewport {
			/*border: 5px solid #fff1e0;
			background: #fff1e0;*/
			/*-webkit-border-radius: 6px;
			        border-radius: 6px;*/
					
		}
		.touchslider .touchslider-item {
			overflow: hidden;
		}
		.touchslider .touchslider-nav {
			text-align: center;
			margin-top: 16px;
		}
		.touchslider .touchslider-nav a {
			cursor: pointer;
			color: #000;
		}
		.touchslider .touchslider-nav a:active {
			background: #689db2;
		}
        .ui-btn-hover-e {
 			background-image: none;  
            background: none;  
            text-shadow: none;   
            border: none;    
        }
        .ui-dialog .ui-content, .ui-popup .ui-content {
        height: auto !important;
        }
        .ui-popup .ui-content
        {
        padding:20px !important;
        }
        
        .ui-popup-container{
		max-width: 92.5%;
        width:400px;
        }
        
		/**** FAN WALL ****/
        
	#fanwall .messages {
		//width: 100%;
		padding-top: 5px;
		padding-bottom: 5px;
        //margin-bottom: 25px;
		margin: 10px 1em 15px;		/* edited by SAZZAD */
        /*border-radius: 0.6em;*/   /* edited by SAZZAD */
        background: transparent; 		/* edited by SAZZAD */
        display: block;
		}
	
	#fanwall #innerpage-cmmnt{
		background-color: rgba(255,255,255,0.7);
		margin:0 0px 25px;
	}
	
	#fanwall .ui-icon-addcomment:after {
		content: "\f086"
	}
	
    #fanwall .thumbnail img { 	/* edited by SAZZAD */
		width: 60px;
        height: 60px;
        /*border-radius: 0.6em;*/
		border:2px solid #808080; /*new*/
		border-radius:50%; /*change*/
        margin: auto 0px;
		display: block;
        } 
	
	#fanwall .arrw{		/*Newly added*/ /* edited by SAZZAD */
			width:0px;
			height:0px;
			border-top:7px solid transparent;
			border-bottom:7px solid transparent;
			border-right:10px solid #fff;
			float:left;
			margin-top:10px;
		}
	
	#fanwall .cmmnt-box{	/*Newly added*/ /* edited by SAZZAD */
			//width:65%;
			background:#fff;
			margin-left:74px;
			border-radius:6px;
		}
        
    #fanwall .name {
    	margin: 0px 0px 5px 10px;		/*Newly added*/ /* edited by SAZZAD */
		color: #000;		/*Newly added*/ /* edited by SAZZAD */
		font-size: 16px;		/*Newly added*/ /* edited by SAZZAD */
		padding: 7px 0 5px 0;		/*Newly added*/ /* edited by SAZZAD */
		//border-bottom: 2px dashed #B3AFAF;
        //margin: 0 0 10px 80px;
        //color: #b3afaf;
        //font-size: 12px;
        //padding-bottom: 5px;
		display: block;
		font-weight: normal;		/* edited by SAZZAD */
        }
    
    #fanwall .comment {
        color: #b3afaf;
        font-size: 12px; 		/* edited by SAZZAD */
		padding: 0 10px 5px 10px;			/* edited by SAZZAD */
        //padding-bottom: 5px;
        display: block;
		font-weight: normal;		/* edited by SAZZAD */
		}
	
	#fanwall .childcmnt 
	{
		padding-top: 10px;
	}
    
    #fanwall .parentcmnt {
    	padding-top: 30px
    }    
    #fanwall A:visited,#fanwall A:link,#fanwall A:active,#fanwall A:hover{text-decoration: none}
    
    #fanwall .ui-grid-a {        
        //margin-top: 30px;		/* edited by SAZZAD */
        margin-left: 10px;
        display: block;
        //font-size: 14px; 		/* edited by SAZZAD */
        color: #b3afaf;
        }    
    #fanwall .ui-grid-a img{
        float: left;
        padding-top: 1.5px;
        padding-right: 5px;
        }
		
	#fanwall .ui-block-a {
		width:40%;		/* edited by SAZZAD */
		padding:5px 0 5px 0px;		/* edited by SAZZAD */
		font-size: 12px; 		/* edited by SAZZAD */
		font-weight: normal;		/* edited by SAZZAD */
	}
	
	#fanwall .ui-block-b {
			width:90px;		/* edited by SAZZAD */
			padding:1px 0;		/* edited by SAZZAD */
			border:1px solid #<?=$qrydesignchk[navbar_bg]?>;		/* edited by SAZZAD */
			border-radius:20px;		/* edited by SAZZAD */
			float: right;
			margin: 0 7px 7px 0;		/* edited by SAZZAD */
			//margin-right: 0px;
			color: #<?=$qrydesignchk[navbar_bg]?>;		/* edited by SAZZAD */
			text-align:center;		/* edited by SAZZAD */
			font-size:12px;		/* edited by SAZZAD */
			//padding-left: 25%;	/* edited by SAZZAD */
			font-weight:normal;
		}
	
	#fanwall .ui-btn-right, #fanwall .ui-btn-right:after {
		background: transparent;
		border: none;
		border-color: transparent;
		background-color: transparent;
		-webkit-box-shadow: none;
		-moz-box-shadow: none;
		box-shadow: none;
		height: 22px;
		//top: 8px;
	}
	
	
   	/*#fanwall .ui-block-b {     
        float: right;
        margin-right: 0px;
        padding-left: 25%;
        }*/
    
    .error
      {
          background-color: #CC3300;
          border: 1px solid #E1E16D;
          border-radius: 0 4px 4px 0;
          box-shadow: 0 0 6px #DDDDDD;
          color: #FFFFFF;
          display: none;
          font-size: 12px;
          height: 15px;
          padding: 4px 10px;
          border-radius: 6px;
      }
      .error em
      {
          display: block;
          width: 0;
          height: 0;
          border: 10px solid;
          border-color: #CC3300 transparent transparent; /* positioning */
          position: absolute;
          bottom: -17px;
          left: 60px;
      }
      .error > p
      {
          margin:0 !important;
      }
    .ui-btn-up-f,
    .ui-btn-hover-f {
	border: 1px solid 		#000 /*{a-bhover-border}*/;
	background: 			#444 /*{a-bhover-background-color}*/;
	font-weight: bold;
	color: 					#fff /*{a-bhover-color}*/;
	text-shadow: 0 /*{a-bhover-shadow-x}*/ 1px /*{a-bhover-shadow-y}*/ 1px /*{a-bhover-shadow-radius}*/ #111 /*{a-bhover-shadow-color}*/;
	background-image: -webkit-gradient(linear, left top, left bottom, from( #555 /*{a-bhover-background-start}*/), to( #383838 /*{a-bhover-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient( #555 /*{a-bhover-background-start}*/, #383838 /*{a-bhover-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient( #555 /*{a-bhover-background-start}*/, #383838 /*{a-bhover-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient( #555 /*{a-bhover-background-start}*/, #383838 /*{a-bhover-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient( #555 /*{a-bhover-background-start}*/, #383838 /*{a-bhover-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient( #555 /*{a-bhover-background-start}*/, #383838 /*{a-bhover-background-end}*/);
    }
    .ui-btn-up-f:visited,
    .ui-btn-up-f a.ui-link-inherit,
    .ui-btn-hover-f:visited,
    .ui-btn-hover-f:hover,
    .ui-btn-hover-f a.ui-link-inherit {
        color: 					#fff /*{a-bhover-color}*/;
    }
    
    	.ui-btn-left{
		text-decoration:none
        }
    
    .ui-mobile .ui-listview>.ui-li-has-icon>.ui-btn>img {
    max-height: 25px;
    max-width: 25px;
    }
    
    @-webkit-keyframes slideInRight {
        0% {
            opacity: 0;
            -webkit-transform: translateX(2000px);
        }
        
        100% {
            -webkit-transform: translateX(0);
        }
    }
    
    keyframes slideInRight {
        0% {
            opacity: 0;
            transform: translateX(2000px);
        }
        
        100% {
            transform: translateX(0);
        }
    }
    
    /*Mailing List */
   
       #mailinglist #mailcontainer{
        //border-radius: 0.63em;  		/*edited by SAZZAD*/
		//background: #ddd;				/*edited by SAZZAD*/
        //padding: 0px 20px 10px 20px;			/*edited by SAZZAD*/
	}
    
    #mailinglist #mailcontainer .ui-field-contain {
    border-width: 0;
    padding: 0;
    margin: 1em 0;
    }
    
     #mailinglist .ui-block-a{
    height: 60px !important;
	width: 50% !important;
	padding-top: 23px;
	background: transparent;
    }
    
    #mailinglist .ui-block-b{
    height: 60px;
	padding-left: 10%;
	width: 35%;
    }
    
    #mailinglist #mailcontainer .title{
        padding: 20px 20px 20px 5px;
    }
    
    #mailinglist .fields{
        //border-top: #ccc dashed 3px;				/*edited by SAZZAD*/
        //border-bottom: #ccc dashed 3px;			/*edited by SAZZAD*/
		position: relative;
    }
	
	
    
    #mailinglist #mailcontainer div.ui-input-text.ui-mini {
		//margin: .25em 0;
		//margin: 13px;							/*edited by SAZZAD*/
    }
	
	/*#mailinglist #mailcontainer div.ui-input-text.ui-mini:after {
		content: "";
		background: #808080;
		opacity: 0.5;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		position: absolute;
		z-index: -1;
	}*/
    
    #mailinglist #mailcontainer .title .h1{
    padding: 3px 0px 0px 40px;
    }
    
	#mailinglist #mailcontainer div.containermail {
	width: 28px;
	height:22px;
	float: left;
	}

	
    #mailinglist .ui-submit{
    	margin-top: 10px;
    }
    
    #mailinglist .submittedmail
	{
    	color:#<? echo $qrydesignchk["feature_text"]?>
    }
	
	#mailinglist .abvcircle
	{
	<? $color = Hexconvert($qrydesignchk["global_background_color"]);?>
	background: rgba(<?=$color['r'].",".$color['g'].",".$color['b'].",0.5" ?>); 
	padding-top: 14px;
	}
	#mailinglist .top-circle{
		height: 100px;
		width: 100px;
		margin: 0 auto;
		border: 2px solid #<?=$qrydesignchk["feature_text"];?>;
		border-radius: 50%;
		background-image: url(/custom_images/<?=$appcode?>/mailing_list_logo.jpg?width=100&height=100)
	}
	
	#mailinglist #mailcontainer div.ui-input-text{
		
		height: 0px;
		border: none;
		margin: 0;
		box-shadow: none;
	}
	
	#mailinglist .ui-content .oddrowbackground{
		width:100%;
		height:65px;
		margin:0px auto 0 auto;
		}
		
	#mailinglist .ui-content .evenrowbackground{
		width:100%;
		height:65px;
		margin:0px auto 0 auto;
		}
	
	#mailinglist input{
		z-index:2;
		position:absolute;
		left:1%;
		text-indent:20px;
		text-align: right;
		font-size:14px;
		width:98%;
		height:45px;
		margin:0 auto;
		background:#F8F6F7;
		border:2px solid #F8F6F7;
		border-radius:7px;
		opacity:0.7;
		}
		
	#mailinglist input[name="name"]{
		top:10px;
		}
		
	#mailinglist input[name="email"]{
		top:75px;
		}
		
	#mailinglist input[name="birthday"]{
		top:140px;
		}
		
	#mailinglist input[name="zip"]{
		top:205px;
		}
	#mailinglist input[name="country"]{
		top:270px;
		}
	
	#sel_country{
		
	}
	#mailinglist input[name="comment"]{
		top:335px;
		}
	
	#mailinglist #action{
		width: 60%;
		margin: 10px auto;
	}
	
	#mailinglist .req-opt{
		float: left;
		color: #000;
		font-size: 14px;
		margin: 23px 0 0 2.5%;
		z-index: 5;
		position:relative;
	}
	
	#mailinglist #select-wrap{
		width:98%;
		height:45px;
		overflow: hidden;
		position:absolute;
		left:1%;
		top: 270px;
		margin:0 auto;
		background:#F8F6F7;
		border:0px solid #F8F6F7;
		border-radius:7px;
		opacity:0.7;
	}
	
	#mailinglist #select-wrap span{
		float: right;
		margin:13px 8px 0 0;
		font-size:14px;
		color:#a4a4a4;
	}
	
	#mailinglist .ui-select{
		z-index:2;
		width:98%;
		height:45px;
		text-indent:20px;
		position:absolute;
		left:1%;
		top: 259px;
		font-size:20px;
		background:#F8F6F7;
		border:0px solid #F8F6F7;
		border-radius:7px;
		opacity:0.7;
	}	
	
	
	#mailinglist .ui-select .ui-btn{
		padding-right: 0;
		padding-bottom: 0.4em;
		border-radius:7px;
	}
	
	#mailinglist .ui-select span{
		text-align: right;
		font-size: 14px;
		font-weight: 100;
		color: #a4a4a4;
		margin: 0px 5px 0 0;
		height: 25px;
	}
	
	#mailinglist .ui-select span.blkcolor{
		color: #000;
	}
	
	#mailinglist .ui-btn-icon-right:after{
		background: none;
	}

	#mailinglist #mailcontainer{
		position: relative;
	}
    
    .ui-popup-screen.in
    {
    opacity:0.3;
    }
    
    .ui-li-aside 
    {
    width: 15%;
    }
    
    #mainpage .ui-icon-callus:after{
    //background-image: url(images/callus.png);
    content: "\f095";
    top:9px;
    }
    <? 
	if (function_exists('get_app_bg_html5')) { 
	$image_file = get_app_bg_html5($conn, '0', '0', '0',$app_code, $app_id);?>
    #mainpage <? if ($qrydesignchk[is_background] =='0') { ?>.maincontent <? }?> {
    	background:url(<?=$image_file; ?>) no-repeat; 
    	background-size: 100% 100%; 
    	-webkit-background-size: 100% 100%;"
    }
	<? }?>
    
    .locationdesc .ui-content .ui-btn-icon-top:after, .locationdesc .ui-header .ui-btn-icon-right:after  
    {
    //margin-left: -9px;
    -webkit-border-radius:0px;
    -border-radius:0px;
    top:6px;
    background-color: transparent;
    }
    
    .locationdesc .ui-icon-callus:after {
    //background-image: url(images/callusbutton.png);
    content: "\f095";
    //-webkit-transform: rotate(137deg);
    //top: 15px !important;
    }
    
    .locationdesc .ui-content, .locationdesc .ui-btn
    {
        font-weight: 100 !important;
    }
    
    .locationdesc .loc_comment .ui-block-a
    {
    	width:20%;
    }
    
    .locationdesc .loc_comment .ui-block-b
    {
    	width:55%;
        font-size: 13px;
    }
    
    .locationdesc .loc_comment .ui-block-b p
    {
    padding-top: 15px;
    }
    
    .pin {
    left: 3px;
    position: absolute;
    list-style-type: disc;
	//background: transparent;
	display: inline-block;
	//border-radius: 20px 14px 20px 0;
	border-radius: 60% 50% 90% 15%/90% 50% 60% 15%;
    width: 50px;
	height: 50px;
	background: #<?=$qrydesignchk[navbar_bg]?>;
    //border: 8px solid #f33;
	-webkit-transform: rotate(-45deg);
	-moz-transform: rotate(-45deg);
	-ms-transform: rotate(-45deg);
	-o-transform: rotate(-45deg);
	transform: rotate(-45deg);
	}
	
	.pin:after {
	height: 20px;
	width: 20px;
	content: '';
	background: #fff;
	border-radius: 10px;
	display: block;
	position: absolute;
	left: 17px;
	top: 24%;
	}
    
    .locationdesc .loc_comment .ui-block-c
    {
    	width:25%;
        font-size: 10px;
    }
    
    .locationdesc .locationday .ui-grid-a .ui-block-a
    {
    	width:40%;
    }
    
    .locationdesc .locationday .ui-grid-a .ui-block-b
    {
    	width:60%;
    }
    
    #mainpage .ui-icon-directions:after{
    border: none;
    height: 30px;
    top: 7px;
    content: "\f124";
    background-color: transparent;
    -webkit-box-shadow: 0 0 0;
	-moz-box-shadow: 0 0 0;
	box-shadow: 0 0 0;
    }
    
    .locationdesc .ui-icon-directions:after{
    border: none;
    content: "\f124";
    }
    
    .locationdesc .ui-header .ui-btn-icon-right
    {
    	background:transparent;
    }
    
    .speech-bubble {
    position: relative;
    width: 175px;
    padding: 15px;
    padding-left: 30px;
    color: #<?=$qrydesignchk["feature_button"]?>;
    background: #<?=$qrydesignchk[navbar_bg]?>;
    -moz-border-radius: 0 10px 10px 0;
    -webkit-border-radius: 0 10px 10px 0;
    border-radius: 0 10px 10px 0;
    position: absolute;
	left: 7px;
    }
    
     /* creates triangle */
    .speech-bubble:after {
    content: "";
    display: block;
    position: absolute;
    bottom: -34px;
    left: -0.5px;
    width: 0;
    height: 0;
    border-width: 20px 0px 15px 18px;
    border-style: solid;
    border-color: #<?=$qrydesignchk[navbar_bg]?> transparent transparent transparent;
    }

    .locationdesc .ui-icon-emailus:after {
    //background-image: url(images/contactbutton.png);
    content: "\f0e0";
    margin-left: -13px;
    }
    
    #mainpage .ui-icon-share:after{
    //background-image: url(images/sharefriend.png);
    content: "\f045";
    top: 9px;
    }
    
    .ui-header .ui-btn-right .ui-btn, .ui-header .ui-btn-right .ui-btn:after, .ui-header .ui-btn-right .ui-btn:hover, .ui-header .ui-btn-right, .ui-header .ui-btn-right:after 
    {
    	background: transparent;
    	border: none;
    	//top: -4px;
    	height:15px;
    }
    
    .ui-icon-share:after, .ui-icon-share:hover 
    {
		content: "\f045";
    }
    
    .locationdesc .ui-icon-share:after,.locationdesc .ui-icon-share,.locationdesc .ui-icon-share:hover
    {
    	border: none;
		height: 30px;
		top: 7px;
		content: "\f045";
		background-color: transparent;
		-webkit-box-shadow: 0 0 0;
		-moz-box-shadow: 0 0 0;
		box-shadow: 0 0 0;
    }
    
    .locationdesc .ui-icon-website:after {
    //background-image: url(images/webbutton.png);
    content: "\f015";
    }
    
    #mainpage .ui-btn-icon-left>.ui-btn-inner>.ui-icon:after{
    width: 30px;
    height: 30px;
    top: 35%;
    background-color: transparent;
    }
    
   	#mainpage .ui-btn-icon-left:after, #mainpage .ui-btn-icon-right:after, #mainpage .ui-btn-icon-top:after, #mainpage .ui-btn-icon-bottom:after, #mainpage .ui-btn-icon-notext:after 
    {
    background-color: transparent;
    border-radius: 0px;
    }
    
    #mainpage .ui-header .ui-btn-icon-left .ui-icon {
    left:1px;
    }
    
    .ui-body-c textarea{
    color: #000;
    }
    
    .locationdesc #city {
    text-align: center;
    padding-top: 10px;
    }
    
    .locationdesc #subheader {
    text-align: center;
    font-size: 12px;
    padding-bottom: 5px;
    background: #666;
    opacity: 0.8;
    text-shadow: none;
    color:#fff;
    margin-left:-15px; 
    margin-right:-15px;
    }
    
    .locationdesc .images .ui-block-a {
    padding: 25px 3px 0 0;
    }
    .locationdesc .images .ui-block-b {
    padding: 25px 0 0 3px;
    }
    
    .locationdesc .ui-mini .ui-btn-inner
    {
    padding: 0px;
    }
    
    .locationdesc .ui-grid-a img
    {
    border-radius: 10px;
    }
    .locationdesc .loc_comment img
    {
    border-radius: 25px !important;
    background:	#ccc;
    width: 50px;
	height: 50px;
    }
    .locationdesc .ui-navbar ul {
    background: #<?=$qrydesignchk["global_header_tint"]?>;
    -moz-box-shadow: 0px 0px 10px #000;
    -webkit-box-shadow: 0px 0px 10px #000;
    box-shadow: 0px 0px 10px #000;
    }
    
    .locationdesc #openhours
    {
    font-size:12.5px;
    font-weight:100;
    padding-left: 10px
    }
    
    
    .locationdesc .ui-content .ui-btn, .locationdesc .ui-content .ui-btn:hover {
    border-style: solid;
    border-width: 0px;
    //border-image: url(images/seperator.png) 0 1 fill repeat;
    background:transparent;
    }
    
    .ui-btn-b, .ui-btn-b:hover, .ui-popup .ui-btn-b,.ui-popup .ui-btn-b:hover {
    background: #<?=$qrydesignchk["navbar_bg"]?> !important;
    color: #<?=$qrydesignchk["feature_button"]?> !important;
    border: none;
    text-shadow: none;
    }
    
    .locationdesc .ui-navbar li:last-child .ui-btn
    {
    border-right-width:0px;
    border-radius: 0 10px 10px 0;
    }
    
    .locationdesc .ui-navbar li:first-child .ui-btn
    {
    border-left-width:0px;
    border-radius: 10px 0 0 10px;
    }
    
    .locationdesc #openhours
    {
    font-size:12.5px;
    font-weight:100;
    padding-left: 10px
    }
    
    .rssdesc .container{
    padding: 10px;
    font-size: 12px;
    }
    
    .rssdesc #rssdate{
    width: 181px;
    height: 48px;
    position: absolute;
    color: #<?=$qrydesignchk["feature_button"]?>;
    top: 80px;
    left: 13px;
    font-size: 13px;
    }
    
    .rssdesc #rssdate img{
    -webkit-filter: hue-rotate(<?=$HSV['H']?>) saturate(<?=$HSV['S']?>) brightness(<?=$HSV['V']?>);
    }
    
    .rssdesc .date {
    position: absolute;
    top: 8px;
    left: 20px;
    }
    
    .rssdesc #title
    {
    padding: 70px 20px 15px;
    background: #ddd;
    border-bottom: #ccc dashed 3px;
    border-radius: 0.6em 0.6em 0 0;
    }
    
    .rssdesc #summary
    {
    padding:20px;
    background: #ddd;
    border-radius: 0 0 0.6em 0.6em;
    }
    
    .homebar
    {
    	position: absolute;
		bottom: 71px;
		margin: 0 auto;
		/* float: right; */
		width: 100%;
    }
    .homebar li
    {
    	margin: 0 1%;
    }
    
    .homebar .ui-grid-b>.ui-block-a,.homebar .ui-grid-b>.ui-block-b,.homebar .ui-grid-b>.ui-block-c 
    {
	width: 31%;
	}
	.homebar .ui-grid-a>.ui-block-a,.homebar .ui-grid-a>.ui-block-b 
	{
	width: 48%;
	}
	.homebar .ui-grid-solo>.ui-block-a {
	width: 98%;
	float: none;
	}
	
    
    @media screen and (max-width: 250px) {
    	.ui-panel {
    		width:100%
    	}
    }
    
    #newfooter .ui-btn-icon-top:after 
    {
	top: 0.15em;
	}
	
    <? if ($qrydesignchk["btn_layout"]=='0' || $qrydesignchk["btn_layout"]=='2')
	{?>
    #newfooter
    {
    -webkit-transform-origin: bottom right;
    -webkit-transform: rotate(90deg);
    transform: rotate(90deg);
    transform-origin:bottom right;
    -ms-transform: rotate(90deg); /* IE 9 */
    -ms-transform-origin:bottom right; /* IE 9 */
    }
	
    #mainpage .ui-footer .ui-navbar ul li
    {
    -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
    -ms-transform: rotate(-90deg);
    }
    #newfooter #wrapper
    {
    overflow: scroll !important;
    -webkit-overflow-scrolling: touch !important;
    }
    #newfooter .table
    {
    display:none;
    }
	<?
	}
	else
	{?>
    #newfooter #scroller
    {
    position: relative !important;
    }
    <?
    }
	?>
	
    #newfooter .ui-navbar li .ui-btn 
    {
    display: block;
    text-align: center;
    margin: 0 -1px 0 0;
    }
    <?
    if (isset($_SESSION[path]) && $_SESSION[path]!="")
	{
	include_once("orderhtml5/style.php");	
	}
    ?>
    

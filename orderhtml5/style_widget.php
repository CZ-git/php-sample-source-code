<?
header("Content-type: text/css; charset: UTF-8");
include_once("dbconnect.inc");
session_start("rest_id");
$app_code = $_SESSION['appcodeorder'];
$sql = "select id from apps where code like '$app_code'";
$res = mysql_query($sql, $conn);
$app_id = mysql_result($res, 0, 0);
$sqldesignchk = "SELECT a.isNewDesign, a.navigation_text_shadow_color, a.navigation_bar_color, a.navigation_text_color, a.even_row_color, a.even_row_text_color, a.odd_row_color, a.feature_button, a.navbar_bg, a.odd_row_text_color, a.section_bar_color, a.section_text_color, d.* FROM  `template_detail` d LEFT JOIN  `template_app` p ON p.detail_id = d.id RIGHT JOIN  `apps` a ON p.app_id = a.id WHERE a.id =  '$app_id' LIMIT 0 , 1";
$resdesignchk = mysql_query($sqldesignchk, $conn);
$qrydesignchk		= mysql_fetch_array($resdesignchk);
$isnewdesign 	= $qrydesignchk["isNewDesign"]; // store new design value
include_once("../../html5/newdesigncss.php");	?>

/* Swatches */

/* A
-----------------------------------------------------------------------------------------------------------*/

.ui-bar-a {
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
	background-image: url(/html5/images/header/<?="global".$app_id.".png"?>);
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
}
.ui-body-c
{
   	color: #<?=$qrydesignchk["feature_text"];?>
}
    
.ui-btn-up-b, 
.ui-btn-hover-b,
.ui-btn-active, 
.ui-btn-active:hover{
	color: #<?=$qrydesignchk["feature_button"]?>;
	background: #<?=$qrydesignchk["navbar_bg"]?>;
    background-image: -webkit-gradient(linear, left top, left bottom, from( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/), to( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/, #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/, #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/, #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/, #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient( #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-start}*/, #<?=$qrydesignchk["navbar_bg"]?> /*{b-bup-background-end}*/);
}

.ui-checkbox-on .ui-icon
{
	background-color: #c8c8c8 /*{global-active-background-color}*/;
}

.ui-footer .ui-btn-up-a {
	border: none;
	font-weight: bold;
	/* color: 					#fff {a-bup-color}*/;
    text-shadow: 0 /*{a-bup-shadow-x}*/ -1px /*{a-bup-shadow-y}*/ 1px /*{a-bup-shadow-radius}*/ #000 /*{a-bup-shadow-color}*/;
   <? if($tab_src){?>
    background-image: url("<?=$tab_src?>");
    background-size: 100% 100%;
    <? } else {?>
    border: 1px solid 		#222 /*{a-bup-border}*/;
	background-image: -webkit-gradient(linear,0% 0%,0% 100%,from(#858585),color-stop(3%,#636363),color-stop(50%,#202020),color-stop(51%,black),color-stop(97%,black),to(#262626));
	background-image:    -moz-linear-gradient(#555 /*{a-bup-background-start}*/, #333 /*{a-bup-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#555 /*{a-bup-background-start}*/, #333 /*{a-bup-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#555 /*{a-bup-background-start}*/, #333 /*{a-bup-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#555 /*{a-bup-background-start}*/, #333 /*{a-bup-background-end}*/);
    <? }?>
}
<? if ($isnewdesign=='1') { ?>
#mainpage .ui-footer .ui-btn-text {
	color: #<?=$qrydesignchk["tab_text"]?>;
    text-shadow: none;
}
<? }?>

.ui-btn-up-a,
.ui-btn-hover-a,
.ui-btn-down-a {
	color: #<?=$qrydesignchk["feature_button"]?>;
}

.evenrowbackground{
	<? $ec = Hexconvert(isset($qrydesignchk["even_row_color"])?$qrydesignchk["even_row_color"]:"fff" )?>
	background: rgba(<?=$ec[r]?>,<?=$ec[g]?>,<?=$ec[b]?>,0.5);
    color: #<? echo isset($qrydesignchk["even_row_text_color"])?$qrydesignchk["even_row_text_color"]:"000" ?> !important;
}
.evenrowbackground, .evenrowbackground a.ui-link-inherit{
color: #<? echo isset($qrydesignchk["even_row_text_color"])?$qrydesignchk["even_row_text_color"]:"000" ?> !important;
}
.oddrowbackground{
	<? $oc = Hexconvert(isset($qrydesignchk["odd_row_color"])?$qrydesignchk["odd_row_color"]:"fff" )?>
	background: rgba(<?=$oc[r]?>,<?=$oc[g]?>,<?=$oc[b]?>,0.5);
    color: #<? echo isset($qrydesignchk["odd_row_text_color"])?$qrydesignchk["odd_row_text_color"]:"000" ?> !important;
}
.oddrowbackground, .oddrowbackground a.ui-link-inherit{
	color: #<? echo isset($qrydesignchk["odd_row_text_color"])?$qrydesignchk["odd_row_text_color"]:"000" ?> !important;
}

.ui-bar-b {
background: rgba(115, 115, 115, 0.7);
border: 1px solid rgba(115, 115, 115, 0.7);
}

.ui-bar-d .ui-btn-inner {
padding: .3em 10px;
}
.ui-bar-d {
	border: 1px solid 		#ccc /*{d-bar-border}*/;
	background: 			#bbb /*{d-bar-background-color}*/;
    <? if ($qrydesignchk["section_text_color"]) { ?> 
	color: 					#<? echo $qrydesignchk["section_text_color"]?> !important;
    <? } else { ?>
    color: 					#333;
    <? } ?>
	text-shadow: 0 /*{d-bar-shadow-x}*/ 0px /*{d-bar-shadow-y}*/ 0 /*{d-bar-shadow-radius}*/ #eee /*{d-bar-shadow-color}*/;
    <? if ($qrydesignchk["section_bar_color"]) { ?> 
	background: 			#<? echo $qrydesignchk["section_bar_color"]?>;
    border: 1px solid 		#<? echo $qrydesignchk["section_bar_color"]?>;
    <? } else { ?>
	background-image: -webkit-gradient(linear, left top, left bottom, from( #ddd /*{d-bar-background-start}*/), to( #bbb /*{d-bar-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#ddd /*{d-bar-background-start}*/, #bbb /*{d-bar-background-end}*/);
	<? } ?>
}

/* -----------------------------------------------------------------------------------------------------------*/
<? if ($launcherheader)
{
	
	HexToRGB($qrydesignchk["header_tint"],$launcherheader,$qrydesignchk["header_tint_opacity"],"launcher".$app_id);
?>
.ui-bar-e {
	background-image: url(images/header/<?="launcher".$app_id.".png"?>) !important;
    border: none;
}
<? }?>

.ui-btn-up-e {
	border: 1px solid 		#000000 /*{e-bup-border}*/;
    font-weight: bold;
	color: 					#<?=$qrydesignchk["navigation_text_color"]?> /*{e-bup-color}*/;
	text-shadow:			none;
    <? if ($isnewdesign!='1') {?>
	background: 			#fadb4e /*{e-bup-background-color}*/;
	background-image: -webkit-gradient(linear, left top, left bottom, from( #fceda7 /*{e-bup-background-start}*/), to( #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{e-bup-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient(#ffffff /*{e-bup-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?>/*{e-bup-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient(#ffffff /*{e-bup-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{e-bup-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient(#ffffff /*{e-bup-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{e-bup-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient(#ffffff /*{e-bup-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{e-bup-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient(#ffffff /*{e-bup-background-start}*/, #<? echo $qrydesignchk["navigation_bar_color"] ?> /*{e-bup-background-end}*/);
    <? } else {?>
    background: 			none;
    background-image: 		none;
    border:					none;
    <? }?>
}

.ui-btn-up-e:visited, .ui-btn-up-e a.ui-link-inherit, .ui-btn-hover-e
{
	color: 					#<?=$qrydesignchk["navigation_text_color"]?>;
}

/* Structure */

/* Active class used as the "on" state across all themes
-----------------------------------------------------------------------------------------------------------*/
#mainpage .ui-content{padding:0}

.touchslider {
			width: 100% !important;
			height: 100% !important;
			-webkit-animation-name: slideInRight;
			animation-name: slideInRight;
			-webkit-animation-duration: 4s;
            animation-duration: 4s;
			-webkit-animation-iteration-count: 1;
            animation-iteration-count: 1;
			-webkit-animation-direction: alternate;
            animation-direction: alternate;
		}
		.touchslider-item {
			width: 100% !important;
			height: 100% !important;
		}
		.touchslider .touchslider-viewport {
			/*border: 5px solid #fff1e0;
			background: #fff1e0;*/
			-webkit-border-radius: 6px;
			        border-radius: 6px;
					
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
        .ui-btn-hover-a {
            opacity: 0.8;
        }
        .ui-btn-hover-e {
 			background-image: none;  
            background: none;  
            text-shadow: none;       
        }
        .ui-dialog .ui-content, .ui-popup .ui-content {
        height: auto !important;
        }
        .ui-popup .ui-content
        {
        padding:20px !important;
        }
        .ui-footer .ui-btn-inner {
        font-size: 10px;
        }
        .ui-popup-container{
		max-width: 92.5%;
        width:400px;
        }
        #mainpage .ui-header .ui-btn-inner {
        font-size: 16px;
        }
        
		/**** FAN WALL ****/
	#fanwall div#messages, div#messagenew {
		background: #DBE1ED;
		}
	#fanwall div#messages div.bubble {
		clear: both;
		}
	#fanwall div#messages div.right {
		float: right;
		}
	#fanwall div#messages div.left {
		float: left;
		width: 300px /* For IE8 and earlier */
		}
	#fanwall div#messages div.left {
		border-width: 15px 15px 0px 15px !important;
		margin-left:-30px;
		padding-left:20px;
		padding-top:15px;
		padding-bottom:25px;
		border-image: url(images/speech_bubble.png) 0 0 0 0 stretch stretch !important;
		-o-border-image: url(images/speech_bubble.png) 0 0 0 0 stretch stretch !important;
		-moz-border-image: url(images/speech_bubble.png) 0 0 0 0 stretch stretch !important;
		-webkit-border-image: url(images/speech_bubble.png) 0 0 0 0 stretch stretch !important;
		}
	/*rights*/	
	#fanwall div#messages div.right p.clear, div#messages div.right textarea.clear {
		-webkit-border-image: url(images/speech_bubble.png) ;
        border-image: url(images/speech_bubble.png) ;
        
		}
	#fanwall div#messages div.left p {
		padding-right:20px;
		}
        
    /* Ordering Part */
    
        #orderingsystem 
        {
        background-size: 100% 100% !important;
        }
      #orderingsystem .clear {clear: both;}
      body 
      { 
      	-webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none; 
      }
      .ui-checkbox-on .ui-icon,
      .ui-radio-on .ui-icon {
            background-color: #ccc /*{global-active-background-color}*/; /* NOTE: this hex should match the active state color. It's repeated here for cascade */
        }
      .ui-focus,
      .ui-btn:focus {
            -moz-box-shadow: inset 0px 0px 3px 		#ccc /*{global-active-background-color}*/, 0px 0px 9px 		#ccc /*{global-active-background-color}*/;
            -webkit-box-shadow: inset 0px 0px 3px 	#ccc /*{global-active-background-color}*/, 0px 0px 9px 		#ccc /*{global-active-background-color}*/;
            box-shadow: inset 0px 0px 3px 			#ccc /*{global-active-background-color}*/, 0px 0px 9px 		#ccc /*{global-active-background-color}*/;
        }
      .ui-btn-active {
            border: 1px solid #ccc;
            background: #ccc;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            text-shadow: 0 1px 0 #ccc;
            text-decoration: none;
            background-image: -webkit-gradient(linear,left top,left bottom,from(#cc),to(#ccc));
            background-image: -webkit-linear-gradient(#cc,#ccc);
            background-image: -moz-linear-gradient(#ccc,#ccc);
            background-image: -ms-linear-gradient(#ccc,#ccc);
            background-image: -o-linear-gradient(#ccc,#ccc);
            background-image: linear-gradient(#ccc,#ccc);
            font-family: Helvetica,Arial,sans-serif;
      }  
      #orderingsystem .ui-input-text.ui-focus,
      #orderingsystem .ui-input-search.ui-focus {
            -moz-box-shadow: 0px 0px 12px 			#ccc /*{global-active-background-color}*/;
            -webkit-box-shadow: 0px 0px 12px 		#ccc /*{global-active-background-color}*/;
            box-shadow: 0px 0px 12px 					#ccc /*{global-active-background-color}*/;	
        }  
      #orderingsystem .cart-line {
          padding: 0px 0 5px 0;
          clear: both;
          margin-bottom: 5px;
      }
      #orderingsystem .cart-line .cart-main {
          clear: both;
          line-height: 25px;
      }
      
      #orderingsystem .cart-line .cart-detail {
          font-size: 10px;
          font-style: italic;
          clear: both;
          border: dotted 1px #aaa;
          padding: 5px;
      }
    #orderingsystem .ui-controlgroup
    { 
        margin-left: 10px;
	}
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
    .ui-btn-up-c:visited,
    .ui-btn-up-c a.ui-link-inherit,
    .ui-btn-hover-c:visited,
    .ui-btn-hover-c:hover,
    .ui-btn-hover-c a.ui-link-inherit
    {
        color: 					#000 /*{a-bhover-color}*/;
    }
    .ui-listview .ui-li-icon {
    max-height: 25px;
    max-width: 25px;
    }
    .ui-btn-hover-c {
	border: 1px solid 		#ccc /*{c-bup-border}*/;
	background: 			#eee /*{c-bup-background-color}*/;
	font-weight: bold;
	color: 					#222 /*{c-bup-color}*/;
	text-shadow: 0 /*{c-bup-shadow-x}*/ 1px /*{c-bup-shadow-y}*/ 0 /*{c-bup-shadow-radius}*/ #fff /*{c-bup-shadow-color}*/;
	background-image: -webkit-gradient(linear, left top, left bottom, from( #fff /*{c-bup-background-start}*/), to( #f1f1f1 /*{c-bup-background-end}*/)); /* Saf4+, Chrome */
	background-image: -webkit-linear-gradient( #fff /*{c-bup-background-start}*/, #f1f1f1 /*{c-bup-background-end}*/); /* Chrome 10+, Saf5.1+ */
	background-image:    -moz-linear-gradient( #fff /*{c-bup-background-start}*/, #f1f1f1 /*{c-bup-background-end}*/); /* FF3.6 */
	background-image:     -ms-linear-gradient( #fff /*{c-bup-background-start}*/, #f1f1f1 /*{c-bup-background-end}*/); /* IE10 */
	background-image:      -o-linear-gradient( #fff /*{c-bup-background-start}*/, #f1f1f1 /*{c-bup-background-end}*/); /* Opera 11.10+ */
	background-image:         linear-gradient( #fff /*{c-bup-background-start}*/, #f1f1f1 /*{c-bup-background-end}*/);
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
    
    .ui-body-c, .ui-overlay-c, .ui-btn-hover-c, .ui-btn-up-c, .ui-btn-hover-a, .ui-btn-up-a {
    	text-shadow: none;
    }
    
    #mailinglist form{
		width:80%;
		margin: 0 auto;
	}
    
    #mailinglist .submittedmail
	{
    	color:#<? echo $qrydesignchk["feature_text"]?>
    }
    
    #orderingsystem .ui-content .ui-btn-up-a,#orderingsystem .ui-content .ui-btn-hover-a:hover, .ui-btn-up-b, .ui-btn-hover-b:hover {
    background: #<?=$qrydesignchk["navbar_bg"]?>;
    -webkit-border-radius: .6em;
	border-radius: .6em;
    -moz-box-shadow: 0px 0px 10px #000;
    -webkit-box-shadow: 0px 0px 10px #000;
    box-shadow: 0px 0px 10px #000;
    border: 3px solid #f1f1f1;
    border-style: solid;
    }
    
    .ui-li, .ui-li.ui-field-contain,.ui-li.ui-last-child, .ui-li.ui-field-contain.ui-last-child {
    border-width: 0px;
	}
    .ui-controlgroup .ui-btn-icon-notext .ui-btn-inner {
    padding: 3.1px 13px;
    }
    .ui-li-divider .ui-btn-right {
    float: left;
    margin-top: -9px;
    margin-left: 0px !important;
    }
    .ui-li-divider  {
    text-align: center;
    }
    .ui-li-divider .divtext
    {
    margin-left:-71px !important;
    }
    .ui-icon-cart {
		background-image: url("/tab_icons/10/(274).png?height=18&width=18");
	}
    .ui-icon-orderback {
		background-image: url("/tab_icons/10/(223).png?height=18&width=18");
	}
    #orderingsystem .ui-li-divider .ui-icon {
   	 	background-color: #f1f1f1;
    }
    #orderingsystem .ui-panel-content-wrap-closed,#orderingsystem .ui-panel-content-wrap-display-reveal, #orderingsystem .ui-panel-content-wrap-open
    {
    background:url(<?=$_SESSION[orderbg]?>);
    }
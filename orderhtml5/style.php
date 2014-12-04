<?
$includedinorder=1;
//echo $includedinorder;
if ($includedinorder=="1")
{
	include_once "dbconnect.inc";
	session_start("rest_id");
	if(!isset($app_code) && $app_code=="")
	{
	$app_code = $_SESSION['appcodeorder'];
	$sql = "select id from apps where code like '$app_code'";
	$res = mysql_query($sql, $conn);
	}
	//echo $_SESSION['path'];
	$app_id = mysql_result($res, 0, 0);
	$sqldesignchk = "SELECT x.global_background_color, a.isNewDesign, a.navigation_text_shadow_color, a.navigation_bar_color, a.navigation_text_color, a.even_row_color, a.even_row_text_color, a.odd_row_color, a.feature_button, a.navbar_bg, a.odd_row_text_color, a.section_bar_color, a.section_text_color, d.* FROM  `template_detail` d LEFT JOIN  `template_app` p ON p.detail_id = d.id RIGHT JOIN  `apps_xtr` x ON p.app_id = x.app_id RIGHT JOIN  `apps` a ON p.app_id = a.id WHERE a.id =  '$app_id' LIMIT 0 , 1";
	//OLD SQL:$sqldesignchk = "SELECT a.isNewDesign, a.navigation_text_shadow_color, a.navigation_bar_color, a.navigation_text_color, a.even_row_color, a.even_row_text_color, a.odd_row_color, a.feature_button, a.navbar_bg, a.odd_row_text_color, a.section_bar_color, a.section_text_color, d.* FROM  `template_detail` d LEFT JOIN  `template_app` p ON p.detail_id = d.id RIGHT JOIN  `apps` a ON p.app_id = a.id WHERE a.id =  '$app_id' LIMIT 0 , 1";
	//echo "test query".$sqldesignchk;
	$resdesignchk 	= mysql_query($sqldesignchk, $conn);
	$qrydesignchk	= mysql_fetch_array($resdesignchk);
	$isnewdesign 	= $qrydesignchk["isNewDesign"]; // store new design value
	include_once "function.php";
	header("Content-type: text/css; charset: UTF-8");
	include_once "../style.php";
}
	?>

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
         
    #orderingsystem .ui-btn-up-a,#orderingsystem  .ui-btn-hover-a,#orderingsystem  .ui-btn-down-a { color: #<?=$qrydesignchk["feature_button"]?>; }
    #orderingsystem .ui-controlgroup
    { 
        margin-left: 10px;
	}
    .error
      {
          background-color: #CC3300;
          border-radius: 0 4px 4px 0;
          color: #FFFFFF;
          display: none;
          font-size: 12px;
          height: 30px;
          padding: 4px 10px;
          border-radius: 6px;
      }
      .error em
      {
          display: block;
          width: 0;
          height: 0;
          border-color: #CC3300 transparent transparent; /* positioning */
          position: absolute;
          bottom: -17px;
          left: 60px;
      }
      .error > p
      {
          margin:0 !important;
      }
	 #cartform label.error {
		color: red;
		font-size: 16px;
		font-weight: normal;
		line-height: 1.4;
		margin-top: 0.5em;
		width: 100%;
		float: none;
		background: transparent;
		border: none;
	}
	@media screen and (orientation: portrait) {
	#cartform label.error {
			margin-left: 0;
			display: block;
		}
	}
	@media screen and (orientation: landscape) {
		#cartform label.error {
			display: inline-block;
			margin-left: 22%;
		}
	}
	#cartform em {
		color: red;
		font-weight: bold;
		padding-right: .25em;
	}
    
    #orderingsystem .ui-content .ui-btn-a,#orderingsystem .ui-content .ui-btn-a:hover, .ui-btn-b, .ui-btn-b:hover, .ui-popup .ui-btn-b,.ui-popup .ui-btn-b:hover {
    background: #<?=$qrydesignchk["navbar_bg"]?> !important;
    color: #<?=$qrydesignchk["feature_button"]?> !important;
    border: none;
    text-shadow: none;
    }
    
    #orderingsystem .ui-page-theme-a .ui-btn {
    	text-shadow: none;
		border: 0px!important;
		background: transparent;
    }
    
    #orderingsystem .ui-li-divider .ui-btn-left {
		margin-top: -9px;
		margin-left: 0px !important;
	}
    
    #orderingsystem .ui-controlgroup .ui-btn-icon-notext .ui-btn-inner {
    padding: 3.1px 13px;
    }
    #orderingsystem .ui-li-divider .ui-btn-right {
    float: left;
    margin-top: -9px;
    margin-left: 0px !important;
    }
    #orderingsystem .ui-li-divider  {
    text-align: center;
    }
    /*#orderingsystem .ui-li-divider .divtext
    {
    margin-left:71px !important;
    }*/
    #orderingsystem .ui-icon-cart:after {
		background-image: url("/tab_icons/10/(274).png?height=18&width=18");
	}
    #orderingsystem .ui-icon-orderback:after {
		background-image: url("/tab_icons/10/(223).png?height=18&width=18");
	}
    #orderingsystem .ui-li-divider .ui-icon {
   	 	background-color: #f1f1f1;
    }
    #orderingsystem .ui-panel-wrapper, #orderingsystem .ui-panel-page-content
    {
    background:url(<?=$_SESSION[orderbg]?>);
    }
    #orderingsystem .ui-bar-d .ui-btn-inner {
    padding: .3em 10px;
    }
    #orderingsystem .paymentinput .ui-controlgroup-controls {
    	margin-top: 1em;
    }
    #orderingsystem .ordersubmit .ui-grid-a>.ui-block-a
    {
    	width: 25%;	
    }
    #orderingsystem .ordersubmit .ui-grid-a>.ui-block-b
    {
    	width: 75%;	
    }
	.clear {clear: both;}
	.cart-line {
		padding: 0px 0 5px 0;
		clear: both;
		margin-bottom: 5px;
	}
	.cart-line .cart-main {
		clear: both;
		line-height: 25px;
	}

	.cart-line .cart-detail {
		font-size: 10px;
		font-style: italic;
		clear: both;
		border: dotted 1px #aaa;
		padding: 5px;
	}
	

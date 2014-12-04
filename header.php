<head> 
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-title" content="<?=$qry["name"] ?>">
    <!--<meta name="apple-itunes-app" content="app-id=<?=$qry["app_store_id"] ?>">-->
    <meta property="og:title" content="<? echo $qry["name"] ?>" />
    <meta property="og:description" content="<? echo $qry["description"] ?>" />
    <meta property="og:image" content="<?="http://".$_SERVER["SERVER_NAME"]."/custom_images/".$appname."/home.jpg"?>" />
    <?
    $appicon = "../uploads/icons/$app_id.png";
    $splashscreen = "../uploads/splash_shots/$app_id.png";
    if($qry["is_redirect"])
    {
        include_once('mobile_device_detect.php');
        $apple = ($qry["app_store_url"] ? $qry["app_store_url"] : "false");
        $google = ($qry["android_url"] ? $qry["android_url"] : "false");
        mobile_device_detect($apple,$google,false,false);
    }
    ?>
    <meta property="og:image" content="<?=$appicon ?>" />
    <meta property="og:image" content="<?=$splashscreen ?>" />
    <link rel="apple-touch-icon" href="<?=$appicon ?>"/>
    <link rel="apple-touch-startup-image" href="<?=$splashscreen ?>"/>
    <title><? echo $qry["name"] ?></title>
    <link rel="stylesheet" href="css/add2home.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link href="css/jquery.alerts.css" rel="stylesheet" type="text/css" media="screen" />
    <link rel="stylesheet"  href="//code.jquery.com/mobile/1.4.3/jquery.mobile-1.4.3.min.css" />
    <link rel="stylesheet" href="style.php" /> 
    <link href="css/photoswipe.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="css/main-mobile.css" />
    <script type="text/javascript" src="js/klass.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript">
        $(document).bind("mobileinit", function(){
            $.extend(  $.mobile , {
                defaultPageTransition: 'none',
                defaultDialogTransition: 'none'
            });
        });

        /*$.mobile.page.prototype.options.backBtnText = "<?//$lngback?>";
        $.mobile.page.prototype.options.addBackBtn = true;
        $.mobile.page.prototype.options.backBtnTheme = "f";*/

    </script>
    
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/nscroll.js"></script>
    <script src="//code.jquery.com/mobile/1.4.3/jquery.mobile-1.4.3.min.js"></script>
    <script type="text/javascript" src="js/code.photoswipe.jquery-3.0.4.min.js"></script>
    
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript" src="js/jquery.ui.map.js"></script>
    <script type="text/javascript" src="js/jquery.ui.map.services.js"></script>
    <script type="text/javascript" src="js/jquery.ui.map.extensions.js"></script>
    
    <script src="js/scroll.js"></script>
    <script src="js/jquery.touchslider.js"></script>
    <script type="text/javascript" src="orderhtml5/js/libs/modernizr-2.0.6.min.js"></script>
    <script src="js/jquery.alerts.js" type="text/javascript"></script>
    <script src="js/jquery.validate.min.js" type="text/javascript"></script>
    <script type='text/javascript' src="http://cdn.jquerytools.org/1.2.6/form/jquery.tools.min.js"></script>
    <!--    <script type="application/javascript" src="js/add2home.js" charset="utf-8"></script>-->
    <script type="text/javascript">
        var previewurl=top.location.href;
        var ispreview=previewurl.indexOf("step5");
        if (top.location != self.location && ispreview<0)
        {
            top.location = self.location.href
        }		
    </script>

    <script type="text/javascript">
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    </script>
    <script type="text/javascript" src="js/functions.js"></script>
    
    	<!-- tooptipster plugin starts-->
	<link rel="stylesheet" type="text/css" href="js/tooltipster/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="js/tooltipster/css/style.css" />
	<link rel="stylesheet" type="text/css" href="js/tooltipster/css/tooltipster.css" />
	<script type="text/javascript" src="js/tooltipster/js/jquery.tooltipster.js"></script>
	<!-- tooptipster plugin starts-->

</head>
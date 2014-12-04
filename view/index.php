<body> 
    <div data-role="page" id="mainpage">
        <?
        $header = $qrydesignchk[is_normal_header];
        $footer = $qrydesignchk[with_moreview];
        $content = "0";
        makehtml5page ($header, $footer, $content);
        $view_controller = 'HomeViewController';
        $mainpage=1;
        ?>

        <script type="text/javascript">

            var x = $(window).width(); 
            $('#scroller li#main').css('width',x+'px');
            window.addEventListener("resize", function() 
                {
                    // Get screen size (inner/outerWidth, inner/outerHeight)
                    var x = $(window).width(); 
                    $('#scroller li#main').css('width',x+'px');
                }, false);

            (function() {
                var fixgeometry = function() {
                    var header = $(".ui-header:visible");
                    var content = $(".ui-content:visible");
                    var newmainheader = $(".newmainheader:visible");
                    var maincontent = $(".maincontent");
                    var viewport_height = window.innerHeight;
                    var viewport_width = window.innerWidth;
                    var content_height = viewport_height - header.innerHeight()+1;
                    var header_src = "<?=$qrydesignchk[header_src]?>";
                    var mainpage = $("#mainpage:visible").height();
                    content_height -= (content.outerHeight() - content.height());
                    content.height(content_height);	
                    //console.log('I am in normal header');
                    <? 
                    if ($qrydesignchk[is_normal_header] == '0')
                    {?>
                        content.height(viewport_height - newmainheader.height());
                        //console.log('I am in new header'+content.height());
                        <?
                    }	
                    ?>
                    if (header_src == "no header.png" && mainpage==window.innerHeight)
                    {
                        content.height(viewport_height);
                        //console.log('I am in no header '+mainpage);
                    }
                    content.niceScroll();	
                    //Fix to Collapsible go to top issue.
                    $(document).on("collapsibleexpand", "[data-role=collapsible]", function () {
                        var position = $(this).offset().top;
                        $.mobile.silentScroll(position);
                    });  
                    //console.log("Testing "+<$sandbox[1]?>);
                    var layouttab = <?=$qrydesignchk["btn_layout"]?>;
                    var moretab = <?=$qrydesignchk[with_moreview]?>;
                    var navbar_wdt = $('.ui-footer .ui-navbar ul li').width();
                    var navbar_hyt = $('.ui-footer .ui-navbar ul li').height();

                    /*$(".ui-page:visible").on( "swiperight", function( e ) {
                    if ( $.mobile.activePage.jqmData( "panel" ) !== "open" ) {
                    if ( e.type === "swiperight" ) {
                    alert('zhongzhen');
                    $( ".nav-class" ).panel( "open" );
                    }
                    }
                    });*/

                    if(layouttab == "1")
                    {
                        if(header.innerHeight()>10)
                        {
                            $('#newfooter').css( "top", "28px" );
                            $('#newfooter').css( "bottom", "initial" );
                        }
                        else
                        {
                            $('#newfooter').css( "top", "0px" );
                            $('#newfooter').css( "bottom", "initial" );
                        }
                    }
                    else
                        if(layouttab == "0")
                        {
                            $('#newfooter').css( "width", viewport_height );
                            $('#newfooter').css( "left", -1*viewport_height+48 );
                            //$('#mainpage .ui-footer .ui-navbar li, #scroller li').height(navbar_wdt);
                            $('#mainpage .ui-footer .ui-navbar li, #wrapper').height(navbar_wdt);
                            $('#mainpage .ui-footer').height(navbar_wdt-48);
                            $('.ui-header').css( "opacity", "0.8" );
                            $('.ui-mobile #mainpage .ui-footer .ui-btn,.ui-mobile #mainpage .ui-footer .ui-btn:hover').css( "margin-top", "11px");
                            $('.ui-mobile #mainpage .ui-footer .ui-btn,.ui-mobile #mainpage .ui-footer .ui-btn:hover').css( "padding-top", "40px");
                            if (moretab != "1")
                            {
                                $('#newfooter #main').css( "width", viewport_height-50 );
                                $('#newfooter').css( "left", -1*viewport_height );
                            }
                        }
                        else
                            if(layouttab == "2")
                            {
                                //var rightbar = (((viewport_width-viewport_height)*(viewport_width/viewport_height))/2)-65;
                                $('#newfooter').css( "width", viewport_height );
                                $('#newfooter').css( "left", viewport_width-viewport_height-navbar_wdt/2);
                                $('.ui-footer .ui-navbar ul li, #scroller li').height(navbar_wdt);
                                $('.ui-footer .ui-btn').height(navbar_wdt-48);
                                //$('.ui-footer .ui-navbar ul li').width(navbar_hyt);
                                $('.ui-header').css( "opacity", "0.8" );
                                //$('.ui-footer span:nth-child(2)').css( "top", "22%");
                                //$('.ui-footer span span:nth-child(1)').css( "bottom", "-15px");
                                $('.ui-mobile #mainpage .ui-footer .ui-btn,.ui-mobile #mainpage .ui-footer .ui-btn:hover').css( "margin-top", "11px");
                                $('.ui-mobile #mainpage .ui-footer .ui-btn,.ui-mobile #mainpage .ui-footer .ui-btn:hover').css( "padding-top", "40px");

                                if (moretab != "1")
                                {
                                    $('#newfooter #main').css( "width", viewport_height );
                                    $('#newfooter').css( "left", viewport_width-viewport_height-navbar_wdt);
                                }
                            }
                }; /* fixgeometry */

                $(document).ready(function() {
                    $(window).bind("orientationchange pageshow load resize", fixgeometry);
                    <?
                    if(isset($_SESSION[changepage]))
                    {?>
                        $.mobile.changePage( "<?=$_SESSION[changepage]?>", { transition: "pop"} );
                        <?
                    }
                    if($data[home][manyImages]=="YES")
                    {?>
                        $(".touchslider").touchSlider
                        ({
                            mouseTouch: true
                            <? if ($qry[sliding_type_mobile] !='0' ) { ?>
                                , duration: 350,
                                delay: 3000, 
                                autoplay: true
                            });
                            $(".touchslider").data("touchslider").start(); // start the slider
                            <? }else {?>
                            , autoplay: false
                        });
                        <? }?>
                    <? }?>
                try {
                    ga('create', '<?=$gaanalyst?>');  // Creates a tracker.
                    ga('send', {
                        'hitType': 'event',          	// Required.
                        'eventCategory': '<?=(is_numeric($_GET['tab_id']))?$_GET['tab_id']:'0'?>',  // Required Tab ID.
                        'eventAction': '<?=(is_numeric($_GET['item_id']))?$_GET['item_id']:'0'?>',      		// Required Item ID.
                        'eventLabel': '<?=(is_numeric($_GET['cat_id']))?$_GET['cat_id']:'0'?>',			// Category ID
                        'dimension1': <?=$app_id?>,
                    });
                } catch(err) {      
                }
            });
            })();
        </script>

        <?     
        if (
        $isnewdesign=='1' && 
        $qrydesignchk[with_moreview] !='1' && 
        $qrydesignchk["btn_layout"] !='0' &&
        $qrydesignchk["btn_layout"] !='2' &&
        isMainPage() // added to disable tab sliding code except for main page by Wang jia
        ) { ?>
            <script type="text/javascript">
                var myScroll;
                function loaded() {
                    myScroll = new iScroll('wrapper', {
                        snap: true,
                        momentum: false,
                        hScrollbar: false,
                        onScrollEnd: function () {
                            document.querySelector('#nav > li.active').className = '';
                            document.querySelector('#nav > li:nth-child(' + (this.currPageX+1) + ')').className = 'active';
                        }
                    });
                }
                document.addEventListener('DOMContentLoaded', loaded, false);
            </script>
            <? }?>
    </div> 

</body> 
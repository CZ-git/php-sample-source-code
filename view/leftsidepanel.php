<div data-role="panel" data-position="right" id="popshareloc<?=$id?>" data-theme="a" class="nav-class">
    <ul data-role="listview" class="nav-search" >
        <li data-role="list-divider"><?=$lngtellfriend ?></li>
        <?
        $domain = $_SERVER["SERVER_NAME"];
        $urlleft= str_split($domain, 4);
        if( $urlleft[0] == "http")
        {
            $domain;
        }
        else
        {
            $domain="http://".$domain;
        }
        if(substr_count($_SERVER['HTTP_HOST'],".")>1)
        {
            $_SESSION['domain'] = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&appcode=".$_SESSION[app_code];
        }
        else
        {
            $_SESSION['domain'] = "www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."&appcode=".$_SESSION[app_code];
        }
        ?>
        <li class="oddrowbackground" data-role="button"><a href="mailto:Who?subject=<?=$_SESSION['app_name'];?>&body=<?=$_SESSION['domain']?>"><?=$lngshare_by_email?></a></li>
        <li class="evenrowbackground" data-role="button"><a href="http://html5authentication.com/login-twitter.php?domain=<?=urlencode($_SESSION['domain'])?>&share=On" onclick="window.location = $(this).attr('href')" rel="external"><?=$lngshare_on_twitter?></a></li>
        <li class="oddrowbackground" data-role="button"><a href="http://html5authentication.com/login-facebook.php?domain=<?=urlencode($_SESSION['domain'])?>&share=On" onclick="window.location = $(this).attr('href')" rel="external"><?=$lngshare_on_facebook?></a></li> 
    </ul>
</div>
<div data-role="panel" data-theme="a" id="nav-panel" class="nav-class">
    <? 
    $respanel = tabstobeseen($_SESSION[app_id], $conn);
    //echo mysql_num_rows($respanel);
    //echo "test panel";
    $countrow = 1;
    ?>	    
    <ul data-role="listview" class="nav-search" >
        <li data-role="list-divider">Navigation</li>
        <li class='oddrowbackground'><a href="/newstructhtml5/?appcode=<?=$_SESSION[app_code]?>" rel="external"><img src="/tab_icons/<?=$_SESSION[styletab]?>/(168).png" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px" />Return to Home</a></li>
        <?
        while ($qrypanel = mysql_fetch_array($respanel)) {
            $countrow++;
            $newicon = $qrypanel["tab_icon_new"];
            $labelquotechk=rawurlencode($qrypanel["tab_label"]);
            if ($qrypanel["tab_icon_new"])
            {
                //  echo $qrypanel["tab_icon_new"];
                if($newicon)
                {
                    //	echo $newicon;
                    $newicon=($newicon);
                    if(substr($newicon,0,6)=='custom')
                    {
                        $iconused = "/tab_icons/".$newicon;
                    }
                    else
                    {
                        $iconused = "/tab_icons/$_SESSION[styletab]/".$newicon;
                    }
                    //	echo $newicon;
                }
                else
                {
                    $iconused = "/tab_icons/classic/".$qrypanel["tab_icon"];
                }
            }
            else
            {
                $iconused = "/tab_icons/classic/".$qrypanel["tab_icon"];
            }
            if ($qrypanel["view_controller"] == 'WebViewController')
            {	
                $sql2 = "SELECT * FROM `web_views` WHERE `tab_id` =".$qrypanel["id"];
                $res2 = mysql_query($sql2, $conn);
                if ( mysql_num_rows($res2) == 1 )
                {
                    $qry2 = mysql_fetch_array($res2);
                    $website2=$qry2["url"];
                    $urlleft2= str_split($website2, 4);
                    if( $urlleft2[0] != "http")
                    {
                        $website2 = "http://".$website2;
                    }
                }
            }
            if ( $qrypanel["view_controller"] == 'InfoItemsViewController' || $qrypanel["view_controller"] == 'InfoSectionViewController')
            {
                if ($qrypanel["view_controller"] == 'InfoSectionViewController')
                {
                    $sqlinfosec = "SELECT * 
                    FROM info_categories
                    WHERE tab_id = '$qrypanel[id]'
                    AND is_active =1
                    GROUP BY section
                    ORDER BY seq";
                }
                else
                {
                    $sqlcat = "select id from info_categories where tab_id = '$qrypanel[id]' and is_active=1 order by seq";
                    $rescat = mysql_query($sqlcat, $conn);
                    if (mysql_num_rows($rescat)) {
                        $catid = mysql_result($rescat, 0, 0);
                    }
                    $sqlinfosec = "select section
                    from info_items
                    where info_category_id = '$catid' and is_active=1
                    GROUP BY section
                    order by seq";
                }
                $resinfosec = mysql_query($sqlinfosec, $conn);
                ?>
                <div <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?> data-role="collapsible" data-theme="a" data-content-theme="false" data-inset="false" data-iconpos="right">

                    <h3><img src="<?=$iconused ?>" /><span class="cat"><?=$qrypanel["tab_label"]?></span></h3>
                    <? if ( mysql_num_rows($resinfosec) > 0 ) {
                        while ($qryinfosec = mysql_fetch_array($resinfosec)) { 
                            $qryinfosec[section] = mysql_real_escape_string($qryinfosec[section]);
                            ?>
                            <div data-role="collapsible" data-inset="false" data-theme="a" data-content-theme="false">

                                <h3 class="ui-bar-d"><?=$qryinfosec["section"]?></h3>
                                <?
                                if ($qrypanel["view_controller"] == 'InfoSectionViewController')
                                {?>
                                    <ul data-role="listview">
                                        <?
                                        $sqlinfoitem = "select id, name
                                        from info_categories
                                        where tab_id = '$qrypanel[id]' and is_active = 1 and section = '$qryinfosec[section]'
                                        order by seq";
                                        $resinfoitem = mysql_query($sqlinfoitem, $conn);
                                        $countrowtier=0;
                                        while ($qryinfoitem = mysql_fetch_array($resinfoitem)) {
                                            $countrowtier++;
                                            ?>
                                            <li <?=$countrowtier%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                                                <a href="?controller=InfoSectionViewController&cat_id=<?=$qryinfoitem["id"] ?>&tab_id=<?=$qrypanel[id]?>">
                                                    <h2><? echo $qryinfoitem["name"] ?></h2>
                                                </a>
                                            </li>
                                            <?
                                        }
                                        ?>
                                    </ul>
                                    <? } else {?>
                                    <ul data-role="listview">
                                        <?
                                        $sqlinfoitem = "select id, name
                                        from info_items
                                        where info_category_id = '$catid' and is_active = 1 and section = '$qryinfosec[section]'
                                        order by seq";
                                        $resinfoitem = mysql_query($sqlinfoitem, $conn);
                                        $countrowtier=0;
                                        while ($qryinfoitem = mysql_fetch_array($resinfoitem)) {
                                            $countrowtier++;
                                            ?>
                                            <li <?=$countrowtier%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                                                <a href="?controller=InfoSectionViewController&item_id=<? echo $qryinfoitem["id"] ?>&tab_id=<?=$qrypanel[id]?>">
                                                    <h2><? echo $qryinfoitem["name"] ?></h2>
                                                </a>
                                            </li>
                                            <?
                                        }
                                        ?>
                                    </ul>
                                    <? } ?>
                            </div><!-- /collapsible -->
                            <? 		}
                    }?>

                </div><!-- /collapsible -->
                <?	
                continue;
            }

            ?>
            <li <?= $countrow%2?"class='oddrowbackground'":"class='evenrowbackground'"; ?>>
                <?  if ($qrypanel["view_controller"] == 'OrderingViewController') 
                { ?>
                    <a href="/newstructhtml5/orderhtml5/?p=orderloc&app_code=<?=$_SESSION['app_code']?>&label=<?=$qrypanel[tab_label]?>&tab_id=<?=$qrypanel["id"]?>&controller=OrderingViewController"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a><? 
                } else if ($qrypanel["view_controller"] == 'MerchandiseViewController') 
                { ?>
                    <a  href="/newstructhtml5/orderhtml5/?p=orderloc&app_code=<?=$_SESSION['app_code']?>&label=<?=$qrypanel[tab_label]?>&tab_id=<?=$qrypanel["id"]?>&controller=MerchandiseViewController"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a>
                    <?  }
                else if ($qrypanel["view_controller"] == 'CustomFormViewController')
                {
                    $sqlform="SELECT tk FROM `form_layout` WHERE tab_id =".$qrypanel["id"]." AND is_active='1'";
                    $resform = mysql_query($sqlform, $conn);
                    $formtk = mysql_result($resform, 0, 0);
                    if(mysql_num_rows($resform)>1)
                    {
                        echo "<a href='/newstructhtml5/?controller=CustomFormViewController&tab_id=".$qrypanel[id]."&label=".$qrypanel[tab_label]."'> <img src='$iconused' alt='$qrypanel[tab_label]' class='ui-li-icon' style='top:0.5em !important; width:25px !important; height:25px'>$qrypanel[tab_label]</a>";
                    }
                    else
                    {
                        echo "<a href='/newstructhtml5/?controller=CustomFormViewController&tab_id=".$qrypanel[id]."&tk=$formtk&label=".$qrypanel["tab_label"]."'> <img src='$iconused' alt='$qrypanel[tab_label]' class='ui-li-icon' style='top:0.5em !important; width:25px !important; height:25px'>$qrypanel[tab_label]</a>";
                    }
                } 
                else if ($qrypanel["view_controller"] == 'EventsManagerViewController') 
                { ?>
                    <a href="<?="/newstructhtml5/?controller=EventsViewController&tab_id=".$qrypanel["id"]."&label=".$qrypanel["tab_label"] ?>"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a>
                    <?  }
                else if ($qrypanel["view_controller"] == 'FanWallManagerViewController') 
                { ?>
                    <a href="<?="/newstructhtml5/?controller=FanWallViewController&tab_id=".$qrypanel["id"]."&label=".$qrypanel["tab_label"] ?>"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a>
                    <?  }
                else
                    if ($website2) 
                    { ?><a <? echo webredirection($website2,$qry2[id]); unset($website2); ?>" rel="external"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a></li><? 
                    } 
                    else if ($qrypanel["view_controller"] == 'HomeViewController')
                    {?>
                        <a href="<?=$_SESSION[app_website] ?>" rel="external"><img src="<?=$iconused ?>" alt="<?=$qrypanel["tab_label"]?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a>
                        <?
                    }
                    else 
                    { ?>
                        <a href="<?="/newstructhtml5/?controller=".$qrypanel["view_controller"] ?>&tab_id=<? echo $qrypanel["id"] ?>&label=<? echo $labelquotechk ?>"><img src="<? echo $iconused ?>" alt="<? echo $qrypanel["tab_label"]  ?>" class="ui-li-icon" style="top:0.5em !important; width:25px !important; height:25px"><? echo $qrypanel["tab_label"]  ?></a>
                    <? }?></li> <?
        } 

        ?>            
    </ul>
</div><!-- /panel -->
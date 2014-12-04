<?php
        //echo $_SERVER["QUERY_STRING"];
        if ( $data['orderstr'] ) {
            $orderstr = $data['orderstr'];
        } else if($_SESSION['orderstr']) {
            $orderstr =  $_SESSION['orderstr'];
        }
        
        if ( $orderstr == '' ) {
            $orderstr=chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122)).chr(rand(97,122));
            $_SESSION['orderstr']=$orderstr;
            $data['orderstr'] = $orderstr;
        }
        
        $location_id = $data['loc_id'];
        insert_add_to_cartnew($conn, $app_id, $data, $location_id, $orderstr, $main_info);
        include_once("ordering_base_params.php");
        
?>
    <? include_once("header.php"); ?> 
    <div data-role="content" style="background:#FFF; opacity:0.8;">
        <div class="content-primary">
            <ul data-role="listview" data-divider-theme="d">
                <li data-role="list-divider" style="height:20px; /*display: -webkit-box;*/">
                    <div class="ui-btn-left" data-role="controlgroup" data-type="horizontal">
                        <a href="#popupMenu" class="ui-bar-d" data-role="button"><i class="fa fa-list"></i></a>
                    </div>
                    <span class="divtext" style="/*display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;
width: 189px;*/"><?=$lngorderadd?></span>
                    <div class="ui-btn-right" data-role="controlgroup" data-type="horizontal">
                        <a href="?p=cart&<?=$PASS_PARAMS;?>" class="ui-bar-d" data-role="button"><i class="fa fa-shopping-cart"></i></a>
                    </div>
                </li>
               </ul>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
        <?=$POST_PARAMS; ?>
        <br><br>
        <a data-role="button" href="?p=ordermenu&<?=$PASS_PARAMS;?>" data-theme="a"><?=$lngordermore?></a>
        <a data-role="button" href="?p=cart&<?=$PASS_PARAMS;?>" data-theme="b"><?=$lngcheckout?></a>
        </form>
        </div>
    </div>
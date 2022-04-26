<!DOCTYPE html>
<html lang="de">
    <head>
        <?php 
            ini_set('display_errors','1');
            $ver="2.0.0";
            $developer_mode=true;
            $cache=$ver;
            if($developer_mode)
                $cache=time();

            $dir=str_replace(" ","%20",dirname($_SERVER['SCRIPT_NAME']));
            if($dir==="/")
                $dir="";
            $loc=str_replace($dir,'',$_SERVER['REQUEST_URI']);
            $locex=array_filter(explode('/',$loc));
            foreach ($locex as $index => $locp) {
                if (strpos($locp, '.html') != false||strpos($locp, '.htm') != false||strpos($locp, '.php') != false) {
                    unset($locex[$index]);
                }
            }
            $locnew=implode('/',$locex);
            $pgi=json_decode(file_get_contents('data/pages.json'),true);
            if(isset($pgi['pages'][$locnew])){
                $spgi=$pgi['pages'][$locnew];
                if(isset($pgi['pages'][$locnew]['alias']))
                    $spgi=$pgi['pages'][$pgi['pages'][$locnew]['alias']];
                $page_name=$spgi['name'];
                $page_url="pages/".$spgi['page'];
            }
            else{
                $page_name="404 Not Found";
                $page_url="pages/404.php";
                http_response_code(404);
            }
            $pgs="";
            for($a=0;$a<count($pgi['navbar_setup']);++$a){
                if(isset($pgi['navbar_setup'][$a]['divider'])){
                    $pgs.='<li class="divider">'.$pgi['navbar_setup'][$a]['divider'].'</li>';
                }
                else if(isset($pgi['navbar_setup'][$a]['redir'])){
                    $pgs.='<a href="'.$dir.'/'.$pgi['navbar_setup'][$a]['redir'].'" target="_blank"><li><i class="ti ti-'.$pgi['navbar_setup'][$a]['icon'].'"></i><b>'.$pgi['navbar_setup'][$a]['name'].'</b><i class="ti ti-external-link"></i></li></a>';
                }
                else{
                    $ia=($page_name==$pgi['pages'][$pgi['navbar_setup'][$a]['page']]['name']) ? "active" : "";
                    $pgs.='<a href="'.$dir.'/'.$pgi['navbar_setup'][$a]['page'].'"><li class="'.$ia.'"><i class="ti ti-'.$pgi['navbar_setup'][$a]['icon'].'"></i><b>'.$pgi['pages'][$pgi['navbar_setup'][$a]['page']]['name'].'</b></li></a>';
                }
            }
        ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <title>Vertretungsplan Intern</title>
        <script src="<?php echo $dir ?>/../js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo $dir ?>/js/jquery.tablednd.js" type="text/javascript"></script>
        <script async src="<?php echo $dir ?>/../js/amp.js"></script>
        <script async custom-element="amp-bind" src="<?php echo $dir ?>/../js/amp-bind.js"></script>
        <script src="<?php echo $dir ?>/js/script.js?_=<?php echo $cache ?>" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $dir ?>/css/style.css?_=<?php echo $cache ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo $dir ?>/../css/tabler-icons.css">
    </head>
    <body onclick="$('#nav .lgninfo').removeClass('open')">
        <nav id="nav">
            <button class="menuToggle" onclick="openMenu()">
                <div class="ti ti-menu-2"></div>
            </button>
            <div class="spacer"></div>
            <button class="notifications">
                <div class="ti ti-bell"></div>
                <div class="indicator" style="display:none">9</div>
            </button>
            <div class="lgninfo" onclick="event.stopPropagation(); $('#nav .lgninfo').toggleClass('open')">
                <div class="name">Malte Slotkowski</div>
                <div class="ti ti-chevron-down"></div>
                <ul class="dd">
                    <a href="<?php echo $dir ?>/you">
                        <li>
                            <i class="ti ti-user-circle"></i>
                            <b>Dein Konto</b>
                        </li>
                    </a>
                    <li>
                        <i class="ti ti-logout"></i>
                        <b>Abmelden</b>
                    </li>
                </ul>
            </div>
        </nav>
        <div id="menu" onclick="closeMenu()">
            <div class="inner" onclick="event.stopPropagation()">
                <div class="scrollable">
                    <div class="head">
                        <amp-img class="si" src="<?php echo $dir ?>/../apis/school_image/index.php?s=400" alt="School Image" layout="responsive" [src]="si_head"></amp-img>
                        <div class="sn sn-ext">
                            Rheingau-Gymnasium Schöneberg
                        </div>
                        <div class="sn sn-col">
                            Rheingau-Gymnasium Schöneberg
                        </div>
                        <button class="arrow_back ti ti-arrow-narrow-left" onclick="closeMenu()"></button>
                    </div>
                    <ul class="menu-items">
                        <!--<li><i class="ti ti-home"></i><b>Start</b></li>
                        <li><i class="ti ti-layout-list"></i><b>Plan ansehen</b><i class="ti ti-external-link"></i></li>
                        <li><i class="ti ti-settings"></i><b>allgemeine Einstellungen</b></li>
                        <li><i class="ti ti-filter"></i><b>Filtereinstellungen</b></li>
                        <li><i class="ti ti-droplet-filled"></i><b>Farbeinstellungen</b></li>
                        <li><i class="ti ti-school"></i><b>Informationen zur Schule</b></li>
                        <li class="divider">Administratoreinstellungen:</li>
                        <li><i class="ti ti-users"></i><b>Benutzer für Intern</b></li>
                        <li><i class="ti ti-server-2"></i><b>System</b></li>-->
                        <?php echo $pgs ?>
                    </ul>
                </div>
            </div>
            </div>
        </div>
        <div id="content">
        <?php include($page_url) ?>
        </div>
        <div id="load" class="show">
            <div class="load_icon">
                <?php echo file_get_contents(__DIR__."/../img/icons/load.svg"); ?>
            </div>
        </div>
        <div id="toast"></div>
        <div id="multiwindow" onclick="closeMultiwindow()">
            <div class="inner" onclick="event.stopPropagation()">
                <div class="top">
                    <div class="arrow_back ti ti-arrow-narrow-left" onclick="closeMultiwindow()"></div>
                    <div class="title"></div>
                    <div class="close ti ti-x" onclick="closeMultiwindow()"></div>
                    <div class="load"></div>
                </div>
                <div class="content">

                </div>
            </div>
        </div>
    </body>
</html>
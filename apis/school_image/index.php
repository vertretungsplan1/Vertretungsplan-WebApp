<?php
    require_once __DIR__.'/../dataFetcher/settings.php';
    $furl="";
    $furl="../../img/school_image/1.jpg";
    if(filesize($furl)<=0)
        $furl="../../img/school_image/default.jpg";
    $img=imagecreatefromstring(file_get_contents($furl));
    if(isset($_GET['s'])&&is_numeric($_GET['s']))
        $img=imagescale($img,intval($_GET['s']));
    else
        $img=imagescale($img,100);
    if($_GET['f']==="png"){
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="Bild'.(getSetting("school_name")===""||getSetting("school_name")==null ? "" : "_".getSetting("school_name")).'.png"');
    }
    else if($_GET['f']==="webp"){
        header('Content-Type: image/webp');
        header('Content-Disposition: inline; filename="Bild'.(getSetting("school_name")===""||getSetting("school_name")==null ? "" : "_".getSetting("school_name")).'.webp"');
    }
    else{
        header('Content-Type: image/jpeg');
        header('Content-Disposition: inline; filename="Bild'.(getSetting("school_name")===""||getSetting("school_name")==null ? "" : "_".getSetting("school_name")).'.jpg"');
    }
    session_cache_limiter('none');
    header('Cache-control: max-age='.(60*60*24*365));
    header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
    header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($furl)));
    $erg;
    if($_GET['f']==="png"){
        $erg=imagepng($img);
    }
    else if($_GET['f']==="webp"){
        $erg=imagepng($img);
    }
    else
        $erg=imagejpeg($img,null,100);
    echo $erg;
?>
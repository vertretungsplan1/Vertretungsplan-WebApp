<?php
    //ini_set('display_errors','1');
    header('Content-Type:application/json');
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="GET")
        $vars=$_GET;
    else
        parse_str(file_get_contents("php://input"),$vars);
    $dir=str_replace(" ","%20",dirname($_SERVER['SCRIPT_NAME']));
    $dir=$dir==="/" ? "" : $dir;
    $loc=str_replace($dir,'',$_SERVER['REQUEST_URI']);
    $locex=explode('/',$loc);
    for($a=0;$a<count($locex);++$a){
        if (strpos($locex[$a], '.html') != false||strpos($locex[$a], '.htm') != false||strpos($locex[$a], '.php') != false||$locex[$a]==="") {
            array_splice($locex,$a,1);
            $a--;
        }
    }
    require_once __DIR__.'/scripts/erroner.php';
    $ln_name="vpladmin";
    if($locex[0]==="login")
        require_once __DIR__.'/scripts/login.php';
    else if(true  /* TODO: LOGIN CHECK */){
        if($locex[0]==="start")
            require_once __DIR__.'/scripts/start.php';
        else if($locex[0]==="settings")
            require_once __DIR__.'/scripts/settings.php';
        else if($locex[0]==="filter")
            require_once __DIR__.'/scripts/filter.php';
        else if($locex[0]==="colors")
            require_once __DIR__.'/scripts/colors.php';
        else if($locex[0]==="school")
            require_once __DIR__.'/scripts/school.php';
        else if($locex[0]==="admin"){
            if(true /* TODO: ADMIN CHECK */){
                if($locex[1]==="users")
                    require_once __DIR__.'/scripts/admin_users.php';
                else if($locex[1]==="system")
                    require_once __DIR__.'/scripts/admin_system.php';
                else
                    echo json_encode(erroner("VPL_UNKNOWN_RQM"));
            }
            else
                echo json_encode(erroner("401"));
        }
        else
            echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
    else
        echo json_encode(erroner("401"));
?>
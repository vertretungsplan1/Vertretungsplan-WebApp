<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    ob_start();
    include __DIR__.'/../../../apis/plan/index.php';
    $plan=json_decode(ob_get_clean(),true);
    $ret=[];
    $ret['success']=true;
    $ret['settings']['datasrv']=parse_url(getSetting('plan_url')==null ? "" : getSetting('plan_url'),PHP_URL_HOST);
    if(count($plan['columns']['all'])>0){
        $ret['settings']['columns']=$plan['columns']['all'][0].($plan['columns']['all']>1 ? " und ".(count($plan['columns']['all'])-1)." weitere" : "");
    }
    else
        $ret['settings']['columns']="";
    $asm=getSetting('asm')==null ? [] : getSetting('asm');
    $ukdt=[];
    foreach($plan['columns']['all'] as $column){
        $found=false;
        foreach(array_values($asm) as $asv){
            if((is_array($asv)&&in_array($column,array_values($asv)))||(is_string($asv)&&$asv===$column)){
                $found=true;
                break;
            }
        }
        if(!$found)
            array_push($ukdt,$column);
    }
    if(count($ukdt)>0){
        $ret['settings']['columns_without_asm']=$ukdt[0].($ukdt>1 ? " und ".(count($ukdt)-1)." weitere" : "");
    }
    else
        $ret['settings']['columns_without_asm']="";
    $classes=getSetting('classes')==null ? [] : getSetting('classes');
    if(count($classes)>0){
        $ret['filter']['classes']=$classes[0].($classes>1 ? " und ".(count($classes)-1)." weitere" : "");
    }
    else{
        $ret['filter']['classes']="keine";
    }
    $types=getSetting('types')==null ? [] : getSetting('types');
    if(count($types)>0){
        $ret['colors']['types']=$types[0]['name'].($types>1 ? " und ".(count($types)-1)." weitere" : "");
    }
    else{
        $ret['colors']['types']="keine";
    }
    $uktypes=[];
    if($plan['columns']['asm']['type']!=="false"){
        for($a=0;$a<count($plan['plan']);++$a){
            for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                    array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
            }
        }
    }
    $ret['colors']['unknown_types']=count($uktypes);
    $ret['school']['school_name']=getSetting('school_name')==null ? "" : getSetting('school_name');
    $ret['school']['location']="kein Standort festgelegt";
    $lat=getSetting('location_lat')==null ? "" : getSetting('location_lat');
    $lon=getSetting('location_lon')==null ? "" : getSetting('location_lon');
    if($lat!==""&&$lon!==""){
        $opts = array('http'=>array('header'=>"User-Agent: VertretungsplanMBS 3.0.0\r\n"));
        $context = stream_context_create($opts);
        $locinfo=json_decode(file_get_contents("https://nominatim.openstreetmap.org/reverse?lat=".$lat."&lon=".$lon."&format=json", false, $context),true);
        $ret['school']['location']=$locinfo['display_name'];
    }
    if(true /* TODO: ADMIN CHECK */){
        $users=getSetting('intern_users')==null ? [] : getSetting('intern_users');
        $ret['admin_users']['users']=count($users);
        $ret['admin_users']['admins']=0;
        foreach($users as $user){
            if($user['is_admin'])
                $ret['admin_users']['admins']++;
        }
        $ret['admin_system']['version']=getInfo('version');
        $update=false;
        $ret['admin_system']['update']=$update!=false ? "Ja (".$update.")" : "Nein";
        $mode=getSetting('plan_mode')==null ? "normal" : getSetting('plan_mode');
        if($mode==="normal") $ret['admin_system']['mode']="Normal"; else if($mode==="old_plan") $ret['admin_system']['mode']="alter Vertretungsplan"; else if($mode==="maintenance") $ret['admin_system']['mode']="Wartungsarbeiten"; else if($mode==="deactivated") $ret['admin_system']['mode']="Deaktiviert"; else $ret['admin_system']['mode']="unbekannter Modus";
        $vpl_root=dirname(dirname(dirname(__DIR__)));
        $arr=str_replace($vpl_root.'/',"",listFolderFiles($vpl_root));
        sort($arr);
        $wnb=getWarnables($arr);
        for($a=0;$a<count($arr);++$a){
            if(in_array($arr[$a],$wnb)){
                array_splice($arr,$a,1);
                --$a;
            }
        }
        $arr=array_merge($wnb,$arr);
        $ret['admin_system']['files_warn']=0;
        foreach($arr as $sub){
            if(!(is_readable($vpl_root.'/'.$sub)&&is_writable($vpl_root.'/'.$sub)))
                $ret['admin_system']['files_warn']++;
        }
    }
    echo json_encode($ret);
    function listFolderFiles($dir){
        $scan=scandir($dir);
        for($a=0;$a<count($scan);++$a){
            if($scan[$a]===""||$scan[$a]==="."||$scan[$a]===".."){
                array_splice($scan,$a,1);
                --$a;
            }
        }
        if (count($scan) < 1)
            return [];
        $scan = preg_filter('/^/', $dir.'/', $scan);
        foreach($scan as $sub){
            if(is_dir($sub))
                $scan=array_merge($scan,listFolderFiles($sub));
        }
        return $scan;
    }
    function getWarnables($arr){
        global $vpl_root;
        $warnables=[];
        foreach($arr as $sub){
            $readable=is_readable($vpl_root.'/'.$sub);
            $writable=is_writable($vpl_root.'/'.$sub);
            if(!($readable&&$writable))
                array_push($warnables,$sub);
        }
        return $warnables;
    }
?>
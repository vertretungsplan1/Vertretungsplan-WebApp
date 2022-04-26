<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($locex[2]==="mode"){
            if(isset($vars['mode'])){
                $ret['success']=saveSetting('plan_mode',$vars['mode']);
                echo json_encode($ret);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else
            echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
    else{
        $ret['success']=true;
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
        $files=[];
        foreach($arr as $sub){
            $readable=is_readable($vpl_root.'/'.$sub);
            $writable=is_writable($vpl_root.'/'.$sub);
            $chmod=substr(sprintf('%o', fileperms($vpl_root.'/'.$sub)),-4);
            $warn=!($readable&&$writable);
            $files[count($files)]['name']=$sub;
            $files[count($files)-1]['warn']=$warn;
            $files[count($files)-1]['readable']=$readable;
            $files[count($files)-1]['writable']=$writable;
            $files[count($files)-1]['chmod']=$chmod;
        }
        if($locex[2]==="update"){
            $ret['update']=false;
        }
        else if($locex[2]==="files"){
            $ret['files']=$files;
        }
        else{
            $ret['update']=false;
            $ret['mode']=getSetting('plan_mode')==null ? "normal" : getSetting('plan_mode');
            $ret['files']=$files;
        }
        echo json_encode($ret);
    }
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
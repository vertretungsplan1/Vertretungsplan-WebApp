<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        $users=getSetting('intern_users')==null ? [] : getSetting('intern_users');
        for($a=0;$a<count($userss);++$a){
            $userss[$a]=$users[$a]['name']===$ln_name;
        }
        if($locex[2]==="add"){
            if(isset($vars['name'])&&isset($vars['full_name'])&&isset($vars['is_admin'])){
                if(!in_array($vars['name'],array_column($users,'name'))){
                    $users[count($users)]['name']=$vars['name'];
                    $users[count($users)-1]['full_name']=$vars['full_name'];
                    $users[count($users)-1]['is_admin']=$vars['is_admin']==="1";
                    $users[count($users)-1]['pwh']="";
                    $ret['success']=saveSetting('intern_users',$users);
                    echo json_encode($ret);
                }
                else
                    echo json_encode(erroner("VPL_ALREADY_EXISTS"));
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[2]==="edit"){
            if(isset($vars['name'])&&isset($vars['full_name'])&&isset($vars['is_admin'])){
                if(in_array($vars['name'],array_column($users,'name'))){
                    $p=array_search($vars['name'],array_column($users,'name'));
                    if(!$userss[$p]){
                        $users[$p]['full_name']=$vars['full_name'];
                        $users[$p]['is_admin']=$vars['is_admin']==="1";
                        $ret['success']=saveSetting('intern_users',$users);
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_IS_YOURSELF"));
                }
                else
                    echo json_encode(erroner("VPL_NOT_EXISTING"));
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[2]==="pwreset"){
            if(isset($vars['name'])){
                if(in_array($vars['name'],array_column($users,'name'))){
                    $p=array_search($vars['name'],array_column($users,'name'));
                    if(!$userss[$p]){
                        $users[$p]['pwh']="";
                        $ret['success']=saveSetting('intern_users',$users);
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_IS_YOURSELF"));
                }
                else
                    echo json_encode(erroner("VPL_NOT_EXISTING"));
            }
            else if(isset($vars['names'])){
                for($a=0;$a<count($vars['names']);++$a){
                    if(in_array($vars['names'][$a],array_column($users,'name'))){
                        $p=array_search($vars['names'][$a],array_column($users,'name'));
                        if(!$userss[$p])
                            $users[$p]['pwh']="";
                    }
                }
                $ret['success']=saveSetting('intern_users',$users);
                echo json_encode($ret);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[2]==="remove"){
            if(isset($vars['name'])){
                if(in_array($vars['name'],array_column($users,'name'))){
                    $p=array_search($vars['name'],array_column($users,'name'));
                    if(!$userss[$p])
                        array_splice($users,$p,1);
                    else
                        echo json_encode(erroner("VPL_IS_YOURSELF"));
                }
                $ret['success']=saveSetting('intern_users',$users);
                echo json_encode($ret);
            }
            else if(isset($vars['names'])){
                for($a=0;$a<count($vars['names']);++$a){
                    if(in_array($vars['names'][$a],array_column($users,'name'))){
                        $p=array_search($vars['names'][$a],array_column($users,'name'));
                        if(!$userss[$p])
                            array_splice($users,$p,1);
                    }
                }
                $ret['success']=saveSetting('intern_users',$users);
                echo json_encode($ret);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
            
        }
        else
            echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
    else{
        $ret=[];
        $ret['success']=true;
        $ret['users']=getSetting('intern_users')==null ? [] : getSetting('intern_users');
        for($a=0;$a<count($ret['users']);++$a){
            unset($ret['users'][$a]['pwh']);
            $ret['users'][$a]['is_yourself']=$ret['users'][$a]['name']===$ln_name;
        }
        echo json_encode($ret);
    }
?>
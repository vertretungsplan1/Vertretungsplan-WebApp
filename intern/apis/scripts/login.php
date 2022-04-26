<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    $ald=getSetting("login_data");
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($vars['username']&&$vars['password']){
            if(count($ald)>0){
                if(in_array(strtolower($vars['username']),array_column($ald,'username'))){

                }
                else{
                    echo json_encode(erroner("VPL_UNKNOWN_LGN"));
                }
            }
            else{
                $nld=[];
                $nld['username']=strtolower($vars['username']);
                $nld['password']=$vars['password'];
                array_push($ald,$nld);
                saveSetting("login_data",$ald);
            }
        }
        else{
            echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
    }
    else{
        echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
?>
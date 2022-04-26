<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($locex[1]==="types"){
            ob_start();
            include __DIR__.'/../../../apis/plan/index.php';
            $plan=json_decode(ob_get_clean(),true);
            $types=getSetting('types')==null ? [] : getSetting('types');
            if($locex[2]==="add"){
                if(isset($vars['name'])&&isset($vars['color'])){
                    if(!in_array($vars['name'],array_column($types,'name'))){
                        $types[count($types)]['name']=$vars['name'];
                        $types[count($types)-1]['color']=intval($vars['color']);
                        $retu['success']=saveSetting('types',$types);
                        $uktypes=[];
                        if($plan['columns']['asm']['type']!=="false"){
                            for($a=0;$a<count($plan['plan']);++$a){
                                for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                                    if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                                        array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                                }
                            }
                        }
                        $retu['unknown_types']=$uktypes;
                        echo json_encode($retu);
                    }
                    else
                        echo json_encode(erroner("VPL_ALREADY_EXISTS"));
                    
                }
                else if(isset($vars['names'])&&isset($vars['color'])){
                    for($a=0;$a<count($vars['names']);++$a){
                        if(!in_array($vars['names'][$a],array_column($types,'name'))){
                            $types[count($types)]['name']=$vars['names'][$a];
                            $types[count($types)-1]['color']=intval($vars['color']);
                        }
                    }
                    $retu['success']=saveSetting('types',$types);
                    $uktypes=[];
                    if($plan['columns']['asm']['type']!=="false"){
                        for($a=0;$a<count($plan['plan']);++$a){
                            for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                                if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                                    array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                            }
                        }
                    }
                    $retu['unknown_types']=$uktypes;
                    echo json_encode($retu);
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));

            }
            else if($locex[2]==="edit"){
                if(isset($vars['old_name'])&&isset($vars['name'])&&isset($vars['color'])){
                    if(in_array($vars['old_name'],array_column($types,'name'))){
                        $p=array_search($vars['old_name'],array_column($types,'name'));
                        $types[$p]['name']=$vars['name'];
                        $types[$p]['color']=intval($vars['color']);
                        $retu['success']=saveSetting('types',$types);
                        $uktypes=[];
                        if($plan['columns']['asm']['type']!=="false"){
                            for($a=0;$a<count($plan['plan']);++$a){
                                for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                                    if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                                        array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                                }
                            }
                        }
                        $retu['unknown_types']=$uktypes;
                        echo json_encode($retu);
                    }
                    else
                        echo json_encode(erroner("VPL_NOT_EXISTING"));
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="remove"){
                if(isset($vars['name'])){
                    if(in_array($vars['name'],array_column($types,'name'))){
                        $p=array_search($vars['name'],array_column($types,'name'));
                        array_splice($types,$p,1);
                        
                    }
                    $retu['success']=saveSetting('types',$types);
                    $uktypes=[];
                    if($plan['columns']['asm']['type']!=="false"){
                        for($a=0;$a<count($plan['plan']);++$a){
                            for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                                if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                                    array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                            }
                        }
                    }
                    $retu['unknown_types']=$uktypes;
                    echo json_encode($retu);
                }
                else if(isset($vars['names'])){
                    for($a=0;$a<count($vars['names']);++$a){
                        if(in_array($vars['names'][$a],array_column($types,'name'))){
                            $p=array_search($vars['names'][$a],array_column($types,'name'));
                            array_splice($types,$p,1);
                        }
                    }
                    $retu['success']=saveSetting('types',$types);
                    $uktypes=[];
                    if($plan['columns']['asm']['type']!=="false"){
                        for($a=0;$a<count($plan['plan']);++$a){
                            for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                                if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                                    array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                            }
                        }
                    }
                    $retu['unknown_types']=$uktypes;
                    echo json_encode($retu);
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else
                echo json_encode(erroner("VPL_UNKNOWN_RQM"));
        }
        else
            echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
    else{
        ob_start();
        include __DIR__.'/../../../apis/plan/index.php';
        $plan=json_decode(ob_get_clean(),true);
        $types=getSetting('types')==null ? [] : getSetting('types');
        $uktypes=[];
        if($plan['columns']['asm']['type']!=="false"){
            for($a=0;$a<count($plan['plan']);++$a){
                for($b=0;$b<count($plan['plan'][$a]['vertretungen']);++$b){
                    if(isset($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']])&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],array_column($types,'name'))&&!in_array($plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']],$uktypes))
                        array_push($uktypes,$plan['plan'][$a]['vertretungen'][$b][$plan['columns']['asm']['type']]);
                }
            }
        }
        $retu['success']=true;
        $retu['types']=$types;
        $retu['unknown_types']=$uktypes;
        $retu['colors']=getInfo('default_colors');
        echo json_encode($retu);
    }
?>
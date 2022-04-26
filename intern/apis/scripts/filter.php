<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($locex[1]==="classes"){
            $classes=getSetting('classes')==null ? [] : getSetting('classes');
            if($locex[2]==="add"){
                if(isset($vars['name'])){
                    if(!in_array($vars['name'],$classes)){
                        array_push($classes,$vars['name']);
                        $ret['success']=saveSetting('classes',$classes);
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_ALREADY_EXISTS"));
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="edit"){
                if(isset($vars['old_name'])&&isset($vars['name'])){
                    if(in_array($vars['old_name'],$classes)){
                        $p=array_search($vars['old_name'],$classes);
                        $classes[$p]=$vars['name'];
                        $ret['success']=saveSetting('classes',$classes);
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_NOT_EXISTING"));
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="remove"){
                if(isset($vars['name'])){
                    if(in_array($vars['name'],$classes)){
                        $p=array_search($vars['name'],$classes);
                        array_splice($classes,$p,1);
                        $ret['success']=saveSetting('classes',$classes);
                        echo json_encode($ret);
                    }
                }
                else if(isset($vars['names'])){
                    for($a=0;$a<count($vars['names']);++$a){
                        if(in_array($vars['names'][$a],$classes)){
                            $p=array_search($vars['names'][$a],$classes);
                            array_splice($classes,$p,1);
                        }
                    }
                    $ret['success']=saveSetting('classes',$classes);
                    echo json_encode($ret);
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="order"){
                if(isset($vars['order'])){
                    $new_classes=[];
                    foreach($vars['order'] as $class){
                        if(in_array($class,$classes))
                            array_push($new_classes,$classes[array_search($class,$classes)]);
                    }
                    $ret['success']=saveSetting('classes',$new_classes);
                    echo json_encode($ret);
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
        $classes=getSetting('classes')==null ? [] : getSetting('classes');
        $ret['success']=true;
        $ret['classes']=$classes;
        echo json_encode($ret);
    }
?>
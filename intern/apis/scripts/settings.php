<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($locex[1]==="plan_url"){
            if(isset($vars['url'])){
                $retu=[];
                $retu['success']=saveSettingInstant('plan_url',$vars['url']);
                $retu['plan_url']=$vars['url'];
                $visib=getSetting('row_visibility');
                ob_start();
                include __DIR__.'/../../../apis/plan/index.php';
                $plan=json_decode(ob_get_clean(),true);
                $headers=$plan['columns']['all'];
                $retu['columns']=[];
                for($a=0;$a<count($headers);++$a){
                    $retu['columns'][$a]=[];
                    $retu['columns'][$a]['name']=$headers[$a];
                    $retu['columns'][$a]['visible']=!isset($visib[$headers[$a]])||$visib[$headers[$a]];
                }
                echo json_encode($retu);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[1]==="columns"){
            if($locex[2]==="visibility"){
                if(isset($vars['column_name'])&&isset($vars['visibility'])){
                    $ret=[];
                    $visib=getSetting('row_visibility');
                    $visib[$vars['column_name']]=$vars['visibility']==="1";
                    $ret['success']=saveSetting('row_visibility',$visib);
                    echo json_encode($ret);
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="asm"){
                if(isset($vars['column_name'])&&isset($vars['asm_name'])){
                    $asm=getSetting('asm')==null ? [] : getSetting('asm');
                    $asms=getInfo('asms');
                    $asmkeys=array_keys($asm);
                    for($a=0;$a<count($asmkeys);++$a){
                        if(is_array($asm[$asmkeys[$a]])&&in_array($vars['column_name'],$asm[$asmkeys[$a]])){
                            $p=array_search($vars['column_name'],$asm[$asmkeys[$a]]);
                            array_splice($asm[$asmkeys[$a]],$p,1);
                        }
                        else if($asm[$asmkeys[$a]]===$vars['column_name'])
                            $asm[$asmkeys[$a]]="false";
                    }
                    if(in_array($vars['asm_name'],array_column($asms,'kname'))){
                        $asmsp=array_search($vars['asm_name'],array_column($asms,'kname'));
                        if($asms[$asmsp]['multipurpose']){
                            if(!isset($asm[$vars['asm_name']]))
                                $asm[$vars['asm_name']]=[];
                            array_push($asm[$vars['asm_name']],$vars['column_name']);
                            $asm[$vars['asm_name']]=array_unique($asm[$vars['asm_name']]);
                        }
                        else
                            $asm[$vars['asm_name']]=$vars['column_name'];
                        $ret['success']=saveSetting('asm',$asm);
                        $ret['asm']=$asm;
                        $ret['asms']=$asms;
                        echo json_encode($ret);

                    }
                    else if($vars['asm_name']==="false"){
                        $ret=[];
                        $ret['success']=saveSetting('asm',$asm);
                        $ret['asm']=$asm;
                        $ret['asms']=$asms;
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_WRONG_PARAMS"));
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
        $retu=[];
        $retu['success']=true;
        $retu['plan_url']=getSetting('plan_url')==null ? "" : getSetting('plan_url');
        $visib=getSetting('row_visibility');
        ob_start();
        include __DIR__.'/../../../apis/plan/index.php';
        $plan=json_decode(ob_get_clean(),true);
        $headers=$plan['columns']['all'];
        $retu['columns']=[];
        for($a=0;$a<count($headers);++$a){
            $retu['columns'][$a]=[];
            $retu['columns'][$a]['name']=$headers[$a];
            $retu['columns'][$a]['visible']=!isset($visib[$headers[$a]])||$visib[$headers[$a]];
        }
        $retu['asm']=getSetting('asm')==null ? [] : getSetting('asm');
        $retu['asms']=getInfo('asms');
        echo json_encode($retu);
    }
?>
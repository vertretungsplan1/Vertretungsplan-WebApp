<?php 
    if(!isset($locex)) exit;
    require_once __DIR__.'/../../../apis/dataFetcher/settings.php';
    require_once __DIR__.'/../../../apis/dataFetcher/info.php';
    if(strtoupper($_SERVER['REQUEST_METHOD'])==="POST"){
        if($locex[1]==="name"){
            if(isset($vars['name'])){
                $ret['success']=saveSetting('school_name',$vars['name']);
                $ret['school_name']=$vars['name'];
                echo json_encode($ret);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[1]==="location"){
            if(isset($vars['lat'])&&isset($vars['lon'])){
                $ret['success']=saveSetting('location_lat',floatval($vars['lat']))&&saveSetting('location_lon',floatval($vars['lon']));
                echo json_encode($ret);
            }
            else
                echo json_encode(erroner("VPL_MISSING_PARAMS"));
        }
        else if($locex[1]==="image"){
            if($locex[2]==="upload"){
                if(isset($_FILES['image'])){
                    $img=imagecreatefromstring(file_get_contents($_FILES['image']['tmp_name']));
                    $img=imagescale($img,1280);
                    $upl=imagejpeg($img,__DIR__.'/../../../img/school_image/1.jpg');
                    if($upl){
                        $ret['success']=true;
                        echo json_encode($ret);
                    }
                    else
                        echo json_encode(erroner("VPL_UPLOAD_FAILED"));
                }
                else
                    echo json_encode(erroner("VPL_MISSING_PARAMS"));
            }
            else if($locex[2]==="remove"){
                $upl=file_put_contents(__DIR__.'/../../../img/school_image/1.jpg',"");
                if($upl==0){
                    $ret['success']=true;
                    echo json_encode($ret);
                }
                else
                    echo json_encode(erroner("VPL_REMOVING_FAILED"));
            }
            else
                echo json_encode(erroner("VPL_UNKNOWN_RQM"));
        }
        else
            echo json_encode(erroner("VPL_UNKNOWN_RQM"));
    }
    else{
        $ret['success']=true;
        $ret['school_name']=getSetting('school_name')==null ? "" : getSetting('school_name');
        $ret['location']=[];
        $ret['location']['name']="kein Standort festgelegt";
        $lat=getSetting('location_lat')==null ? "" : getSetting('location_lat');
        $lon=getSetting('location_lon')==null ? "" : getSetting('location_lon');
        if($lat!==""&&$lon!==""){
            $opts = array('http'=>array('header'=>"User-Agent: VertretungsplanMBS 3.0.0\r\n"));
            $context = stream_context_create($opts);
            $locinfo=json_decode(file_get_contents("https://nominatim.openstreetmap.org/reverse?lat=".$lat."&lon=".$lon."&format=json", false, $context),true);
            $ret['location']['name']=$locinfo['display_name'];
        }
        echo json_encode($ret);
    }
?>
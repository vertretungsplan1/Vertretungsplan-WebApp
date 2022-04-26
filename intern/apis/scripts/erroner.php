<?php 
    if(!isset($locex)) exit;
    function erroner($code){
        $ret=[];
        $ret['success']=false;
        $ret['code']=$code;
        if($code==="401"){
            $ret['message']="Du hast nicht die Berechtigung diesen Befehl auszuführen.";
            http_response_code(401);
        }
        else if($code==="VPL_UNKNOWN_RQM"||$code==="VPL_MISSING_PARAMS"||$code==="VPL_WRONG_PARAMS")
            $ret['message']="Die Anfrage scheint unkorrekt oder unvollständig.";
        else if($code==="VPL_ALREADY_EXISTS")
            $ret['message']="Dieses Element/Name existiert bereits.";
        else if($code==="VPL_NOT_EXISTING")
            $ret['message']="Dieses Element/Name scheint nicht (mehr) zu existieren.";
        else if($code==="VPL_UPLOAD_FAILED")
            $ret['message']="Während des Uploads ist ein Fehler aufgetreten. Bitte überprüfen Sie 'System' als Administrator.";
        else if($code==="VPL_REMOVING_FAILED")
            $ret['message']="Während des Entfernens ist ein Fehler aufgetreten. Bitte überprüfen Sie 'System' als Administrator.";
        else if($code==="VPL_IS_YOURSELF")
            $ret['message']="Du kannst keine Aktionen an dir vornehmen.";
        else if($code==="VPL_UNKNOWN_LGN")
            $ret['message']="Ungültige Anmeldedaten.";
        else
            $ret['message']="Unbekannter Fehler.";
        return $ret;
    }
?>
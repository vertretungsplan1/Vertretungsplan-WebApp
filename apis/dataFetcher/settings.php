<?php 
    //$config=json_decode(hex2bin(file_get_contents(__DIR__."/../../data/config")),true);
    $config=json_decode(hex2bin(str_replace(" ?>","",str_replace("<?php ","",file_get_contents(__DIR__."/../../data/config.php")))),true);
    function getSetting($name){
        global $config;
        return $config[$name];
    }
    function saveSetting($name,$value){
        global $config;
        $config[$name]=$value;
        return $config[$name]==$value;
    }
    function saveSettingInstant($name,$value){
        global $config;
        $config[$name]=$value;
        file_put_contents(__DIR__."/../../data/config",bin2hex(json_encode($config)));
        return $config[$name]==$value;
    }
    function removeSetting($name){
        global $config;
        unset($config[$name]);
        return !isset($config[$name]);
    }
    function hasSetting($name){
        global $config;
        return isset($config[$name]);
    }
    function shutdown(){
        global $config;
        file_put_contents(__DIR__."/../../data/config.php",'<?php '.bin2hex(json_encode($config)).' ?>');
    }
    register_shutdown_function('shutdown');
    
?>
<?php 
    $info=json_decode(file_get_contents(__DIR__."/../../data/info.json"),true);
    function getInfo($name){
        global $info;
        return $info[$name];
    }
?>
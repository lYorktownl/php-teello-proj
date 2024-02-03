<?php

spl_autoload_register(function ($class_name){
    if ($class_name[0]=="T") {
        include "inc/".$class_name. '.php';
    }
    elseif ($class_name[0]=="M") {
        include "modules/".$class_name. '.php';
    }
    elseif ($class_name[0]=="C") {
        include "inc/inc-".$class_name. '.php';
    }
});
<?php

session_start();
		
			ini_set('error_reporting', E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);


include('inc/inc-Core.php');

include('inc/inc-autoload.php');




$core = new Core;
$core->execute();


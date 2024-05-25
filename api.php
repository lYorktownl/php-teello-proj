<?php

session_start();
		
			ini_set('error_reporting', E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
// print('<!--');
print_r($_GET);
print_r($_POST);
// print('-->');

include('./inc-CoreApi.php');
include('inc/inc-autoload.php');

$core = new CoreApi;
$core->execute();
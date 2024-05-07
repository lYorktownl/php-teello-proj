<?php

session_start();
		
			ini_set('error_reporting', E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);


// $data = file_get_contents('http://localhost/api.php?getUserList&userId=29');
// print($data);
// print('<hr>');

// $array = array (
// 	'login' => 'admin',
// 	'password' => '1234'
// );

// $ch = curl_init('http://localhost/api.php');
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, "", "&"));
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_HEADER, false);
// $html = curl_exec($ch);

// print($html);

$array = array (
	'request' => 'getUserData',
	'data' => 30
);

$ch = curl_init('http://localhost/api.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, "", "&"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);

print($html);
<?php

session_start();
		
			ini_set('error_reporting', E_ALL & ~E_NOTICE);
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);


$sesid = '8de1d883de4a1f8d41239dba57ad347a'; 

// $array = array (
// 	'request' => 'makeAuth',
// 	'login' => 'admin',
// 	'password'=> 'admin',
// );
$array = array(
    'request' => 'setTaskData',
    'taskid' => 47,
    'title' => 'New Task Title',
    'description' => 'New Task Description',
    'session_id' => $sesid
);
	
// );

$ch = curl_init('http://localhost/api.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, "", "&"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);

print($html);






























// session_start();
		
// ini_set('error_reporting', E_ALL & ~E_NOTICE);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

// $sesid = '8de1d883de4a1f8d41239dba57ad347a';

// // Функция для отправки запросов
// function sendRequest($data) {
//     $ch = curl_init('http://localhost/api.php');
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, "", "&"));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_HEADER, false);
//     $response = curl_exec($ch);
//     curl_close($ch);
//     return $response;
// }

// // Проверка авторизации
// $authData = array(
//     'request' => 'checkAuth',
//     'session_id' => $sesid
// );

// $authResponse = sendRequest($authData);
// $authResponseArray = json_decode($authResponse, true);

// if (isset($authResponseArray['error'])) {
//     // Ошибка авторизации
//     print($authResponse);
// } else {
//     // Авторизация успешна, выполняем основной запрос
//     $array = array(
//         'request' => 'getTaskData',
//         'data' => 47,
//         'session_id' => $sesid
//     );

//     $response = sendRequest($array);
//     print($response);
// }
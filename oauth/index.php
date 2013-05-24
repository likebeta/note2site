<?php
require_once('../config.php');
if (isset($_GET['code']))
{
	$http_array = array();
	$http_array[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
	$http_array[] = 'Authorization: '.base64_encode(utf8_encode($app_secret));
	$connect = curl_init();
	curl_setopt($connect,CURLOPT_URL,'http://'.$grant_host.'/authorize?grant_type=authorization_code&code='.$_GET['code']);
	curl_setopt($connect,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($connect,CURLOPT_HTTPHEADER,$http_array);
	$content = curl_exec($connect);
	//curl_close($connect);
	echo $content;
	$content = json_decode($content);
	var_dump($content);
	$http_array = array();
	$http_array[] = 'Authorization: '.base64_encode(utf8_encode($content->access_token));
	$http_array[] = 'Content-Type£ºapplication/json';
	//curl_setopt($connect,CURLOPT_URL,'http://'.$grant_host.'/v1.2/user/current');
	curl_setopt($connect,CURLOPT_URL,'http://'.$grant_host.'/v1.2/note-categories');
	curl_setopt($connect,CURLOPT_HTTPHEADER,$http_array);
	$content = curl_exec($connect);
	var_dump($content);	
}
else
{
	echo 'failed';
}
?>
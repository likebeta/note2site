<?php
$site_url = 'http://note2site.ixxoo.me';

// note
$base_url = 'http://note.youdao.com';
$oauth_consumer_key = '780b8bb560897a357c78de3e85b88cd9';
$oauth_consumer_secret = '82c911c61bb3067058febaea94007a10';
$oauth_access_token = 'ec4087f1c03661637bc148f1f4a665bb';
$oauth_access_secret = '598301bc3f0e4187bcb0b26482a06eb1';

$flush_token = 'r4c0oifbhvat3dszm9nuyl6qpj5e2x7wkg81';// token of flush 

// db
$db_host = $_ENV['OPENSHIFT_MYSQL_DB_HOST'].':'.$_ENV['OPENSHIFT_MYSQL_DB_PORT'];
$db_name = $_ENV['OPENSHIFT_APP_NAME'];
$db_username = $_ENV['OPENSHIFT_MYSQL_DB_USERNAME'];
$db_password = $_ENV['OPENSHIFT_MYSQL_DB_PASSWORD'];
?>

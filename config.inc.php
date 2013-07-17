<?php
// note api
$oauth_base_url = 'http://note.youdao.com';
$oauth_consumer_key = '780b8bb560897a357c78de3e85b88cd9';
$oauth_consumer_secret = '82c911c61bb3067058febaea94007a10';
$oauth_access_token = 'ec4087f1c03661637bc148f1f4a665bb';
$oauth_access_secret = '598301bc3f0e4187bcb0b26482a06eb1';

// db info
define('DB_MYSQL_HOST', "localhost");
define('DB_MYSQL_USERNAME', "root");
define('DB_MYSQL_PASSWORD', "359359");
define('DB_MYSQL_DBNAME', "note2site");
define('DB_MYSQL_PORT', "3306");

// define('DB_MYSQL_HOST', $_ENV['OPENSHIFT_MYSQL_DB_HOST']);
// define('DB_MYSQL_USERNAME', $_ENV['OPENSHIFT_MYSQL_DB_USERNAME']);
// define('DB_MYSQL_PASSWORD', $_ENV['OPENSHIFT_MYSQL_DB_PASSWORD']);
// define('DB_MYSQL_DBNAME', $_ENV['OPENSHIFT_APP_NAME']);
// define('DB_MYSQL_PORT', $_ENV['OPENSHIFT_MYSQL_DB_PORT']);

// misc
define('SITE_URL', 'http://note2site.ixxoo.me');
define('FLUSH_TOKEN', 'r4c0oifbhvat3dszm9nuyl6qpj5e2x7wkg81'); // token of flush
define('PAGE_SUFFIX', '');	// url suffix
?>

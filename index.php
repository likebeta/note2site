<!doctype html>
<html>
<head>
<title>note2site</title>
<meta charset="utf8" />
</head>
<body>
<?php
require('config.php');
/*
require('oauth/ynote_client.php');
require('oauth/ynote_parse.php');

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->listNotebooks($oauth_access_token,$oauth_access_secret);
if ($notebooks = parseNotebooks($response)){
	echo '<ul>';
	foreach($notebooks as $notebook){
		echo '<li><a href="'.$site_url.'/topic'.$notebook->path.'.html" title="有'.$notebook->notes_num.'篇笔记" target="_blank">'.$notebook->name.'</a></li>';
	}
	echo '</ul>';
}
else{
	echo '服务器错误，请重试';
}
*/
$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
/* check connection */
if ($mysqli->connect_errno) {
	$msg = "Connect failed: ".$mysqli->connect_error;
	php_die($msg);
}

$strsql = "SELECT path,name,notes_num FROM notebook";
if ($results = $mysqli->query($strsql)) {
	echo '<ul>';
	while ($row = $results->fetch_assoc()) {
        echo '<li><a href="'.$site_url.'/topic'.$row['path'].'.html" title="有'.$row['notes_num'].'篇笔记" target="_blank">'.$row['name'].'</a></li>';
    }
    echo '</ul>';
}
?>
</body>
</html>

<?php
	function php_die($msg){
		printf("%s</body>\n</html>",$msg);
		exit();
	}
?>

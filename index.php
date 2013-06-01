<!doctype html>
<html>
<head>
<title>note2site</title>
<meta charset="utf8" />
</head>
<body>
<?php
require_once('config.php');
require('oauth/ynote_client.php');
require('oauth/ynote_parse.php');

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->listNotebooks($oauth_access_token,$oauth_access_secret);
if ($notebooks = parseNotebooks($response)){
	echo '<ul>';
	foreach($notebooks as $notebook){
		echo '<li><a href="'.$site_url.'/topic/index.php?path='.$notebook->path.'" title="有'.$notebook->notes_num.'篇笔记" target="_blank">'.$notebook->name.'</a></li>';
	}
	echo '</ul>';
}
else{
	echo '服务器错误，请重试';
}
?>
</body>
</html>

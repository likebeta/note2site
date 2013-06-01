<?php
$path = isset($_GET['path']) ? $_GET['path']:'';
if ($path === '')
{
	include('404.php');
	exit;
}
?>
<!doctype html>
<html>
<head>
<title>topic | <?php echo $path;?></title>
<meta charset="utf8" />
</head>
<body>
<?php
require_once('../config.php');
require('../oauth/ynote_client.php');
require('../oauth/ynote_parse.php');

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->listNotes($oauth_access_token,$oauth_access_secret,$path);

if ($notes = parseNotes($response)){
	echo '<ul>';
	foreach($notes as $notepath){
		$response = $client->getNote($oauth_access_token,$oauth_access_secret,$notepath);
		if ($note = parseNote($response)){
			echo '<li><a href="'.$site_url.'/archive/index.php?path='.$note->path.'" title="'.$note->title.'" target="_blank">'.$note->title.'</a></li>';
		}
	}
	echo '</ul>';
}
else{
	echo '网页不存在或者服务器错误';
}
?>
</body>
</html>

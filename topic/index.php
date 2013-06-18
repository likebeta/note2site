<?php
$path = isset($_GET['path']) ? $_GET['path']:'';
if ($path === '')
{
	include('404.php');
	exit;
}

// 获取notebook中的笔记
require_once('../config.php');
require('../oauth/ynote_client.php');
require('../oauth/ynote_parse.php');

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->listNotes($oauth_access_token,$oauth_access_secret,$path);

$str_notes = '';
if ($notes = parseNotes($response)){
	$str_notes .= '<ul>';
	foreach($notes as $notepath){
		$response = $client->getNote($oauth_access_token,$oauth_access_secret,$notepath);
		if ($note = parseNote($response)){
			$str_notes .= '<li><a href="'.$site_url.'/archive'.$note->path.'.html" title="'.$note->title.'" target="_blank">'.$note->title.'</a></li>';
		}
	}
	$str_notes .= '</ul>';
}
else{
	die('网页不存在或者服务器错误');
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
	echo $str_notes;
?>
</body>
</html>

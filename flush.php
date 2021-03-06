<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>flush notes</title>
</head>
<body>
<?php
$dir = dirname(__FILE__);
require_once($dir.'/config.inc.php');
require_once($dir.'/oauth/ynote_client.php');
require_once($dir.'/oauth/ynote_parse.php');

try
{
	if (!isset($_GET['token']) || $_GET['token'] !== FLUSH_TOKEN)
	{
		throw new Exception('your sister, token error！');
	}

	$mysqli = new mysqli(DB_MYSQL_HOST, DB_MYSQL_USERNAME, DB_MYSQL_PASSWORD, DB_MYSQL_DBNAME,DB_MYSQL_PORT);
	if ($mysqli->connect_errno)
	{
		throw new Exception('Connect failed: '.$mysqli->connect_error);
	}
	$strsql = "DROP TABLE IF EXISTS `notebook`";
	if ($mysqli->query($strsql) === FALSE)
	{
	    throw new Exception('drop table notebook failed: '.$mysqli->error);
	}

	$strsql = "DROP TABLE IF EXISTS `note`";
	if ($mysqli->query($strsql) === FALSE)
	{
	    throw new Exception('drop table note failed: '.$mysqli->error);
	}
	$strsql = <<<EOF
	CREATE TABLE `notebook` (
	  `path` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `notes_num` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `notebook_group` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `create_time` datetime NOT NULL,
	  `modify_time` datetime NOT NULL,
	  PRIMARY KEY (`path`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOF;
	if ($mysqli->query($strsql) === FALSE)
	{
    	throw new Exception('create table notebook failed: '.$mysqli->error);
	}
	$strsql = <<<EOF
	CREATE TABLE `note` (
	  `path` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `title` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  `create_time` datetime NOT NULL,
	  `modify_time` datetime NOT NULL,
	  `notebook_path` varchar(255) COLLATE utf8mb4_bin NOT NULL,
	  PRIMARY KEY (`path`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
EOF;
	if ($mysqli->query($strsql) === FALSE)
	{
    	throw new Exception('create table note failed: '.$mysqli->error);
	}
	$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
	$response = $client->listNotebooks($oauth_access_token,$oauth_access_secret);
	$notebooks = parseNotebooks($response);
	if (!$notebooks)
	{
    	throw new Exception('listNotebooks error，retry please');	
	}

	echo "flush begin...<br />";
	$strnotebooksql = "INSERT INTO notebook(path,name,notes_num,notebook_group,create_time,modify_time) VALUES('%s','%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE name='%s',notes_num='%s',notebook_group='%s',create_time='%s',modify_time='%s'";
	$strnotesql = "INSERT INTO note(path,title,create_time,modify_time,notebook_path) VALUES('%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE title='%s',create_time='%s',modify_time='%s',notebook_path='%s'";
	foreach($notebooks as $notebook)
	{
		$path = $mysqli->real_escape_string($notebook->path);
		$name = $mysqli->real_escape_string($notebook->name);
		$notes_num = $mysqli->real_escape_string($notebook->notes_num);
		$notebook_group = $mysqli->real_escape_string($notebook->group);
		$create_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $notebook->create_time));
		$modify_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $notebook->modify_time));
		$strsql = sprintf($strnotebooksql,$path,$name,$notes_num,$notebook_group,$create_time,$modify_time,$name,$notes_num,$notebook_group,$create_time,$modify_time);
		if ($mysqli->query($strsql) === FALSE)
		{
			printf("flush notebook %s failed: %s<br />", $notebook->name, $mysqli->error);
			continue;
		}
		$response = $client->listNotes($oauth_access_token,$oauth_access_secret,$path);
		$notes = parseNotes($response);
		if (!$notes)
		{
			printf("listNotes %s failed<br />%s<br />", $notebook->name,  $response);
			continue;		
		}
		
		foreach($notes as $notepath)
		{
			$response = $client->getNote($oauth_access_token,$oauth_access_secret,$notepath);
			if ($note = parseNote($response))
			{
				$path = $mysqli->real_escape_string($note->path);
				$title = $mysqli->real_escape_string($note->title);
				$create_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $note->create_time));
				$modify_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $note->modify_time));
				$notebook_path = $mysqli->real_escape_string($notebook->path);
				$strsql = sprintf($strnotesql,$path,$title,$create_time,$modify_time,$notebook_path,$title,$create_time,$modify_time,$notebook_path);
				if ($mysqli->query($strsql) === FALSE)
				{
					printf("flush note %s failed: %s<br />", $note->title, $mysqli->error);
					continue;
				}
			}
		}
	}
	echo "flush end...<br />";
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>
</body>
</html>

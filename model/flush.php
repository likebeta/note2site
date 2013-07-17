<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>flush notes</title>
</head>
<body>
<?php
require('config.php');
require('oauth/ynote_client.php');
require('oauth/ynote_parse.php');

if (!isset($_GET['token']) || $_GET['token'] !== $flush_token) {
	php_die('your sister, token error！');
}

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($mysqli->connect_errno) {
	$msg = "Connect failed: ".$mysqli->connect_error;
	php_die($msg);
}

$strsql = "DROP TABLE IF EXISTS `notebook`";
if ($mysqli->query($strsql) === FALSE) {
    $msg = "drop table notebook failed: ".$mysqli->error;
   	php_die($msg);
}

$strsql = "DROP TABLE IF EXISTS `note`";
if ($mysqli->query($strsql) === FALSE) {
    $msg = "drop table note failed: ".$mysqli->error;
   	php_die($msg);
}

$strsql = <<<EOF
CREATE TABLE `notebook` (
  `path` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `notes_num` varchar(255) NOT NULL,
  `notebook_group` varchar(255) NOT NULL,
  `create_time` datetime NOT NULL,
  `modify_time` datetime NOT NULL,
  PRIMARY KEY (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

if ($mysqli->query($strsql) === FALSE) {
    $msg = "create table notebook failed: ".$mysqli->error;
   	php_die($msg);
}

$strsql = <<<EOF
CREATE TABLE `note` (
  `path` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `create_time` datetime NOT NULL,
  `modify_time` datetime NOT NULL,
  `notebook_path` varchar(255) NOT NULL,
  PRIMARY KEY (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

if ($mysqli->query($strsql) === FALSE) {
    $msg = "create table note failed: ".$mysqli->error;
   	php_die($msg);
}

$client = new YnoteClient($oauth_consumer_key, $oauth_consumer_secret);
$response = $client->listNotebooks($oauth_access_token,$oauth_access_secret);
if ($notebooks = parseNotebooks($response)){
	echo "flush begin...<br />";
	$strnotebooksql = "INSERT INTO notebook(path,name,notes_num,notebook_group,create_time,modify_time) VALUES('%s','%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE name='%s',notes_num='%s',notebook_group='%s',create_time='%s',modify_time='%s'";
	$strnotesql = "INSERT INTO note(path,title,create_time,modify_time,notebook_path) VALUES('%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE title='%s',create_time='%s',modify_time='%s',notebook_path='%s'";
	foreach($notebooks as $notebook){
		$path = $mysqli->real_escape_string($notebook->path);
		$name = $mysqli->real_escape_string($notebook->name);
		$notes_num = $mysqli->real_escape_string($notebook->notes_num);
		$notebook_group = $mysqli->real_escape_string($notebook->group);
		$create_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $notebook->create_time));
		$modify_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $notebook->modify_time));
		$strsql = sprintf($strnotebooksql,$path,$name,$notes_num,$notebook_group,$create_time,$modify_time,$name,$notes_num,$notebook_group,$create_time,$modify_time);
		if ($mysqli->query($strsql) === FALSE) {
			printf("flush notebook %s failed: %s<br />", $notebook->name, $mysqli->error);
			continue;
		}
		$response = $client->listNotes($oauth_access_token,$oauth_access_secret,$path);
		if ($notes = parseNotes($response)) {
			foreach($notes as $notepath){
				$response = $client->getNote($oauth_access_token,$oauth_access_secret,$notepath);
				if ($note = parseNote($response)){
				$path = $mysqli->real_escape_string($note->path);
				$title = $mysqli->real_escape_string($note->title);
				$create_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $note->create_time));
				$modify_time = $mysqli->real_escape_string(date("Y-m-d H:i:s", $note->modify_time));
				$notebook_path = $mysqli->real_escape_string($notebook->path);
					$strsql = sprintf($strnotesql,$path,$title,$create_time,$modify_time,$notebook_path,$title,$create_time,$modify_time,$notebook_path);
					if ($mysqli->query($strsql) === FALSE) {
						printf("flush note %s failed: %s<br />", $note->title, $mysqli->error);
						continue;
					}
				}
			}
		}
		else{
			printf("listNotes %s failed<br />", $notebook->name);
		}
	}
	echo "flush end...<br />";
}
else{
	php_die('listNotebooks error，retry please');
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

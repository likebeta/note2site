<!doctype html>
<html>
<head>
<title>note2site</title>
<meta charset="utf8" />
</head>
<body>
<?php
require('config.php');

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($mysqli->connect_errno) {
	$msg = "Connect failed: ".$mysqli->connect_error;
	php_die($msg);
}

$strsql = "SELECT path,name,notes_num FROM notebook";
if ($results = $mysqli->query($strsql)) {
	echo '<ul>';
	while ($row = $results->fetch_assoc()) {
        echo '<li><a href="'.$site_url.'/topic'.$row['path'].'.html" title="'.$row['notes_num'].'notes" target="_blank">'.$row['name'].'</a></li>';
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

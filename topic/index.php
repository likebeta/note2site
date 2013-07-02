<?php
$path = isset($_GET['path']) ? $_GET['path']:'';
if ($path === '')
{
	include('404.php');
	exit;
}

// get notes from the notebook
require_once('../config.php');

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
/* check connection */
if ($mysqli->connect_errno) {
	$msg = "Connect failed: ".$mysqli->connect_error;
	die($msg);
}
$title = '';
$strsql = "SELECT name FROM notebook where path='/".$path."'";
if ($results = $mysqli->query($strsql)) {
	if ($results->num_rows !== 1) {
		include('../404.php');
		exit();
	}
	$row = $results->fetch_assoc();
	$title = $row['name'];
}

$strsql = "SELECT path,title,create_time,modify_time FROM note WHERE notebook_path='/".$path."' ORDER BY create_time DESC";
$str_notes = '';
if ($results = $mysqli->query($strsql)) {
	$str_notes .= '<ul>';
	while ($row = $results->fetch_assoc()) {
       $str_notes .= '<li><a href="'.$site_url.'/archive'.$row['path'].'.html" title="'.$row['title'].'" target="_blank">'.$row['title'].'</a></li>';
    }
    $str_notes .= '</ul>';
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $title;?> | note2site</title>
<meta charset="utf-8" />
</head>
<body>
<?php
	echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$site_url\" title=\"home\">home</a> &gt; $title</div>";
	echo $str_notes;
?>
</body>
</html>

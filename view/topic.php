<?php
$str_notes = '';
$str_notes .= '<ul>';
foreach ($notes as $note) {
   $str_notes .= '<li><a href="'.SITE_URL.'/archive'.$note->path.PAGE_SUFFIX.'" title="'.$note->title.'" target="_blank">'.$note->title.'</a></li>';
}
$str_notes .= '</ul>';
?>
<!doctype html>
<html>
<head>
<title><?php echo $notebook->name;?></title>
<meta charset="utf-8" />
</head>
<body>
<?php
	echo '<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.SITE_URL.'" title="home">home</a> &gt; '.$notebook->name.'</div>';
	echo $str_notes;
?>
</body>
</html>

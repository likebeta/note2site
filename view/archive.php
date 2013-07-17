<!doctype html>
<html>
<head>
<title><?php echo $note->title;?></title>
<meta charset="utf-8" />
</head>
<body>
<?php
	echo '<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.SITE_URL.'" title="home">home</a> &gt; <a href="'.SITE_URL.'/topic'.$notebook->path.PAGE_SUFFIX."\" title=\"$notebook->name\">$notebook->name</a> &gt; ".$note->title."</div>";
	echo $note->content;
?>
</body>
</html>

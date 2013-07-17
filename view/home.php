<!doctype html>
<html>
<head>
<title>note2site</title>
<meta charset="utf-8" />
</head>
<body>
<?php
echo '<ul>';
foreach ($notebooks as $notebook)
{
    echo '<li><a href="'.SITE_URL.'/topic'.$notebook->path.PAGE_SUFFIX.'" title="'.$notebook->notes_num.'notes" target="_blank">'.$notebook->name.'</a></li>';
}
echo '</ul>';
?>
</body>
</html>

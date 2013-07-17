<?php
require_once(MODEL_DIR.'/archive.php');
if (defined('CALL_ERROR_VIEW')) {
	require_once(VIEW_DIR.'/error.php');	
}
else if (defined('CALL_404_VIEW')) {
	require_once(VIEW_DIR.'/404.php');
}
else{
	require_once(VIEW_DIR.'/archive.php');
}
?>

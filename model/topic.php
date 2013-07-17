<?php
if (!isset($route_params['params']) || count($route_params['params']) != 1) {
	define('CALL_404_VIEW', true);
}
else {
	$path = '/'.$route_params['params'][0];
	require(DAO_DIR.'/mysql.class.php');
	$dao = new MysqlDao();
	$notebook = $dao->getNotebookByPath($path);
	if ($notebook === false) {
		define('CALL_ERROR_VIEW', true);
		define('ERROR_TITLE', '数据库错误');
		define('ERROR_REASON', $dao->getLastError());
	}
	else if ($notebook === true) {
		define('CALL_404_VIEW', true);
	}
	else {
		$notes = $dao->getNotes($path);
		if ($notes === false) {
			define('CALL_ERROR_VIEW', true);
			define('ERROR_TITLE', '数据库错误');
			define('ERROR_REASON', $dao->getLastError());
		}
	}	
}
?>

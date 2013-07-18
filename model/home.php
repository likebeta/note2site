<?php
require(DAO_DIR.'/mysql.class.php');
$dao = new MysqlDao();
$notebooks = $dao->getNotebooks();
if ($notebooks === false)
{
	define('CALL_ERROR_VIEW', true);
	define('ERROR_TITLE', '数据库错误');
	define('ERROR_REASON', $dao->getLastError());
}
else
{
	if (count($notebooks) > 0) {
		$notes = $dao->getNotes($notebooks[0]->path);
		if ($notes === false)
		{
			define('CALL_ERROR_VIEW', true);
			define('ERROR_TITLE', '数据库错误');
			define('ERROR_REASON', $dao->getLastError());
		}
	}
	else
	{
		$notes = array();
	}
}
?>

<?php
class MysqlDao
{
	private $mysql = null;
	private $lasterror = '';
	function __construct()
	{
		$this->mysql = new mysqli(DB_MYSQL_HOST,DB_MYSQL_USERNAME,DB_MYSQL_PASSWORD,DB_MYSQL_DBNAME,DB_MYSQL_PORT);
		if ($this->mysql->connect_errno)
		{
			$this->lasterror = sprintf("Connect Error (%s) : %s\n",$this->mysql->connect_errno,$this->mysql->connect_error);
		}
	}

	function __destruct()
	{
		if (!$this->mysql->connect_errno)
		{
			$this->mysql->close();
		}		
	}

	// 获取出错原因
	function getLastError()
	{
		return $this->lasterror;
	}

	function getNotebooks()
	{
		if ($this->mysql->connect_errno)
		{
			return false;
		}
		$strsql = "SELECT path,name,notes_num FROM notebook";
		$result = $this->mysql->query($strsql);
		if (!$result)
		{
			$this->lasterror = sprintf("%s, %s",$this->mysql->errno,$this->mysql->error);
			return false;
		}
		$notebooks = array();
		while ($row = $result->fetch_assoc())
		{
			$notebooks[] = new Notebook($row['path'],$row['name'],$row['notes_num']);
		}
		return $notebooks;
	}

	function getNotebookByPath($path)
	{
		if ($this->mysql->connect_errno)
		{
			return false;
		}
		$strsql = "SELECT path,name,notes_num FROM notebook WHERE path='$path'";
		$result = $this->mysql->query($strsql);
		if (!$result)
		{
			$this->lasterror = sprintf("%s, %s",$this->mysql->errno,$this->mysql->error);
			return false;
		}
		$row = $result->fetch_assoc();
		if (!$row)
		{
			return true;
		}
		$notebook = new Notebook($row['path'],$row['name'],$row['notes_num']);
		return $notebook;
	}

	function getNotebookByNotePath($path)
	{
		if ($this->mysql->connect_errno)
		{
			return false;
		}
		$strsql = "SELECT * FROM notebook WHERE notebook.path=(SELECT note.notebook_path FROM note WHERE note.path='$path')";
		$result = $this->mysql->query($strsql);
		if (!$result)
		{
			$this->lasterror = sprintf("%s, %s",$this->mysql->errno,$this->mysql->error);
			return false;
		}
		$row = $result->fetch_assoc();
		if (!$row)
		{
			return true;
		}
		$notebook = new Notebook($row['path'],$row['name'],$row['notes_num']);
		return $notebook;
	}

	function getNotes($path)
	{
		if ($this->mysql->connect_errno)
		{
			return false;
		}
		$strsql = "SELECT path,title,create_time,modify_time FROM note WHERE notebook_path='$path' ORDER BY create_time DESC";
		$result = $this->mysql->query($strsql);
		if (!$result)
		{
			$this->lasterror = sprintf("%s, %s",$this->mysql->errno,$this->mysql->error);
			return false;
		}
		$notes = array();
		while ($row = $result->fetch_assoc())
		{
			$notes[] = new Note($row['path'],$row['title'],$row['create_time'],$row['modify_time']);
		}
		return $notes;
	}
}

class Notebook
{	
	public $path;
	public $name;
	public $notes_num;
	function __construct($path,$name,$notes_num)
	{
		$this->path = $path;
		$this->name = $name;
		$this->notes_num = $notes_num;
	}
}

class Note
{
	public $path;
	public $title;
	public $create_time;
	public $modify_time;
	function __construct($path,$title,$create_time,$modify_time)
	{
		$this->path = $path;
		$this->title = $title;
		$this->create_time = $create_time;
		$this->modify_time = $modify_time;
	}
}

?>
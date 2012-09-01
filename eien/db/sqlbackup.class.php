<?php
/** 此类完成从数据库到SQL语句的转化
 * @author WaiTing
 * @package Eien.Web.DB.SQL
 * @version 1.1.0
 * @@dependency db/db.class.php,db/sqlscript.class.php,filesys/file.class.php */

define('EIEN_SQLBACKUP_CLASS', 'db/sqlbackup.class.php');

/** 以下是数据库备份类SQLBackup
 * @version 1.1.0 */
class SQLBackup
{
	/**
	 * @var IDBConnection */
	private $cnn = null;    # 数据库操作接口 @@use interface IDBConnection
	/**
	 * @var IFile */
	private $file = null;  # 文件操作接口 @@use interface IFile
	/**
	 * @var array */
	private $tableQuotes = array( '', '' );
	/**
	 * @param $cnn IDBConnection
	 * @param $file IFile
	 * @return SQLBackup */
	function __construct( IDBConnection $cnn, IFile $file )
	{
		$this->cnn = $cnn;
		$this->file = $file;
		$this->tableQuotes = $cnn->tableQuotes();
	}
	/**
	 * @param $tablename string */
	function backupTableStructure($tablename)
	{
		$res = $this->cnn->query("SHOW CREATE TABLE {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]};");
		$row = $res->fetchRow(); # @@use interface IDBResult
		$this->file->puts("\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("-- Table {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]} structure;\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("DROP TABLE IF EXISTS {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]};\n");
		$this->file->puts($row[1].";\n");
	}
	/**
	 * @param string $tablename */
	function backupTableData($tablename)
	{
		$this->file->puts("\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("-- Table {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]} data;\n");
		$this->file->puts("-- -----------------------------\n");
		$res = $this->cnn->query("SELECT * FROM {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]};");
		$sql = "INSERT INTO {$this->tableQuotes[0]}{$tablename}{$this->tableQuotes[1]} VALUES (";
		while ($row = $res->fetchRow())
		{
			$i = 0;
			$valueArr = array();
			foreach ($row as $value)
			{
				$value = $this->cnn->escape($value);
				$valueArr[$i] = "'$value'";
				$i++;
			}
			$this->file->puts($sql.implode(',',$valueArr).');'."\n");
		}
	}
	/** 备份数据库 */
	function backupDB($backupStructure = true)
	{
		$this->file->puts("-- ------------------------------------------\n");
		$this->file->puts("-- 此SQL脚本由Eien database backup tools产生\n");
		$this->file->puts("-- Eien database backup tools version 1.1.0\n");
		$this->file->puts("-- \n");
		$this->file->puts("--  Author: WaiTing\n");
		$this->file->puts("--      QQ: 162057326\n");
		$this->file->puts("-- WebSite: www.x86pro.com\n");
		$this->file->puts("-- ------------------------------------------\n");

		$res = $this->cnn->listTables();
		$tablenames = array();
		while ($row = $res->fetchRow())
			$tablenames[] = $row[0];

		if ($backupStructure)
		{
			foreach ($tablenames as $tablename)
				$this->backupTableStructure($tablename);
		}

		foreach ($tablenames as $tablename)
			$this->backupTableData($tablename);
	}
	/** 恢复数据库 */
	function resumeDB()
	{
		$script = new SQLScript($this->cnn); # @@use class SQLScript
		$script->loadSQLFromIFile($this->file); # @@use interface IFile
		$script->execute();
	}
}

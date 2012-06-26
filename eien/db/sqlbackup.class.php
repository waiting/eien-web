<?
/**
 * 此类完成从数据库到SQL语句的转化
 * @author WaiTing
 * @package Eien.Web.DB.SQL
 * @version 1.0.0
 */
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SQLBACKUP_CLASS', 1);

/**
 * 以下是数据库备份类SQLBackup
 * @version 1.1.0
 */
class SQLBackup
{
	/**
	 * @var IDBConnection
	 */
	private $cnn = null;    # 数据库操作接口
	/**
	 * @var IFile
	 */
	private $file = null;  # 文件操作接口
	/**
	 * @param IDBConnection $cnn
	 * @param IFile $file
	 * @return SQLBackup
	 */
	function __construct(IDBConnection $cnn, IFile $file)
	{
		$this->cnn = $cnn;
		$this->file = $file;
	}
	/**
	 * @param string $tablename
	 */
	function backupTableStructure($tablename)
	{
		$res = $this->cnn->query("SHOW CREATE TABLE $tablename;");
		$row = $res->fetchRow();
		$this->file->puts("\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("-- Table $tablename structure;\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("DROP TABLE IF EXISTS $tablename;\n");
		$this->file->puts($row[1].";\n");
	}
	/**
	 * @param string $tablename
	 */
	function backupTableData($tablename)
	{
		$this->file->puts("\n");
		$this->file->puts("-- -----------------------------\n");
		$this->file->puts("-- Table $tablename data;\n");
		$this->file->puts("-- -----------------------------\n");
		$res = $this->cnn->query("SELECT * FROM $tablename;");
		$sql = "INSERT INTO $tablename VALUES (";
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
	/**
		备份数据库
	 */
	function backupDB()
	{
		$this->file->puts("-- ----------------------------------------\n");
		$this->file->puts("-- 此SQL脚本由Eien database backup tools产生\n");
		$this->file->puts("-- Eien database backup tools version 1.1.0\n");
		$this->file->puts("-- \n");
		$this->file->puts("-- Author : WaiTing\n");
		$this->file->puts("-- QQ     : 162057326\n");
		$this->file->puts("-- WebSite: www.x86pro.com\n");
		$this->file->puts("-- ----------------------------------------\n");

		$res = $this->cnn->query("SHOW TABLES;");
		$tablenames = array();
		while ($row = $res->fetchRow())
		{
			$tablenames[] = $row[0];
			$this->backupTableStructure($row[0]);
		}
		foreach ($tablenames as $tablename)
			$this->backupTableData($tablename);
	}
	/**
		恢复数据库
	 */
	function resumeDB()
	{
		$s = new SQLScript($this->cnn);
		$s->setSQLTextFromIFile($this->file, false, true);
	}
}
?>
<?php
/** SQL脚本解释器
 * @author WaiTing
 * @version 1.0.0
 * @@dependency db/db.class.php,filesys/file.class.php */

define('EIEN_SQLSCRIPT_CLASS', 'db/sqlscript.class.php');

/** SQL脚本执行器
 * @version 1.0.1 */
class SQLScript
{
	private $sqlcmdArr = array();   # SQL命令数组
	private $resArr = array();      # 结果数组，执行完毕后存放结果
	private $errArr = array();      # 错误数组
	private $cnn = null;         # 数据库操作接口 @@use interface IDBConnection
	/**
	 * @param $cnn IDBConnection
	 * @return SQLScript */
	function __construct(IDBConnection $cnn)
	{
		$this->cnn = $cnn;
	}
	/** 加载SQL
	 * @param $sqlText string */
	function loadSQL($sqlText)
	{
		$i = 0;
		$cmd = '';
		while (ereg("([^\r\n]+)([\r\n]|\r\n|$)", $sqlText, $regs))
		{
			$pos = strpos($sqlText, $regs[0]);
			$sqlText = substr($sqlText, $pos + strlen($regs[0]));
			$head = ltrim($regs[1]);
			if (substr($head,0,2) == '--') continue;
			$cmd .= rtrim($regs[1]);
			if ($cmd[strlen($cmd) - 1] == ';')
			{
				$this->sqlcmdArr[$i] = $cmd;
				$i++;
				$cmd = '';
			}
			else
				$cmd .= "\n";
		}
		return $i;
	}
	/** 从文件接口加载SQL @@use interface IFile
	 * @param $ifile IFile 文件接口 */
	function loadSQLFromIFile(IFile $ifile)
	{
		$cmd = '';
		$i = 0;
		while (!$ifile->eof())
		{
			$line = rtrim($ifile->gets());
			if ($line == '') continue;
			if (substr($line,0,2) == '--') continue;
			$cmd .= $line;
			if ($cmd[strlen($cmd) - 1] == ';')
			{
				$this->sqlcmdArr[$i] = $cmd;
				$i++;
				$cmd='';
			}
			else
				$cmd .= "\n";
		}
		return $i;
	}
	/** 从文件载入SQL
	 * @param $sqlfile string */
	function loadSQLFromFile($sqlfile)
	{
		return $this->loadSQLFromIFile(new File($sqlfile, 'r', false));
	}
	/** 执行脚本
	 * @param $onErrorNext bool[optional] 遇到错误继续执行下一句
	 * @param $storeError bool[optional] 存储错误
	 * @param $storeResult bool[optional] 存储结果
	 * @return int 成功执行的语句数 */
	function execute($onErrorNext = false, $storeError = true, $storeResult = true)
	{
		$i = 0;
		foreach ($this->sqlcmdArr as $sqlcmd)
		{
			$res = $this->cnn->query($sqlcmd);
			if ($storeResult) $this->resArr[$i] = $res;
			$err = $this->cnn->error();
			if ($storeError)
				$this->errArr[$i] = $err;
			else
			{
				if ($err)
				{
					echo "SQL Syntax Index $i:<br />\n$sqlcmd<br />\n";
					echo "<strong style='color:red;'>$err</strong><br />\n";
				}
			}
			if ($err && !$onErrorNext) break;
			$i++;
		}
		return $i;
	}
	/** 返回结果数组 */
	function results() { return $this->resArr; }
	/** 返回命令数组 */
	function commands() { return $this->sqlcmdArr; }
	/** 返回错误数组 */
	function errors() { return $this->errArr; }
}

<?php
/*
 * SQL脚本解释器
 * @author WaiTing
 * @version 1.0.0
 */
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SQLSCRIPT_CLASS', 1);

/**
 * SQL脚本执行器
 * @version 1.0.1
 */
class SQLScript
{
	/**
	 * @var array
	 */
	private $sqlcmdArr = array();   # SQL命令数组
	/**
	 * @var array
	 */
	private $resArr = array();      # 结果数组，执行完毕后存放结果
	/**
	 * @var array
	 */
	private $errArr = array();      # 错误数组
	/**
	 * @var string
	 */
	private $sqlText = '';       # SQL文本
	/**
	 * @var IDBConnection
	 */
	private $cnn = null;         # 数据库操作接口
	/**
	 * @param IDBConnection $cnn
	 * @return SQLScript
	 */
	function __construct(IDBConnection $cnn)
	{
		$this->cnn = $cnn;
	}
	/**
	 * @param string $sqlText
	 */
	function setSQLText($sqlText)
	{
		$this->sqlText = $sqlText;
	}
	/**
	 * @param IFile $sqlfile 文件接口
	 * @param bool[optional] $store
	 * @param bool[optional] $execute
	 */
	function setSQLTextFromIFile(IFile $sqlfile, $store = true, $execute = false)
	{
		$cmd = '';
		$i = 0;
		while (!$sqlfile->eof())
		{
			$line = rtrim($sqlfile->gets());
			if($line == '') continue;
			if(substr($line,0,2) == '--') continue;
			$cmd .= $line;
			if($cmd[strlen($cmd) - 1] == ';')
			{
				if ($store)	$this->sqlcmdArr[$i] = $cmd;
				if ($execute)
				{
					$this->cnn->directQuery($cmd);
					if ($err = $this->cnn->error()) echo "$cmd :<br />\n".$err."<br />\n";
				}
				$i++;
				$cmd='';
			}
			else
				$cmd .= "\n";
		}
	}
	/**
	 * @param string $sqlfile
	 * @param bool[optional] $store
	 * @param bool[optional] $execute
	 */
	function setSQLTextFromFile($sqlfile, $store = true, $execute = false)
	{
		$f = new File($sqlfile,'r',false);
		$this->setSQLTextFromIFile($f, $store, $execute);
	}
	/**
	 * @param bool[optional] $store
	 * @param bool[optional] $execute
	 */
	function parseSQLCommand($store = true, $execute = false)
	{
		$sqlText = $this->sqlText;
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
				if ($store)	$this->sqlcmdArr[$i] = $cmd;
				if ($execute)
				{
					$this->cnn->directQuery($cmd);
					if ($err = $this->cnn->error()) echo "$cmd :<br />\n".$err."<br />\n";
				}
				$i++;
				$cmd = '';
			}
			else
				$cmd .= "\n";
		}
	}
	/**
	 * @param bool[optional] $on_err_continue
	 * @param bool[optional] $writeErr
	 * @param bool[optional] $writeRes
	 */
	function execute($on_err_continue = true, $writeErr = true, $writeRes = true)
	{
		$i = 0;
		foreach ($this->sqlcmdArr as $sqlcmd)
		{
			$res = $this->cnn->query($sqlcmd);
			if ($writeRes) $this->resArr[$i] = $res;
			$err = $this->cnn->error();
			if ($writeErr)
				$this->errArr[$i] = $err;
			else
			{
				if ($err)
				{
					echo $sqlcmd." :<br />\n";
					echo $err."<br />\n";
				}
			}
			if($err && !$on_err_continue) break;
			$i++;
		}
	}
	/**
	 * @return array
	 */
	function resultArr(){ return $this->resArr; }
	/**
	 * @return array
	 */
	function commandArr(){ return $this->sqlcmdArr; }
	/**
	 * @return array
	 */
	function errorArr(){ return $this->errArr; }
}
?>
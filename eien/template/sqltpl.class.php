<?php
/**	SQL模板引擎
	sql template engine
	@author WaiTing
	@version 0.1.0
 */
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SQLTPL_CLASS', 1);

class SQLTpl extends Template
{
public $sqlDir;
private $sqlt;
public function __construct($sqlt, $sqlDir = null)
{
	parent::__construct(TplTag::PROC_RESULT);
	$this->sqlDir = $sqlDir ? $sqlDir : config('sql_path');
	$this->cache = 0;
	$this->sqlt = $sqlt;
}
public function sql()
{
	$this->templateDir = $this->sqlDir;
	return $this->fetch($this->sqlt);
}

public function __toString()
{
	return $this->sql();
}

}

?>
<?php
/**	SQL模板引擎
	sql template engine
	@author WaiTing
	@version 0.1.0 */
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SQLTPL_CLASS', 'template/sqltpl.class.php');

class SQLTpl extends Template
{
	private $sqlt;
	public function __construct($sqlt)
	{
		parent::__construct(TplTag::PROC_RESULT);
		$this->templateDir = '';
		$this->cache = 0;
		$this->sqlt = $sqlt;
	}
	public function sql()
	{
		return $this->fetch($this->sqlt);
	}

	public function __toString()
	{
		return $this->sql();
	}

}

<?php
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_VARSFILE_CLASS', 1);

class ValueSrcCode
{
public $m_valueStr;
public $m_valueRaw;
public $m_type;
/**
 * @param mixed $value
 * @param string $type
 * @return ValueSrcCode
 */
public function __construct($value)
{
	$this->m_valueRaw = $value;
	$this->m_type = gettype($value);
	$this->updateValueSrc();
}
public function updateValueSrc()
{
	$this->m_valueStr = self::valueSrc($this->m_valueRaw);
}
/**
 * @param mixed $value
 * @return string
 */
public static function valueSrc($value)
{
	switch (gettype($value))
	{
	case 'string':
		return '"'.addcslashes($value,"\\\"\x0..\x19").'"';
		break;
	case 'array':
		$str = 'array(';
		$isnofirst = false;
		foreach ($value as $k => $v)
		{
			if ($isnofirst) $str .= ', ';
			$keyStr = '';
			$keyStr = ValueSrcCode::valueSrc($k).' => ';
			$str .= $keyStr.ValueSrcCode::valueSrc($v);
			$isnofirst = true;
		}
		$str .= ')';
		return $str;
		break;
	}
	return $value;
}
}

class VarsFile
{
private $arVars = array();
private $szText;
private $varsfile;
/**
 * @param string $varsfile
 * @return VarsFile */
public function __construct($varsfile = null)
{
	$this->varsfile = $varsfile;
	if(file_exists($varsfile))
	{
		$this->loadFile();
	}
}
public function loadFile($varsfile = null, $autoParse = true)
{
	if ($varsfile != '')
	{
		$this->varsfile = $varsfile;
	}
	if($fp = fopen($this->varsfile, 'rb'))
	{
		$filelen = filesize($this->varsfile);
		$this->szText = fread($fp, $filelen);
		fclose($fp);
	}
	if ($autoParse)
	{
		$this->parse();
	}
}
public function parse()
{
	preg_match_all('/\$\w+\b/', $this->szText, $arSub);
	include_once $this->varsfile;
	foreach ($arSub[0] as $varname)
	{
		$varname = substr($varname, 1);
		$this->arVars[$varname] = new ValueSrcCode($$varname);
	}
	return $this->arVars;
}
public function saveFile($varsfile = null, $autoAntParse = true)
{
	$filename = $this->varsfile;
	if ($varsfile != '')
	{
		$filename = $varsfile;
	}
	if ($autoAntParse) $this->antParse();
	$fp = fopen($filename, 'w');
	fputs($fp, $this->szText);
	fclose($fp);
}
public function antParse()
{
	$str = "<?php\n";
	foreach ($this->arVars as $k => $var)
	{
		$str .= '$'.$k.' = '.$var->m_valueStr.";\n";
	}
	$str .= "?>";
	$this->szText = $str;
	return $str;
}
public function getVar($varname)
{
	return $this->arVars[$varname]->m_valueRaw;
}
public function setVar($varname, $value)
{
	if (!isset($this->arVars[$varname]))
	{
		$this->arVars[$varname] = new ValueSrcCode(null);
	}
	$varObj = $this->arVars[$varname];
	$varObj->m_valueRaw = $value;
	$varObj->updateValueSrc();
}
public function delVar($varname)
{
	unset($this->arVars[$varname]);
}
}
?>
<?php
/** 变量配置文件模板类 */
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_VARSTPL_CLASS', 'template/varstpl.class.php');

/** 值-源代码转换 */
class ValueSrcCode
{
	public $valueSrc;
	public $value;
	public $type;
	/**
	 * @param mixed $value
	 * @return ValueSrcCode */
	public function __construct($value)
	{
		$this->value = $value;
		$this->type = gettype($value);
		$this->updateValueSrc();
	}
	public function updateValueSrc()
	{
		$this->valueSrc = self::valueSrc($this->value);
	}
	/**
	 * @param mixed $value
	 * @return string */
	public static function valueSrc($value)
	{
		switch (gettype($value))
		{
		case 'NULL':
			return 'null';
			break;
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

/** 变量名Tag,识别变量名 */
class VarNamesTag extends Tag
{
	public function __construct()
	{
		parent::__construct();
		$this->odd = true;
	}
	public function _quickProcess()
	{
		$varname = $this->tagName;
		$this->tagcx->tpl->assign($varname, 'null');
	}
	protected function replaceProc()
	{
		return '$'.$this->tagName.' = {=$'.$this->tagName.'};';
	}
}

class VarNamesContext extends TagContext
{
	public $tpl;
	public function __construct(VarsTpl $tpl)
	{
		parent::__construct('#', '#', 'TextNode', array());
		$this->tpl = $tpl;
	}
	public function fromTagName($tagName)
	{
		$t = new VarNamesTag();
		$t->tagcx = $this; # 设置context
		$t->tagName = $tagName;
		return $t;
	}
	public function exists($tagName)
	{
		return !preg_match('/[^0-9A-Za-z_]/', $tagName);
	}
}

/** 变量配置文件模板 */
class VarsTpl extends Template
{
	private $varsTpl;
	private $varsTplText = '';
	private $varsFile;
	public function __construct($varsTpl, $varsFile)
	{
		parent::__construct(TplTag::PROC_RESULT);
		$this->templateDir = '';
		$this->cache = 0;
		$this->varsTpl = $varsTpl;
		$this->varsFile = $varsFile;
		$this->load($varsTpl, $varsFile);
	}
	public function load($varsTpl, $varsFile)
	{
		if (file_exists($varsTpl))
		{
			$cx = new VarNamesContext($this);
			$parser = new TagParser($cx);
			$f = new File($varsTpl, 'r');
			$doc = $parser->parse($f->bufData);
			$f->close();
			$this->varsTplText = $doc->process(Tag::PROC_REPLACE);
		}
		if (file_exists($varsFile))
		{
			include $varsFile;
			foreach ($this->varcx as $name => $val)
			{
				if (isset($$name))
				{
					$this->assign($name, ValueSrcCode::valueSrc($$name));
				}
			}
		}
	}
	public function save($varsFile = null)
	{
		$filename = $this->varsFile;
		if ($varsFile != '')
		{
			$filename = $varsFile;
		}
		$f = new File($filename, 'w');
		$f->puts($this->resultTplText($this->varsTplText));
		$f->close();
	}
	public function getVar($name)
	{
		@eval('$v = '.$this->varcx[$name].';');
		return $v;
	}
	public function setVar($name, $val)
	{
		$this->varcx[$name] = ValueSrcCode::valueSrc($val);
	}
}

/** 简单变量配置文件类 */
class VarsFile
{
	private $arVars = array();
	private $szText;
	private $varsfile;
	/**
	 * @param string $varsfile
	 * @return VarsFile */
	public function __construct($varsfile)
	{
		$this->varsfile = $varsfile;
		if (file_exists($varsfile))
		{
			$this->load();
		}
	}
	public function load()
	{
		if($fp = fopen($this->varsfile, 'rb'))
		{
			$size = filesize($this->varsfile);
			$this->szText = fread($fp, $size);
			fclose($fp);
		}
		$this->parse();
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
	public function antParse()
	{
		$str = "<?php\n";
		foreach ($this->arVars as $k => $var)
		{
			$str .= '$'.$k.' = '.$var->valueSrc.";\n";
		}
		$str .= "";
		$this->szText = $str;
		return $str;
	}
	public function save($varsfile = null)
	{
		$filename = $this->varsfile;
		if ($varsfile != '')
		{
			$filename = $varsfile;
		}
		$this->antParse();
		$fp = fopen($filename, 'w');
		fputs($fp, $this->szText);
		fclose($fp);
	}
	public function getVar($varname)
	{
		return $this->arVars[$varname]->value;
	}
	public function setVar($varname, $value)
	{
		if (!isset($this->arVars[$varname]))
		{
			$this->arVars[$varname] = new ValueSrcCode(null);
		}
		$varObj = $this->arVars[$varname];
		$varObj->value = $value;
		$varObj->updateValueSrc();
	}
	public function delVar($varname)
	{
		unset($this->arVars[$varname]);
	}
	public function __set($name, $value)
	{
		$this->setVar($name, $value);
	}

	public function __get($name)
	{
		return $this->getVar($name);
	}

	public function __isset($name)
	{
		return array_key_exists($name, $this->arVars);
	}

	public function __unset($name)
	{
		$this->delVar($name);
	}
}

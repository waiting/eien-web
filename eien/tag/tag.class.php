<?php
/** 标签处理 */

//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_TAG_CLASS', 'tag/tag.class.php');
/** Tag Parser */
class TagParser
{
	/**	Tag 场景
	 *	@var TagContext */
	private $tagcx;
	public function __construct(TagContext $tagcx)
	{
		$this->tagcx = $tagcx;
	}
	/*搜索一个[...], 把前面的字符当作文本元素存入
	遇到一个[...], 如果是头,则新建元素,解析属性,再进入此函数*/
	/**	解析文本
	 *	@param $s string
	 *	@return TagDocument */
	public function parse($s)
	{
		$e = new TagDocument($this->tagcx);
		while ($s != '')
		{
			$pos = $this->searchTag($s, $tL);
			if ($pos == -1) // 说明没有搜到tag, 全当作文本来处理
			{
				$e->add($this->tagcx->newText($s));
				break;
			}
			else // 搜到tag
			{
				// 前面若有文本,先处理它
				if ($pos != 0)
				{
					$e->add($this->tagcx->newText(substr($s, 0, $pos)));
				}
	
				$tS = substr($s, $pos, $tL);
				$is = $this->is($tS);
				# 下次串
				$s = substr($s, $pos + $tL);
				if ($is !== null) // 不是空[]解析到了数据
				{
					// 读数据
					$this->read($tS, $tN, $d, $a);
					if ($is) // 是头
					{
						$em = $this->tagcx->fromTagName($tN);
						$em->defAttr = $d;
						$em->attrs = $a;
						$em->raw1 = $tS;
						$e->add($em); // 加入Document Tree中
						$em->_init(); // 调用初始化处理
						$e = ($em->odd ? $e : $em);
						if ($em->odd) $em->_quickProcess(); // 独立标签在此调用快速处理
					}
					else // 是尾
					{
						if (strcasecmp($e->tagName, $tN) != 0) // 如过不能匹配
						{
							// 就把此尾部当作文本处理,连入本元素的文本接点,继续...
							$e->addToLastTextNode($this->tagcx->newText($tS));
						}
						else // 匹配尾部
						{
							$e->raw2 = $tS;
							$e->_quickProcess();
							$e = $e->parent; // 回到父节点继续解析
						}
					}
				}
				else
				{
					$e->addToLastTextNode($this->tagcx->newText($tS));
				}
			}
		}
		return $e;
	}
	/** 搜一个支持的tag */
	private function searchTag($str, &$length = null)
	{
		$searchPos = 0;
		$pos = 0;
		$length = 0;
		do
		{
			$searchPos += $pos + $length;
			$pos = $this->search(substr($str, $searchPos), $length);
			$tagStr = substr($str, $searchPos + $pos, $length);
			$this->read($tagStr, $tagName);
		}
		while (!($pos == -1 || $this->tagcx->exists($tagName)));
		if ($pos == -1) return -1;
		else return $searchPos + $pos;
	}
	/** 搜索 [...] 这个串, 返回其开始位置,否则返回-1, $length表示搜到的长度,包括定界符 */
	private function search($str, &$length = null)
	{
		$length = 0;
		$ldelimLen = strlen($this->tagcx->ldelim);
		$rdelimLen = strlen($this->tagcx->rdelim);

		$pos = strpos($str, $this->tagcx->ldelim);
		if ($pos === false || $pos == strlen($str) - 1) return -1;
		$str = substr($str, $pos + $ldelimLen);
		$pos2 = strpos($str, $this->tagcx->rdelim);
		if ($pos2 === false || $pos2 == 0) return -1;
		$newStr = substr($str, 0, $pos2);
		$pos1 = strrpos($newStr, $this->tagcx->ldelim);

		if ($pos1 === false)
		{
			$length = $pos2 + $ldelimLen + $rdelimLen;
			return $pos;
		}
		$length = $pos2 - $pos1 + $rdelimLen;
		return $ldelimLen + $pos1 + $pos;
	}
	/** 判断一个[...] 是头还是尾, $data返回去掉界定符后的内容 */
	private function is($str, &$data = null)
	{
		$ldelimLen = strlen($this->tagcx->ldelim);
		$rdelimLen = strlen($this->tagcx->rdelim);
		$len = strlen($str);
		$rstriplen = $len - $ldelimLen - $rdelimLen; // 不包括左定界符,并去掉右定界符后的长度
		$data = substr($str, $ldelimLen, $rstriplen);
		if ($data != '' && $data[strlen($data) - 1] == '/')
		{
			$data = substr($data, 0, strlen($data) - 1);
		}
	
		if ($data != '' && $data[0] == '/') // 尾
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	/*  读取字符到键名,遇到=号键名结束,开始读值
		如果第一个字符不是引号,则读到空格为止值结束
		如果是引号,则继续读到另一个引号为止值结束
		读到\时应对下一字符进行判断,如果是引号,则不结束值,继续读取.
	*/
	/** 读取一个[...]内的数据 */
	private function read($str, &$tagName = null, &$defAttr = null, &$attrs = null)
	{
		$ret = $this->is($str, $data);
		if($ret !== null)
		{
			if ($ret) // 头
			{
				// read tagName
				$tagName = '';
				$len = strlen($data);
				for ($i = 0; $i < $len; $i++)
				{
					$ch = $data[$i];
					if ($ch == '=')
					{
						break;
					}
					else if (preg_match('@[^ \r\n\t]@', $tagName) && preg_match('@[ \r\n\t]@', $ch))
					{
						$i++; // skip this space
						break;
					}
					else
					{
						$tagName .= $ch;
					}
				}
				$tagName = strtolower($tagName);
				while ($i < $len && preg_match('@[ \r\n\t]@', $data[$i])) $i++; // skip space
				// end read tagName
				$data = substr($data, $i);
	
				$defAttr = '';
				$attrs = array();
				$isKey = true;
				$key = '';
				$value = '';
				$len = strlen($data);
				for ($i = 0; $i < $len; )
				{
					if ($isKey)
					{
						$this->key($data, $len, $i, $key, $i);
						$isKey = false;
						if ($key !== '')
						{
							$attrs[strtolower($key)] = '';
						}
					}
					else
					{
						$this->val($data, $len, $i, $value, $i);
						$isKey = true;
						// 跳过多余空白
						while ($i < $len && preg_match('@[ \r\n\t]@', $data[$i]))
						{
							$i++;  // skip " "
						}
						$value = stripcslashes($value);
						if ($key == '')
						{
							$defAttr = $value;
						}
						else
						{
							$attrs[strtolower($key)] = $value;
						}
					}
				}
			}
			else
			{
				// skip "/"
				$tagName = strtolower(substr($data, 1));
			}
			return true;
		}
		return false;
	}
	/** 读一个键 */
	private function key($str, $len, $start, &$key, &$pos)
	{
		$key = '';
		for ($i = $start; $i < $len; $i++)
		{
			$ch = $str[$i];
			if ($ch == '=') // 遇到=,键名结束,并跳过=
			{
				$i++;
				break;
			}
			else if (preg_match('@[ \r\n\t]@', $ch)) // 遇到空白就结束键名
			{
				break;
			}
			else
			{
				$key .= $ch;
			}
		}
		$pos = $i;
	}
	/** 读取一个值 */
	private function val($str, $len, $start, &$value, &$pos)
	{
		$value = '';
		$quote = '';
		for ($i = $start; $i < $len; $i++)
		{
			$ch = $str[$i];
			if ($i == $start && ($ch == "'" || $ch == '"'))
			{
				$quote = $ch;
			}
			else
			{
				if ($quote == '' && preg_match('@[ \r\n\t]@', $ch)) // 没有引号,遇到空白就结束值
				{
					break;
				}
				else if ($quote != '' && $ch == $quote) // 有引号,遇到相同引号就结束值
				{
					$slashes = 0;
					// 获取引号前反斜杠数
					while ($i - $slashes - 1 >= $start && $str[$i - $slashes - 1] == '\\') $slashes++;
	
					if ($slashes % 2) // 如果引号前反斜杠是单数,则说明引号不是边界.
					{
						$value .= $ch;
					}
					else // 是双数,结束,并跳过引号
					{
						$i++;  // skip "'" or '"'
						break;
					}
				}
				else
				{
					$value .= $ch;
				}
			}
		}
		$pos = $i;
	}
}

/** 节点基类 */
class Node
{
	const UE_TEXT = 0; # 文本
	const UE_ELEM = 1; # 元素
	const UE_ROOT = 2; # 根

	/**	Tag 场景
	 *	@var TagContext */
	public $tagcx;          # tag context
	/**	节点类型
	 *	@var int */
	public $type = 0;       # 0:文本  1:元素  2:Root
	/**	父节点
	 *	@var Tag */
	public $parent = null;  # 父元素
	/**	处理本元素 procType{0:原始串,1:经过处理,2:树}
	 *	@param $procType int
	 *	@return string */
	public function process($procType = Tag::PROC_RAW)
	{
		return null;
	}
	public function __toString()
	{
		return $this->process(Tag::PROC_SIMPLE);
	}
}

/** Text Node */
class TextNode extends Node
{
	/**	元素包含的文本数据,默认NULL.
	 *	@var string */
	public $value = null;
	public function __construct($str = '')
	{
		$this->type = Node::UE_TEXT;
		$this->value = $str;
	}
	public function process($procType = Tag::PROC_RAW)
	{
		switch ($procType)
		{
		case Tag::PROC_TREE:
			$t = '';
			$e = $this;
			while ($e->parent)
			{
				$t .= '&nbsp;&nbsp;&nbsp;&nbsp;';
				$e = $e->parent;
			}
			return $t.'<span style="font-family:Arial;">'.htmlspecialchars($this->value).'</span>'."<br />";
		default:
			return $this->value;
		}
	}
}

/** Tag */
class Tag extends Node
{
	const PROC_RAW = 0;     // 不处理
	const PROC_SIMPLE = 1;  // 简单修正
	const PROC_TREE = 2;    // 树型处理
	const PROC_STRIP = 3;   // 去掉tag
	const PROC_REPLACE = 4; // 替换处理

	const ADDSLASHES = "\\\"";
	/**	Tag名称
	 *	@var string */
	public $tagName = null;
	/**	默认属性
	 *	@var string */
	public $defAttr = null;
	/**	属性
	 *	@var array */
	public $attrs = array();
	/**	raw data
	 *	@var string */
	public $raw1 = null, $raw2 = null;
	/**	是否独立
	 *	@var bool */
	public $odd = false;
	/**	子元素节点
	 *	@var array */
	public $children = array();
	/**	构造函数 */
	public function __construct()
	{
		$this->odd = false;
		$this->type = Node::UE_ELEM;
	}
	/** 初始化处理，此过程执行时，子元素尚未载入，覆盖此过程一般用以初始化元素属性值 */
	public function _init()
	{
	}
	/** 快速处理 */
	public function _quickProcess()
	{
	}
	/**	向最后一个文本节点加字符串,如果没有文本节点,则马上添加文本节点
	 *	@param $t TextNode 文本节点 */
	public function addToLastTextNode(TextNode $t)
	{
		$cnt = count($this->children);
		if ($cnt)
		{
			for ($i = $cnt - 1; $i >= 0; $i--)
			{
				$e = $this->children[$i];
				if ($e->type == Node::UE_TEXT)
				{
					$e->value .= $t->value;
					break;
				}
			}
			if ($i < 0)
			{
				$e->add($t);
			}
		}
		else // 没有任何子节点,直接加上文本节点
		{
			$this->add($t);
		}
	}
	/**	添加子元素
	 *	@param $e Node */
	public function add(Node $e)
	{
		$e->parent = $this;
		$this->children[] = $e;
	}
	/**	是否有子元素
	 *	@return bool */
	public function hasChildren()
	{
		return (boolean)count($this->children);
	}
	/**	遍历整个节点树,并寻找指定TagName的元素
	 *	@param $arr array&
	 *	@param $tagName string */
	protected function _raw_getElements(&$arr, $tagName)
	{
		if ($this->tagName == $tagName)
		{
			$arr[] = $this;
		}
		foreach ($this->children as $elem)
		{
			$elem->_raw_getElements($arr, $tagName);
		}
	}
	/**	遍历整个节点树寻找指定TagName的元素,并返回找到的结果集
	 *	@param $tagName string
	 *	@return array */
	public function getElements($tagName)
	{
		$arr = array();
		$tagName = strtolower($tagName);
		$this->_raw_getElements($arr, $tagName);
		return $arr;
	}
	/**	处理子元素
	 *	@param $procType int 处理类型
	 *	@return string */
	public function childProcess($procType = Tag::PROC_RAW)
	{
		$s = '';
		foreach ($this->children as $e)
		{
			$s .= $e->process($procType);
		}
		return $s;
	}
	/**	进行处理 */
	public function process($procType = Tag::PROC_RAW)
	{
		switch ($procType)
		{
		case Tag::PROC_RAW:
			return $this->raw1 . $this->childProcess($procType) . $this->raw2;
		case Tag::PROC_SIMPLE:
			$attrStr = '';
			foreach ($this->attrs as $k=>$v)
			{
				$attrStr .= " $k=\"".addcslashes($v, Tag::ADDSLASHES)."\"";
			}
			$head = $this->tagcx->ldelim.$this->tagName.($this->defAttr !== '' && $this->defAttr !== null ? '="'.addcslashes($this->defAttr, Tag::ADDSLASHES).'"' : '').$attrStr.($this->odd ? ' /'.$this->tagcx->rdelim : $this->tagcx->rdelim);
			$foot = $this->odd ? '' : $this->tagcx->ldelim.'/'.$this->tagName.$this->tagcx->rdelim;
			return $head.$this->childProcess($procType).$foot;
		case Tag::PROC_TREE:
			return $this->treeProc();
		case Tag::PROC_STRIP:
			return $this->childProcess($procType);
		case Tag::PROC_REPLACE:
			return $this->replaceProc();
		}
		return '';
	}
	/**	子类可覆盖此函数以提供特殊的替换处理 */
	protected function replaceProc()
	{
		return $this->process(Tag::PROC_SIMPLE);
	}
	/**	树型处理 */
	protected function treeProc()
	{
		$t = '';
		$e = $this;
		while ($e->parent)
		{
			$t .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			$e = $e->parent;
		}
		$attrStr = '';
		foreach ($this->attrs as $k=>$v)
		{
			$attrStr .= " <strong style=\"font-family:Tahoma;\">$k</strong>=\""."<strong style=\"color:darkgreen;font-family:Tahoma;\">".addcslashes($v, Tag::ADDSLASHES)."</strong>"."\"";
		}
		$head = $t."<strong style=\"color:darkblue;font-family:Tahoma;\">".htmlspecialchars($this->tagcx->ldelim)."</strong>"."<strong style=\"font-family:Tahoma;\">".$this->tagName."</strong>".($this->defAttr !== '' ? '="'."<strong style=\"color:green;font-family:Tahoma;\">".addcslashes($this->defAttr, Tag::ADDSLASHES)."</strong>".'"' : '').$attrStr."<strong style=\"color:darkblue;font-family:Tahoma;\">".($this->odd ? ' /'.htmlspecialchars($this->tagcx->rdelim) : htmlspecialchars($this->tagcx->rdelim))."</strong>"."<br />";
	
		$foot = $this->odd ? '' : $t."<strong style=\"color:darkblue;font-family:Tahoma;\">".htmlspecialchars($this->tagcx->ldelim).'/'."</strong>"."<strong style=\"font-family:Tahoma;\">".$this->tagName."</strong>"."<strong style=\"color:darkblue;font-family:Tahoma;\">".htmlspecialchars($this->tagcx->rdelim)."</strong>"."<br />";
	
		return $head.$this->childProcess(Tag::PROC_TREE).$foot;
	}

	public function hasAttr($name)
	{
		return isset($this->attrs[strtolower($name)]);
	}
	public function getAttr($name)
	{
		return $this->hasAttr($name) ? $this->attrs[strtolower($name)] : null;
	}
	public function setAttr($name, $value)
	{
		if ($value == null)
		{
			unset($this->attrs[strtolower($name)]);
		}
		else
		{
			$this->attrs[strtolower($name)] = $value;
		}
	}
}

/** Tag文档 */ 
class TagDocument extends Tag
{
	public function __construct(TagContext $tagcx)
	{
		$this->tagName = '#__eien.document'; # Tag名称
		$this->type = Node::UE_ROOT;       # 0:文本  1:元素  2:Root
		$this->parent = null;
		$this->children = array();
		$this->tagcx = $tagcx;
	}
	public function process($procType = Tag::PROC_RAW)
	{
		return $this->childProcess($procType);
	}
	/**
	 * @param $e Tag
	 * @param $visitor function($e,$params)
	 * @param $params any */
	private static function _raw_traverse($e, $visitor, $params)
	{
		switch ($e->type)
		{
		case Node::UE_ELEM:
			$visitor($e,$params);
		case Node::UE_ROOT:
			foreach ($e->children as $child)
				self::_raw_traverse($child, $visitor,$params);
			break;
		case Node::UE_TEXT:
			$visitor($e,$params);
			break;
		}
	}
	/** 遍历
	 * @param $visitor function($e,$params)
	 * @param $params any */
	public function traverse($visitor, $params = null)
	{
		//var_dump($this->tagcx);exit;
		self::_raw_traverse($this, $visitor, $params);
	}
}


/** Tag场景 */
class TagContext
{
	private $tagArr = array();   // 标签=>类名映射
	private $txtCls = '';  // 文本节点类名
	public $ldelim; // 左定界符
	public $rdelim; // 右定界符
	
	/**	构造函数
	 *	@param $ldelim string 左定界符
	 *	@param $rdelim string 右定界符
	 *	@param $txtCls string 文本节点类
	 *	@param $tagArr array 支持的Tag: array('标签名称'=>'实现此标签的类') */
	public function __construct($ldelim, $rdelim, $txtCls, $tagArr)
	{
		$this->txtCls = $txtCls;
		$this->tagArr = $tagArr;
		$this->ldelim = $ldelim;
		$this->rdelim = $rdelim;
	}
	/**	向标签表里增加或修改标签
	 *	@param $tagName string
	 *	@param $clsName string */
	public function setTag($tagName, $clsName)
	{
		$this->tagArr[strtolower($tagName)] = $clsName;
	}
	/**	从标签表里删除标签
	 *	@param $tagName string */
	public function delTag($tagName)
	{
		unset($this->tagArr[strtolower($tagName)]);
	}
	/**	从TagName创建元素节点
	 *	@param $tagName string
	 *	@return Tag */
	public function fromTagName($tagName)
	{
		$t = null;
		if ($this->exists($tagName))
		{
			$cls = $this->tagArr[strtolower($tagName)];
			$t = new $cls();
		}
		else
			$t = new Tag();
		$t->tagcx = $this; # 设置context
		$t->tagName = $tagName;
		return $t;
	}
	/**	创建文本节点
	 *	@param $str string
	 *	@return TextNode */
	public function newText($str)
	{
		$txtCls = $this->txtCls;
		$t = new $txtCls($str);
		$t->tagcx = $this; # 设置context
		return $t;
	}
	/**	是否存在这个TagName的创建器
	 *	@param $tagName string
	 *	@return bool */
	public function exists($tagName)
	{
		return isset($this->tagArr[strtolower($tagName)]);
	}

}

/**	Tag 处理
 * @param $str string
 * @param $tagcx TagContext
 * @param $procType int
 * @return string */
function tag_proc($str, TagContext $tagcx, $procType = Tag::PROC_REPLACE)
{
	$p = new TagParser($tagcx);
	$doc = $p->parse($str);
	return $doc->process($procType);
}

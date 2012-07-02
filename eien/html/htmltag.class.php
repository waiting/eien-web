<?php
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_HTMLTAG_CLASS', 'html/htmltag.class.php');

/** html tag context */
class HtmlContext extends TagContext
{
public function __construct()
{
	parent::__construct('<', '>', 'HtmlTextNode', array(
		'a' => 'HtmlTagA',
		'img' => 'HtmlTagImg',
		'br' => 'HtmlTagBr',
		'u' => 'HtmlTag',
		'b' => 'HtmlTag',
		'i' => 'HtmlTag',
		'td' => 'HtmlTag',
		'th' => 'HtmlTag',
		'span' => 'HtmlTagSpan',
		'table' => 'HtmlChildTextRawTag',
		'tr' => 'HtmlChildTextRawTag',
		'p' => 'HtmlTagP',
	));
}
}

function html_proc($str, $procType = Tag::PROC_REPLACE)
{
	return tag_proc($str, new HtmlContext(), $procType);
}

/** HTML文本节点 */
class HtmlTextNode extends TextNode
{
public function process($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case Tag::PROC_SIMPLE:
	case Tag::PROC_REPLACE:
		return trim($this->value);
	case HtmlTag::PROC_ASUBB:
		return str_replace('&nbsp;&nbsp;&nbsp;&nbsp;', "\t", trim($this->value));
	}
	return parent::process($procType);
}

}

/** HTML标签节点 */
class HtmlTag extends Tag
{
const PROC_ASUBB = 42;   // 转为UBB代码
public function __construct()
{
	parent::__construct();
	$this->odd = false;
}
protected function asUbbProc()
{
	$attrStr = '';
	foreach ($this->attrs as $k=>$v)
	{
		$attrStr .= ' '.strtolower($k).'="'.addcslashes($v, Tag::ADDSLASHES).'"';
	}
	$head = '['.strtolower($this->tagName).$attrStr;
	$head .= ($this->odd ? ' /]' : ']');

	$foot = $this->odd ? '' : '[/'.strtolower($this->tagName).']';

	return $head.$this->childProcess(HtmlTag::PROC_ASUBB).$foot;
}
public function process($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case HtmlTag::PROC_ASUBB:
		return $this->asUbbProc();
	}
	return parent::process($procType);
}

}

/** 单独的标签 */
class HtmlOddTag extends HtmlTag
{
public function __construct()
{
	parent::__construct();
	$this->odd = true;
}

}

/*不处理直接子文本节点的标签*/
class HtmlChildTextRawTag extends HtmlTag
{
protected function asUbbProc()
{
	$attrStr = '';
	foreach ($this->attrs as $k=>$v)
	{
		$attrStr .= ' '.strtolower($k).'="'.addcslashes($v, Tag::ADDSLASHES).'"';
	}
	$head = '['.strtolower($this->tagName).$attrStr;
	if ($this->odd) $head .= ' /]';
	else $head .= ']';

	if ($this->odd) $foot = '';
	else $foot = '[/'.strtolower($this->tagName).']';

	$childStr = '';
	foreach ($this->children as $e)
	{
		if ($e->type == Node::UE_TEXT)
			$childStr .= $e->process(Tag::PROC_RAW);
		else
			$childStr .= $e->process(HtmlTag::PROC_ASUBB);
	}
	return $head.$childStr.$foot;
}

}

class HtmlTagSpan extends HtmlTag
{
protected function asUbbProc()
{
	$childStr = $this->childProcess(HtmlTag::PROC_ASUBB);

	$styles = array();
	$styleStr = $this->getAttr('style');
	if ($styleStr)
	{
		$arr = split(';', $styleStr);
		foreach ($arr as $s)
		{
			if ($s !== '')
			{
				$a = split(':', trim($s));
				$styles[$a[0]] = $a[1];
			}
		}
	}
	$c = 0;
	$attrnames = array('font-family' => 'family', 'font-size' => 'size', 'color' => 'color', 'background' => 'bkcolor');

	$fontAttr = '';
	$singleTag = '';
	foreach ($styles as $k => $v)
	{
		if (strcasecmp($k, 'font-family') == 0)
		{
			$fontAttr .= ' '.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"';
			$singleTag = '['.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"]'.$childStr.'[/'.$attrnames[$k].']';
		}
		if (strcasecmp($k, 'font-size') == 0)
		{
			$fontAttr .= ' '.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"';
			$singleTag = '['.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"]'.$childStr.'[/'.$attrnames[$k].']';
		}
		if (strcasecmp($k, 'color') == 0)
		{
			$fontAttr .= ' '.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"';
			$singleTag = '['.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"]'.$childStr.'[/'.$attrnames[$k].']';
		}
		if (strcasecmp($k, 'background') == 0)
		{
			$fontAttr .= ' '.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"';
			$singleTag = '['.$attrnames[$k].'="'.addcslashes($v, Tag::ADDSLASHES).'"]'.$childStr.'[/'.$attrnames[$k].']';
		}

		if (isset($attrnames[$k])) $c++;
	}

	if ($c > 1)
	{
		return '[font'.$fontAttr.']'.$childStr.'[/font]';
	}
	else if ($c == 1)
	{
		return $singleTag;
	}

	return $childStr;
}

}

class HtmlTagA extends HtmlTag
{
protected function asUbbProc()
{
	if (($href = $this->getAttr('href')) !== null)
	{	// 链接
		return '[url'.($href !== '' ? '="'.addcslashes($href, Tag::ADDSLASHES).'"' : '').']'.$this->childProcess(HtmlTag::PROC_ASUBB).'[/url]';
	}
	else
	{	// 锚点, 是锚点的话, 则不处理, 直接返回子节点
		return $this->childProcess(HtmlTag::PROC_ASUBB);
	}
}

}

class HtmlTagImg extends HtmlOddTag
{
protected function asUbbProc()
{
	$attr = '';
	if ($this->getAttr('width') == $this->getAttr('height'))
	{
		if ($this->getAttr('width') != '')
		{
			$attr .= '="'.addcslashes($this->getAttr('width'), Tag::ADDSLASHES).'"';
		}
	}
	else
	{
		if ($this->getAttr('width') != '' && $this->getAttr('height') != '')
		{
			$attr .= '="'.addcslashes($this->getAttr('width').','.$this->getAttr('height'), Tag::ADDSLASHES).'"';
		}
		else if ($this->getAttr('width') != '')
		{
			$attr .= ' width="'.addcslashes($this->getAttr('width'), Tag::ADDSLASHES).'"';
		}
		else if ($this->getAttr('height') != '')
		{
			$attr .= ' height="'.addcslashes($this->getAttr('height'), Tag::ADDSLASHES).'"';
		}
	}
	if ($this->getAttr('alt') !== null && $this->getAttr('alt') !== '')
	{
		$attr .= ' alt="'.addcslashes($this->getAttr('alt'), Tag::ADDSLASHES).'"';
	}
	return '[img'.$attr.']'.$this->getAttr('src').'[/img]';
}

}

class HtmlTagBr extends HtmlOddTag
{
protected function asUbbProc()
{
	return "\r\n";
}
}

class HtmlTagP extends HtmlTag
{
protected function asUbbProc()
{
	return $this->childProcess(HtmlTag::PROC_ASUBB)."\r\n";
}
}

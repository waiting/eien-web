<?php
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_UBBTAG_CLASS', 'ubb/ubbtag.class.php');

/** UBB Tag Context */
class UbbContext extends TagContext
{
public function __construct()
{
	parent::__construct('[', ']', 'UbbTextNode', array(
		'color' => 'UbbTagColor',
		'bkcolor' => 'UbbTagBkColor',
		'size' => 'UbbTagSize',
		'family' => 'UbbTagFamily',
		'font' => 'UbbTagFont',
		'url' => 'UbbTagURL',
		'u' => 'UbbTag',
		'b' => 'UbbTag',
		'i' => 'UbbTag',
		'table' => 'UbbChildTextRawTag',
		'tr' => 'UbbChildTextRawTag',
		'td' => 'UbbTag',
		'th' => 'UbbTag',
		'img' => 'UbbTagImg',
	));
}
}

function ubb_proc($str, $procType = UbbTag::PROC_ASHTML)
{
	return tag_proc($str, new UbbContext(), $procType);
}

/*UBB文本节点*/
class UbbTextNode extends TextNode
{
public function process($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case UbbTag::PROC_ASHTML:
		$str = $this->value;
		$str = htmlspecialchars($str);
		$str = preg_replace_callback('/ {2,}/', create_function('$m', 'return str_replace(" ", "&nbsp;", $m[0]);'), $str);
		$str = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $str);
		$str = nl2br($str);
		return $str;
	}
	return parent::process($procType);
}

}

/**UBB标签*/
class UbbTag extends Tag
{
const PROC_ASHTML = 41;   // 转为html代码
public function __construct()
{
	parent::__construct();
	$this->odd = false;
}
protected function asHtmlProc()
{
	$attrStr = '';
	foreach ($this->attrs as $k=>$v)
	{
		$attrStr .= ' '.strtolower($k).'="'.addcslashes($v, Tag::ADDSLASHES).'"';
	}
	$head = '<'.strtolower($this->tagName).$attrStr;
	$head .= ($this->odd ? ' />' : '>');
	$foot = $this->odd ? '' : '</'.strtolower($this->tagName).'>';
	return $head.$this->childProcess(UbbTag::PROC_ASHTML).$foot;
}
public function process($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case UbbTag::PROC_ASHTML:
		return $this->asHtmlProc();
	}
	return parent::process($procType);
}

}

/** 单独的标签 */
class UbbOddTag extends UbbTag
{
public function __construct()
{
	parent::__construct();
	$this->odd = true;
}
}
/*	不处理直接子文本节点的标签
	这一类Tag不处理直接子文本节点，因为若处理，则造成输出结果错误。例如table,tbody,tr等，若处理直接子文本，则输入
	[table]
		[tr]
			[td]Hello[/td]
		[/tr]
	[/table]
	会被处理成
	<table><br /><tr><br /><td>Hello</td><br /></tr><br /></table>
	在某些浏览器下，table和tr中的br会影响换行，导致不美观。一些本不应该存在直接子文本的标签应放弃处理直接子文本。若放弃，则会处理成
	<table>
		<tr>
			<td>Hello</td>
		</tr>
	</table>
	浏览器下正确输出。
*/
class UbbChildTextRawTag extends UbbTag
{
protected function asHtmlProc()
{
	$attrStr = '';
	foreach ($this->attrs as $k=>$v)
	{
		$attrStr .= ' '.strtolower($k).'="'.addcslashes($v, Tag::ADDSLASHES).'"';
	}
	$head = '<'.strtolower($this->tagName).$attrStr;
	$head .= ($this->odd ? ' />' : '>');
	$foot = $this->odd ? '' : '</'.strtolower($this->tagName).'>';

	$childStr = '';
	foreach ($this->children as $e)
	{
		if ($e->type == Node::UE_TEXT)
			$childStr .= $e->process(Tag::PROC_RAW);
		else
			$childStr .= $e->process(UbbTag::PROC_ASHTML);
	}
	return $head.$childStr.$foot;
}

}


class UbbTagColor extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="color:'.$this->defAttr.';">'.$this->childProcess(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagBkColor extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="background:'.$this->defAttr.';">'.$this->childProcess(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagSize extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="font-size:'.$this->defAttr.';">'.$this->childProcess(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagFamily extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="font-family:'.$this->defAttr.';">'.$this->childProcess(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagFont extends UbbTag
{
protected function asHtmlProc()
{
	$result = '<span style="';
	if ($attr = $this->getAttr('family'))
	{
		if (preg_match('@[ \x{4e00}-\x{9fa5}]@u', $attr))
		{
			$result .= "font-family:'$attr';";
		}
		else
		{
			$result .= "font-family:$attr;";
		}
	}
	if ($attr = $this->getAttr('size'))
		$result .= "font-size:$attr;";
	if ($attr = $this->getAttr('color'))
		$result .= "color:$attr;";
	if ($attr = $this->getAttr('bkcolor'))
		$result .= "background:$attr;";
	$result .= '">'.$this->childProcess(UbbTag::PROC_ASHTML).'</span>';
	return $result;
}

}

class UbbTagURL extends UbbTag
{
protected function asHtmlProc()
{
	if ($this->defAttr != '')
	{
		return '<a rel="_blank" href="'.$this->defAttr.'">'.$this->childProcess(UbbTag::PROC_ASHTML).'</a>';
	}
	else
	{
		return '<a rel="_blank" href="'.$this->childProcess(Tag::PROC_STRIP).'">'.$this->childProcess(UbbTag::PROC_ASHTML).'</a>';
	}
}

}

class UbbTagImg extends UbbTag
{
protected function asHtmlProc()
{
	$imgPath = $this->childProcess(Tag::PROC_RAW);
	$width = $height = null;
	$alt = $this->getAttr('alt');
	if ($this->defAttr != '')
	{
		if (preg_match('@([^,]*),(.*)@', $this->defAttr, $m))
		{
			$width = (int)trim($m[1]);
			$height = (int)trim($m[2]);
		}
		else
		{
			$width = $height = (int)$this->defAttr;
		}
	}
	else
	{
		$width = $this->getAttr('width');
		$height = $this->getAttr('height');
	}

	return '<img'.
		($imgPath != '' ? ' src="'.$imgPath.'"' : '').
		($width !== null ? ' width="'.$width.'"' : '').
		($height !== null ? ' height="'.$height.'"' : '').
		' alt="'.$alt.'" />';
}

}


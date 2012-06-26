<?php
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_UBBTAG_CLASS', 1);

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

function ubb_proc($str, $procType = Tag::PROC_REPLACE)
{
	return tag_proc($str, new UbbContext(), $procType);
}

/*UBB文本节点*/
class UbbTextNode extends TextNode
{
public function asStr($procType = Tag::PROC_RAW)
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
	return parent::asStr($procType);
}

}

/**UBB标签*/
class UbbTag extends Tag
{
const PROC_ASHTML = 21;   // 转为html代码
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
	return $head.$this->childAsStr(UbbTag::PROC_ASHTML).$foot;
}
public function asStr($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case UbbTag::PROC_ASHTML:
		return $this->asHtmlProc();
	}
	return parent::asStr($procType);
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
/*不处理直接子文本节点的标签*/
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
			$childStr .= $e->asStr(Tag::PROC_RAW);
		else
			$childStr .= $e->asStr(UbbTag::PROC_ASHTML);
	}
	return $head.$childStr.$foot;
}

}


class UbbTagColor extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="color:'.$this->defAttr.';">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagBkColor extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="background:'.$this->defAttr.';">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagSize extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="font-size:'.$this->defAttr.';">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</span>';
}

}

class UbbTagFamily extends UbbTag
{
protected function asHtmlProc()
{
	return '<span style="font-family:'.$this->defAttr.';">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</span>';
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
	$result .= '">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</span>';
	return $result;
}

}

class UbbTagURL extends UbbTag
{
protected function asHtmlProc()
{
	if ($this->defAttr != '')
	{
		return '<a rel="_blank" href="'.$this->defAttr.'">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</a>';
	}
	else
	{
		return '<a rel="_blank" href="'.$this->childAsStr(Tag::PROC_STRIP).'">'.$this->childAsStr(UbbTag::PROC_ASHTML).'</a>';
	}
}

}

class UbbTagImg extends UbbTag
{
protected function asHtmlProc()
{
	$imgPath = $this->childAsStr(Tag::PROC_RAW);
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

?>
<?php
/**	模板页面引擎
	eien template engine
	@author WaiTing
	@version 0.1.0 */
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_TEMPLATE_CLASS', 'template/template.class.php');

/** 模板类 */
class Template
{
public $varcx;         # 变量场景
public $templateDir;   # 模板文件路径
public $compileDir;    # 编译路径
public $cacheDir;      # 缓存路径
public $ldelim = '{';
public $rdelim = '}';

public $cache = 0;     # 缓存控制, 0 禁止, 1 打开
public $lifeTime = 0;  # 缓存时间
public $mode;          # 执行模式选择
public function __construct($mode = TplTag::PROC_RESULT)
{
	$this->varcx = array();
	$this->mode = $mode;
}
public function assign($name, $val)
{
	$this->varcx[$name] = $val;
}
public function assignRef($name, &$refVal)
{
	$this->varcx[$name] = &$refVal;
}
/** 检查是否存在编译文件,以及是否需要重新编译
编译模板,返回编译后的文件名 */
public function compile($template)
{
	$cepName = file_name($template).'_'.base64_encode(file_name($template)).'.cep';
	// 模板最后修改时间
	$tplmtime = filemtime($this->templateDir.$template);
	$needc = false;
	if (file_exists($this->compileDir.$cepName))
	{
		// 编译文件修改时间
		$cepmtime = filemtime($this->compileDir.$cepName);
		// 模板有变动
		if ($cepmtime < $tplmtime)
		{
			$needc = true;
		}
	}
	else
	{
		$needc = true;
	}

	if ($needc)
	{
		$f = new File($this->templateDir.$template);
		$this->compileTplText($f->bufData, $cepName);
	}
	return $cepName;
}
/** 编译模板文本 */
public function compileTplText($tplText, $cepName)
{
	$pr = new TagParser(new TplContext($this));
	$code = $pr->parse($tplText)->process(TplTag::PROC_COMPILE);
	$cepFile = new File($this->compileDir.$cepName, 'w');
	$cepFile->puts($code);
	$cepFile->close();
}
/** 直接解释模板 */
public function result($template)
{
	$f = new File($this->templateDir.$template);
	return $this->resultTplText($f->bufData);
}
/** 直接解释模板文本 */
public function resultTplText($tplText)
{
	$pr = new TagParser(new TplContext($this));
	return $pr->parse($tplText)->process(TplTag::PROC_RESULT);
}
// 缓存机制
// 检查是否存在缓存,以及是否过期
public function hasCached($template, $cacheId = '', &$repName = null)
{
	$repName = file_name($template).'_'.base64_encode(file_name($template).$cacheId).'.rep';
	if (file_exists($this->cacheDir.$repName)) // 存在缓存文件
	{
		// 判断是否过期
		$repmtime = filemtime($this->cacheDir.$repName);
		if (time() > $repmtime + $this->lifeTime) // 已经过期
		{
			return false;
		}
	}
	else // 不存在缓存文件
	{
		return false;
	}
	return true;
}
public function display($template, $cacheId = '')
{

	if (!$this->hasCached($template, $cacheId, $repName))
	{
		if ($this->mode == TplTag::PROC_COMPILE)
		{
			$cepName = $this->compile($template);
			if ($this->cache)
			{
				$varcx = &$this->varcx;
				ob_start();
				include $this->compileDir.$cepName;
				$r = ob_get_clean();
				$repFile = new File($this->cacheDir.$repName, 'w');
				$repFile->puts($r);
				$repFile->close();
				echo $r;
			}
			else
			{
				$varcx = &$this->varcx;
				include $this->compileDir.$cepName;
			}
		}
		else // 直接执行
		{
			$r = $this->result($template);
			if ($this->cache)
			{
				$repFile = new File($this->cacheDir.$repName, 'w');
				$repFile->puts($r);
				$repFile->close();
				echo $r;
			}
			else
			{
				echo $r;
			}
		}
	}
	else
	{
		include $this->cacheDir.$repName;
	}
	
}
public function fetch($template, $cacheId = '')
{
	ob_start();
	$this->display($template, $cacheId);
	return ob_get_clean();
}

}

/******************* template Tag Context *******************/
class TplContext extends TagContext
{
	public $tpl;
	public function __construct(Template $tpl)
	{
		parent::__construct($tpl->ldelim, $tpl->rdelim, 'TextNode', array(
			'' => 'TplTagOutput', // 输出标签
			'if' => 'TplTagIf',
			'elseif' => 'TplOddTag',
			'else' => 'TplOddTag',
			'iif' => 'TplTagIIf',
			'for' => 'TplTagFor',
			'forelse' => 'TplOddTag',
			'loop' => 'TplTagLoop',
			'loopelse' => 'TplOddTag',
			'load' => 'TplTagLoad',
		));
		$this->tpl = $tpl;
	}
}

/*模板标签基类*/
class TplTag extends Tag
{
const PROC_COMPILE = 44;  // 编译模板
const PROC_RESULT = 45;   // 直接输出结果
public function __construct()
{
	parent::__construct();
	$this->odd = false;
}
public function process($procType = Tag::PROC_RAW)
{
	switch ($procType)
	{
	case TplTag::PROC_COMPILE:
		return $this->compileProc();
	case TplTag::PROC_RESULT:
		return $this->resultProc();
	}
	return parent::process($procType);
}
public function childProcessEx($procType = Tag::PROC_RAW, $stopType = Node::UE_ELEM, $stopTag = null, $incStopTag = false, $start = 0, &$pos = null)
{
	$s = '';
	$cnt = count($this->children);
	for ($pos = $start; $pos < $cnt; $pos++)
	{
		$e = $this->children[$pos];
		if ($stopType == $e->type && $stopTag !== null)
		{
			if ($stopType == Node::UE_ELEM)
			{
				if (is_array($stopTag))
				{
					if (array_search($e->tagName, $stopTag) !== false)
					{
						if ($incStopTag) $s .= $e->process($procType);
						break;
					}
				}
				else
				{
					if ($e->tagName == $stopTag)
					{
						if ($incStopTag) $s .= $e->process($procType);
						break;
					}
				}
			}
			else // UE_TEXT
			{
				if (is_array($stopTag))
				{
					if (array_search($e->value, $stopTag) !== false)
					{
						if ($incStopTag) $s .= $e->process($procType);
						break;
					}
				}
				else
				{
					if ($e->value == $stopTag)
					{
						if ($incStopTag) $s .= $e->process($procType);
						break;
					}
				}
			}
		}
		$s .= $e->process($procType);
	}
	return $s;
}
public function childProcessEx2($procType = Tag::PROC_RAW, $stopTag = null, $incStopTag = false, $start = 0)
{
	$s = '';
	$cnt = count($this->children);
	for ($pos = $start; $pos < $cnt; $pos++)
	{
		$e = $this->children[$pos];
		if ($stopTag !== null)
		{
			if (is_int($stopTag))
			{
				if ($pos == $stopTag)
				{
					if ($incStopTag) $s .= $e->process($procType);
					break;
				}
			}
			else
			{
				if ($e === $stopTag)
				{
					if ($incStopTag) $s .= $e->process($procType);
					break;
				}
			}
		}
		$s .= $e->process($procType);
	}
	return $s;
}
// 查找子节点
public function childFind($type = Node::UE_ELEM, $tag = null, $start = 0, &$pos = null, &$e = null)
{
	$cnt = count($this->children);
	for ($pos = $start; $pos < $cnt; $pos++)
	{
		$e = $this->children[$pos];
		if ($type == $e->type && $tag !== null)
		{
			if ($type == Node::UE_ELEM)
			{
				if (is_array($tag))
				{
					if (array_search($e->tagName, $tag) !== false)
					{
						return true;
					}
				}
				else
				{
					if ($e->tagName == $tag)
					{
						return true;
					}
				}
			}
			else // UE_TEXT
			{
				if (is_array($tag))
				{
					if (array_search($e->value, $tag) !== false)
					{
						return true;
					}
				}
				else
				{
					if ($e->value == $tag)
					{
						return true;
					}
				}
			}
		}
	}
	return false;
}
protected function compileProc()
{
	return '';
}
protected function resultProc()
{
	return '';
}
// 编译表达式 expression compile..
protected function ec($expr)
{
	// 在生成的PHP代码中$this代表关联的模板对象实例
	return preg_replace('@\\$([A-Za-z0-9_]+)@', '\\$varcx[\'$1\']', $expr);
}
// 执行表达式 expression execute..
protected function ee($expr)
{
	if (!$expr) return '';
	$varcx = &$this->tagcx->tpl->varcx;
	$code = $this->ec($expr);
	@eval("\$s = ($code);");
	return $s;
}
}

/* 独立模板标签基类 */
class TplOddTag extends TplTag
{
public function __construct()
{
	parent::__construct();
	$this->odd = true;
}
}

// 输出变量的标签
// {="variable" fmt="html,url,quote,source,json,base64,md5"}
class TplTagOutput extends TplOddTag
{
protected function compileProc()
{
	return '<?php echo '.$this->ec($this->defAttr).'; ?>';
}
protected function resultProc()
{
	return $this->ee($this->defAttr);
}
}

// if 标签
// {if="condition"}...{elseif="condition"}...{else}...{/if}
class TplTagIf extends TplTag
{
protected function compileProc()
{
	$defAttr = $this->defAttr === '' ? 'false' : $this->defAttr;
	$code = '<?php if ('.$this->ec($defAttr).'): ?>';
	foreach ($this->children as $child)
	{
		if ($child->type == Node::UE_ELEM)
		switch ($child->tagName)
		{
		case 'elseif':
			$defAttr = $child->defAttr === '' ? 'false' : $child->defAttr;
			$code .= '<?php elseif ('.$child->ec($defAttr).'): ?>';
			break;
		case 'else':
			$code .= '<?php else: ?>';
			break;
		default:
			$code .= $child->process(TplTag::PROC_COMPILE);
			break;
		}
		else // UE_TEXT
		{
			$code .= $child->process(TplTag::PROC_COMPILE);
		}
	}
	$code .= '<?php endif; ?>';
	return $code;
}
protected function resultProc()
{
	$res = '';
	$cnt = count($this->children);
	$i = 0;
	$e = null;
	if ($this->ee($this->defAttr))
	{
		$res .= $this->childProcessEx(TplTag::PROC_RESULT, Node::UE_ELEM, array('else', 'elseif'), false, $i, $i);
	}
	else // if 条件为假
	{
		while ($i < $cnt)
		{
			// 跳到elseif,else节点
			$b = $this->childFind(Node::UE_ELEM, array('else', 'elseif'), $i, $i, $e);
			//有分支
			if ($b)
			{
				switch ($e->tagName)
				{
				case 'elseif':
					$i++;
					if ($e->ee($e->defAttr === '' ? 'false' : $e->defAttr))
					{
						$res .= $this->childProcessEx(TplTag::PROC_RESULT, Node::UE_ELEM, array('else', 'elseif'), false, $i, $i);
						return $res;
					}
					break;
				case 'else':
					$i++;
					$res .= $this->childProcessEx(TplTag::PROC_RESULT, Node::UE_ELEM, array('else', 'elseif'), false, $i, $i);
					return $res;
					break;
				}
			}
		}
	}
	return $res;
}
}
// IIf标签 
// {iif="condition" y="yesValue" n="noValue" /}
class TplTagIIf extends TplOddTag
{
protected function compileProc()
{
	$defAttr = $this->defAttr === '' ? 'false' : $this->defAttr;
	$y = (($v = $this->getAttr('y')) === null || $v === '' ? "''": $v);
	$n = (($v = $this->getAttr('n')) === null || $v === '' ? "''": $v);
	$code = '';
	$code .= '<?php echo (('.$this->ec($defAttr).') ? '.$this->ec($y).' : '.$this->ec($n).'); ?>';
	return $code;
}
protected function resultProc()
{
	$defAttr = $this->defAttr === '' ? 'false' : $this->defAttr;
	$y = (($v = $this->getAttr('y')) === null || $v === '' ? "''": $v);
	$n = (($v = $this->getAttr('n')) === null || $v === '' ? "''": $v);
	return $this->ee($defAttr) ? $this->ee($y) : $this->ee($n);
}
}
// Loop标签,用于枚举数组值
// {loop="array variable" $key=$val}...{/loop}
// {loop="array variable" $val}...{/loop}
class TplTagLoop extends TplTag
{
protected function compileProc()
{
	// 判断是否需要获取键名
	$key = array_keys($this->attrs);
	$key = $key[0];
	if ($this->childFind(Node::UE_ELEM, 'loopelse', 0, $pos, $e))
	{
		$s = '<?php if (count('.$this->ec($this->defAttr).')): ?>';
		if ($this->attrs[$key] === '')
		{
			$s .= '<?php foreach ('.$this->ec($this->defAttr).' as '.$this->ec($key).'): ?>'.$this->childProcessEx2(TplTag::PROC_COMPILE, $e).'<?php endforeach; ?>';
		}
		else
		{
			$s .= '<?php foreach ('.$this->ec($this->defAttr).' as '.$this->ec($key).' => '.$this->ec($this->attrs[$key]).'): ?>'.$this->childProcessEx2(TplTag::PROC_COMPILE, $e).'<?php endforeach; ?>';
		}
		$s .= '<?php else: ?>';
		$s .= $this->childProcessEx2(TplTag::PROC_COMPILE, null, false, $pos + 1);
		$s .= '<?php endif; ?>';
		return $s;
	}
	else
	{
		if ($this->attrs[$key] === '')
		{
			return '<?php foreach ('.$this->ec($this->defAttr).' as '.$this->ec($key).'): ?>'.$this->childProcess(TplTag::PROC_COMPILE).'<?php endforeach; ?>';
		}
		else
		{
			return '<?php foreach ('.$this->ec($this->defAttr).' as '.$this->ec($key).' => '.$this->ec($this->attrs[$key]).'): ?>'.$this->childProcess(TplTag::PROC_COMPILE).'<?php endforeach; ?>';
		}
	}
}
protected function resultProc()
{
	// 判断是否需要获取键名
	$key = array_keys($this->attrs);
	$key = $key[0];
	$res = '';
	$varcx = &$this->tagcx->tpl->varcx;
	$arrName = substr($this->defAttr, 1);// 要遍历的数组名
	if ($this->childFind(Node::UE_ELEM, 'loopelse', 0, $pos, $e))
	{
		if (count($varcx[$arrName]))
		{
			if ($this->attrs[$key] === '')// 不需要键名
			{
				$name = substr($key, 1);
				foreach ($varcx[$arrName] as $varcx[$name])
				{
					$res .= $this->childProcessEx2(TplTag::PROC_RESULT, $e);
				}
			}
			else
			{
				$name = substr($key, 1);
				$valName = substr($this->attrs[$key], 1);
				foreach ($varcx[$arrName] as $varcx[$name] => $varcx[$valName])
				{
					$res .= $this->childProcessEx2(TplTag::PROC_RESULT, $e);
				}
			}
		}
		else
		{
			$res .= $this->childProcessEx2(TplTag::PROC_RESULT, null, false, $pos + 1);
		}
	}
	else // 没有 loopelse
	{
		if ($this->attrs[$key] === '')// 不需要键名
		{
			$name = substr($key, 1);
			foreach ($varcx[$arrName] as $varcx[$name])
			{
				$res .= $this->childProcess(TplTag::PROC_RESULT);
			}
		}
		else
		{
			$name = substr($key, 1);
			$valName = substr($this->attrs[$key], 1);
			foreach ($varcx[$arrName] as $varcx[$name] => $varcx[$valName])
			{
				$res .= $this->childProcess(TplTag::PROC_RESULT);
			}
		}
	}
	return $res;
}
}
// For标签,用于数字型循环
// {for var="迭代器" to="value" step="步长"}{/for}
class TplTagFor extends TplTag
{
protected function compileProc()
{
	$step = $this->hasAttr('step') ? $this->getAttr('step') : '1';
	$to = $this->getAttr('to');
	$varName = ''; // 迭代器
	$init = false; // 已初始化否
	if (($pos = strpos($this->attrs['var'], '=')) !== false) { // 有无等号
		$varName = substr($this->attrs['var'], 0, $pos);
		$init = true;
	} else {
		$varName = $this->attrs['var'];
		$init = false;
	}
	// 方向
	$d = $this->ee($step) > 0;
	if ($this->childFind(Node::UE_ELEM, 'forelse', 0, $pos, $e)) {
		$code = '<?php '.($init ? $this->ec($this->attrs['var']) : $this->ec($varName).'=0').'; if ('.$this->ec($varName).($d ? ' <= ' : ' >= ').$this->ec($to).'): ?>';
		$code .= '<?php for (; '.$this->ec($varName).($d ? ' <= ' : ' >= ').$this->ec($to).'; '.$this->ec($varName).' += '.$this->ec($step).'): ?>';
		$code .= $this->childProcessEx2(TplTag::PROC_COMPILE, $e);
		$code .= '<?php endfor; ?>';
		$code .= '<?php else: ?>';
		$code .= $this->childProcessEx2(TplTag::PROC_COMPILE, null, false, $pos + 1);
		$code .= '<?php endif; ?>';
		return $code;
	} else {
		$code = '<?php for ('.($init ? $this->ec($this->attrs['var']) : $this->ec($varName).'=0').'; '.$this->ec($varName).($d ? ' <= ' : ' >= ').$this->ec($to).'; '.$this->ec($varName).' += '.$this->ec($step).'): ?>';
		$code .= $this->childProcess(TplTag::PROC_COMPILE);
		$code .= '<?php endfor; ?>';
		return $code;
	}
}
protected function resultProc()
{
	$varcx = &$this->tagcx->tpl->varcx;
	$step = $this->hasAttr('step') ? (int)$this->ee($this->getAttr('step')) : 1;
	$to = (int)$this->ee($this->getAttr('to'));
	$varName = ''; // 迭代器
	$init = false; // 已初始化否
	if (($pos = strpos($this->attrs['var'], '=')) !== false) { // 有无等号
		$varName = substr($this->attrs['var'], 1, $pos - 1);
		$init = true;
		$this->ee($this->attrs['var']);
	} else {
		$varName = substr($this->attrs['var'], 1);
		$init = false;
		$varcx[$varName] = 0;
	}
	// 方向
	$d = $step > 0;
	$res = ''; // 结果
	if ($this->childFind(Node::UE_ELEM, 'forelse', 0, $pos, $e)) {
		if ($d ? $varcx[$varName] <= $to : $varcx[$varName] >= $to) {
			for (; ($d ? $varcx[$varName] <= $to : $varcx[$varName] >= $to); $varcx[$varName] += $step) {
				$res .= $this->childProcessEx2(TplTag::PROC_RESULT, $e);
			}
		} else {
			$res .= $this->childProcessEx2(TplTag::PROC_RESULT, null, false, $pos + 1);
		}
	} else {
		for (; ($d ? $varcx[$varName] <= $to : $varcx[$varName] >= $to); $varcx[$varName] += $step) {
			$res .= $this->childProcess(TplTag::PROC_RESULT);
		}
	}
	return $res;
}
}
// load标签,用于载入其他模板
// {load="模板文件"}
class TplTagLoad extends TplOddTag
{
protected function compileProc()
{
	return '<?php include $this->compileDir.$this->compile(\''.addcslashes($this->defAttr,"\\'").'\'); ?>';
}
protected function resultProc()
{
	return $this->tagcx->tpl->result($this->defAttr);
}
}

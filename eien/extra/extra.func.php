<?php
/** 一些额外辅助函数
 * @package Eien.Web.ExtraFunc
 * @author WaiTing
 * @version 1.1.0
 */
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_EXTRA_FUNC', 'extra/extra.func.php');

/** Extra Functions config */
class ExtraConfig
{
	public static $page_charset = 'UTF-8';
}

/** FlashShow,用于向网页中嵌入FLASH插件,并提供一些常用操作
 * 输出遵循XHTML1.0 */
function flash_show($mains, $params = array(), $flaVars = array())
{
	if (!is_array($mains))
	{
		return 'flash show 参数错误!';
	}
	$flash = isset($mains['flash']) ? $mains['flash'] : '';
	$cx = isset($mains['cx']) ? $mains['cx'] : 160;
	$cy = isset($mains['cy']) ? $mains['cy'] : 120;
	$id = isset($mains['id']) ? $mains['id'] : '';
	$other = isset($mains['other']) ? $mains['other'] : '';

	$params['movie'] = $flash;
	
	// 自定义参数
	// 透明
	if (isset($mains['transparent']))
	{
		if ($mains['transparent'])
		{
			$params['wmode'] = 'transparent';
		}
		else
		{
			$params['wmode'] = 'opaque';
		}
	}
	// 脚本访问权
	if (isset($mains['scriptAccess']))
	{
		$params['allowScriptAccess'] = $mains['scriptAccess'];
	}
	// 背景色
	if (isset($mains['bgcolor']))
	{
		$params['wmode'] = 'opaque';
		$params['bgcolor'] = $mains['bgcolor'];
	}


	$embed = '<embed' . ($id != '' ? ' name="' . $id . '"' : '') . ' src="' . $flash . '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="' . $cx . '" height="' . $cy . '" ';
	$s = '';
	$s .= '<object' . ($other !== '' ? ' '.$other : '') . ($id != '' ? ' id="' . $id . '"' : '') . ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="' . $cx . '" height="' . $cy . '">';

	$sFlaVars = '';
	$i = 0;
	foreach ($flaVars as $varname => $varvalue)
	{
		if ($i != 0)
		{
			$sFlaVars .= '&';
		}
		$sFlaVars .= $varname . '=' . $varvalue;
		$i++;
	}

	foreach ($params as $name => $value)
	{
		$s .= '<param name="' . $name . '" value="' . $value . '" />';
		$embed .= $name . '="' . $value . '" ';
	}

	if ($sFlaVars != '')
	{
		$s .= '<param name="FlashVars" value="' . $sFlaVars . '" />';
		$embed .= 'FlashVars="' . $sFlaVars . '" ';
	}

	$embed .= '/>';
	$s .= $embed;
	$s .= '</object>';

	return $s;
}

require_once 'mime.inc.php';
/**
 * get mime type
 */
function get_mime($filename, $ext = null)
{
	$flag = (strtolower(PHP_SHLIB_SUFFIX) == 'dll');
	$mime = 'application/octet-stream';
	if (function_exists('mime_content_type'))
	{
		if ($flag)
		{
			$php_exe_path = getenv('PHPRC').'\\php.exe';
			$cmd = "$php_exe_path -r \"echo mime_content_type('$filename');\"";
			$mime = `$cmd`;
		}
		else
		{
			$mime = mime_content_type($filename);
		}
	}
	else
	{
		global $mimetypes;
		if ($ext && isset($mimetypes[$ext]))
		{
			$mime = $mimetypes[$ext];
		}
	}

	return $mime;
}
/**	@brief 分页
	@param $addr string 地址串,{p}将会替换成页号
	@param $p int 页号
	@param $pc int 总页数
	@param $sc int 显示数 -1则全部显示 */
function split_page($addr, $p, $pc, $sc = -1, $first = '首页'/*'&lsaquo;&lsaquo;&lsaquo;'*/, $prev = '上页'/*'&lsaquo;&lsaquo;'*/, $next = '下页'/*'&rsaquo;&rsaquo;'*/, $last = '末页'/*'&rsaquo;&rsaquo;&rsaquo;'*/)
{
	$pages = array();
	if ($pc > 0)
	{
		array_push($pages, array('name' => $first, 'url' => str_replace('{p}', 1, $addr)));
		array_push($pages, array('name' => $prev, 'url' => str_replace('{p}', ($p > 1 ? $p - 1 : 1), $addr)));
		if ($sc > $pc)
		{
			for ($i = 0; $i < $pc; $i++)
			{
				array_push($pages, array('name' => $i + 1, 'url' => iifstr($i + 1 != $p, str_replace('{p}', $i + 1, $addr))));
			}
		}
		else
		{
			$prevCount = $prevCount2 = ceil($sc / 2) - 1;
			$nextCount2 = $sc - ($prevCount2 + 1);
			if ($prevCount > $p - 1)
			{
				$prevCount = $p - 1;
			}
			$nextCount = $sc - ($prevCount + 1);

			if ($nextCount > $pc - $p)
			{
				$nextCount = $pc - $p;
			}
			$prevCount = $sc - ($nextCount + 1);

			if ($p - $prevCount > 1)
			{
				array_push($pages, array(
					'name' => '...',
					'url' => str_replace('{p}', ($p - $prevCount - 1 - $nextCount2 < 1 ? 1 : $p - $prevCount - 1 - $nextCount2), $addr)
				));
			}

			$prevPages = array();
			$i = 0;
			$q = $p;
			while ($q > 1 && $i < $prevCount)
			{
				$q--;
				array_push($prevPages, array('name' => $q, 'url' => str_replace('{p}', $q, $addr)));
				$i++;
			}
			while ($pg = array_pop($prevPages))
			{
				array_push($pages, $pg);
			}

			array_push($pages, array('name' => $p, 'url' => ''));

			$i = 0;
			$q = $p;
			while ($q < $pc && $i < $nextCount)
			{
				$q++;
				array_push($pages, array('name' => $q, 'url' => str_replace('{p}', $q, $addr)));
				$i++;
			}

			if ($p + $nextCount < $pc)
			{
				array_push($pages, array(
					'name' => '...',
					'url' => str_replace('{p}', ($p + $nextCount + 1 + $prevCount2 > $pc ? $pc : $p + $nextCount + 1 + $prevCount2), $addr)
				));
			}
		}
		array_push($pages, array('name' => $next, 'url' => str_replace('{p}', ($p < $pc ? $p + 1 : $pc), $addr)));
		array_push($pages, array('name' => $last, 'url' => str_replace('{p}', $pc, $addr)));
	}

	return $pages;
}
/**
 * 获取当前文档的URL
 * @return string 返回当前文档的URL
 */
function get_url()
{
	//$URL=$_SERVER['PHP_SELF'].iifstr($_SERVER['QUERY_STRING']!='','?'.$_SERVER['QUERY_STRING']);
	return $_SERVER['REQUEST_URI'];
}
/**
 * 给定一个值，若真，则输出指定字符串，否则输出另外字符串
 * @param $b boolean
 * @param $v1
 * @param $v2
 */
function iif($b, $v1, $v2 = '')
{
	return $b ? $v1 : $v2;
}
/** 限字数(gbk) */
function limit_words($str, $n, $chn = true)
{
	$len = strlen($str);
	$retStr = '';
	$flag = 0;
	for ($i = 0; $i < $len; $i++)
	{
		if ($n == 0) break;
		$ch = ord($str[$i]);
		if ($ch & 0x80)
		{
			$retStr .= $str[$i].($i + 1 < $len ? $str[$i + 1] : '');
			$i++;
			$n--;
		}
		else
		{
			$flag++;
			$retStr .= $str[$i];
			if ($chn)
			{
				if ($flag == 2)
				{
					$n--;
					$flag = 0;
				}
			}
			else
			{
				$n--;
			}
		}
	}
	/*if (strlen($retStr) < $len)
	{
		$retStr .= '…';
	}*/
	return $retStr;
}
/** 处理文章中的HTML特殊字符
 * @param string $string 要处理的字符串
 * @param boolean $bIsHtml 若为true,则结果可包含html标签; 为false,则把html标签转化为&??;
 * @return string
 */
function str_html($string, $bIsHtml = false)
{
	if (!$bIsHtml)
	{
		$string = htmlspecialchars($string);
	}
	$string = str_replace(' ','&nbsp;',$string);
	$string = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$string);
	$string = nl2br($string);
	return $string;
}
/** 此函数将把PHP字符串转换成单行形式
 * 把换行转化为\n
 * @param string $string
 * @return string
 */
function str_source($string)
{
	return addcslashes($string,"\'\"\x0..\x19");
}
/** 从GET,POST,COOKIE获取字符串. ',",\,NULL
 * @param string $string
 * @param bool[optional] $haveSlashes true,结果包含\; false,结果祛除\
 * @return string
 */
function gpc($string, $haveSlashes = false)
{
	$magic_quotes_gpc = ini_get('magic_quotes_gpc');
	if($haveSlashes) return $magic_quotes_gpc ? $string : addslashes($string);
	return $magic_quotes_gpc ? stripslashes($string) : $string;
}

function arr_gpc($arr, $haveSlashes = false)
{
	$newarr = array();
	foreach ($arr as $k => $v)
	{
		if (is_array($v))
		{
			$newarr[$k] = arr_gpc($v, $haveSlashes);
		}
		else
		{
			$newarr[$k] = gpc($v, $haveSlashes);
		}
	}
	return $newarr;
}
/** 从脚本接收字符串
 * 由于脚本编码是UTF-8,所以需要转换
 * 此函数需要设置全局变量$page_charset,指定当前页面编码
 * @param string $str
 * @return string
 */
function str_from_script($str, $do_gpc = false, $haveSlashes = false)
{
	$page_charset = ExtraConfig::$page_charset;
	return $do_gpc ? gpc(iconv('UTF-8',"{$page_charset}//IGNORE",$str), $haveSlashes) : iconv('UTF-8',"{$page_charset}//IGNORE",$str);
}

/**
 * 从Script接受数据
 * 专门处理GET POST数组的
 * @param array $arr
 * @param bool[optional] $do_gpc
 * @param bool[optional] $haveSlashes
 * @return array
 */
function arr_from_script($arr, $do_gpc = false, $haveSlashes = false){
	$newarr = array();
	foreach($arr as $k => $v)
	{
		if (is_array($v))
		{
			$newarr[str_from_script($k, $do_gpc, $haveSlashes)] = arr_from_script($v, $do_gpc, $haveSlashes);
		}
		else
		{
			$newarr[str_from_script($k, $do_gpc, $haveSlashes)] = str_from_script($v, $do_gpc, $haveSlashes);
		}
	}
	return $newarr;
}
/**
 * 返回用户IP地址
 * @return string
 */
function ip()
{
	$ip = "Unknown";
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	else if (isset($_SERVER["HTTP_CLIENT_IP"]))
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	else if (isset($_SERVER["REMOTE_ADDR"]))
		$ip = $_SERVER["REMOTE_ADDR"];
	else if (getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	return $ip;
}
/** 输出字节单位 */
function bytes_unit($size)
{
	if ($size < 1024)
	{
		return $size . ' B';
	}
	elseif ($size < 1024 * 1024)
	{
		return ceil($size * 100 / 1024) / 100 . ' KB';
	}
	else
	{
		return ceil($size * 100 / (1024 * 1024)) / 100 . ' MB';
	}
}
// 加密
function site_encode($data)
{
	return base64_encode($data);
}
// 解密
function site_decode($encodedata)
{
	return base64_decode($encodedata);
}

/** 引发致命错误
function fatal_error($errstr)
{
	exit( '<strong>Fatal error</strong>: '.__METHOD__.'(): '.$errstr.' in <strong>'.__FILE__.'</strong> on line <strong>'.__LINE__.'</strong>');
}*/


<?php
/** HTTP相关类 HttpWebRequest, HttpHeaderProcessor, URLProcessor
 * @author WaiTing
 * @package Eien.Web.Http
 * @version 1.0.0 */

//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_HTTP_CLASS', 'http/http.class.php');

/**
 * HTTP头部处理器 */
class HttpHeaderProcessor
{
/**
 * @var string */
public $method = '', $url = '', $version = '';
/**
 * @var string */
public $statusCode = '', $statusStr = '';
/**
 * @var array */
public $header = array();
public function __construct($headerStr = null)
{
	$this->parse($headerStr);
}
public function parse($headerStr)
{
	if (preg_match_all('/^.+$/m', $headerStr, $arr))
	{
		$m = $arr[0];
		$flag = true;
		foreach ($m as $key => $val)
		{
			$val = trim($val);
			if ($val == '') continue;
			if ($flag) $this->parseStartLine($val);
			else if (preg_match('/(.*?)\s*:\s*(.*)/', $val, $s))
			{
				$headername = $s[1];
				$headervalue = $s[2];
				$this->header[$headername] = $headervalue;
			}
			$flag = false;
		}
	}
}
private function parseStartLine($startLine = null)
{
	$arr = array();
	$len = strlen($startLine);
	$nFields = 0;
	$temp = '';
	$flag = false;
	for ($i = 0; $i < $len; $i++)
	{
		$ch = $startLine[$i];
		if (($ch == " " || $ch == "\t") && $nFields < 2)
		{
			if ($flag)
			{
				$arr[$nFields] = $temp;
				$temp = '';
				$flag = false;
				$nFields++;
			}
		}
		else
		{
			$temp .= $ch;
			$flag = true;
		}
	}
	$arr[2] = $temp;
	//$arr = split(' ', $startLine);
	if (strstr($arr[0], 'HTTP'))  # 是响应头
	{
		$this->version = $arr[0];
		$this->statusCode = $arr[1];
		$this->statusStr = $arr[2];
	}
	else # 是请求头
	{
		$this->method = $arr[0];
		$this->url = $arr[1];
		$this->version = $arr[2];
	}
}
public function getStartLine()
{
	$startLine = '';
	if ($this->method != '')
	{
		$startLine .= $this->method.' '.($this->url).' '.$this->version;
	}
	else
	{
		$startLine .= $this->version.' '.$this->statusCode.' '.$this->statusStr;
	}
	return $startLine;
}
public function getHeaderStr()
{
	$headerStr = '';
	//$headerStr .= $this->getStartLine()."\r\n";
	foreach ($this->header as $headername => $headervalue)
	{
		$headerStr .= $headername.': '.$headervalue."\r\n";
	}
	//$headerStr .= "\r\n";
	return $headerStr;
}
public function getHeader($name)
{
	return isset($this->header[$name]) ? $this->header[$name] : null;
}
public function setHeader($name, $value)
{
	$this->header[$name] = $value;
}
public function delHeader($name)
{
	unset($this->header[$name]);
}
}

/**
 * URL处理器,URL的生成,解析
 * URL = "http:""//"host[":"port][path["?"query]] */
class URLProcessor
{
/**
 * @var string */
public $scheme, $host, $port, $path, $query, $fragment, $user, $pass;
/**
 * @var array */
public $queryItems = array(), $urlRes = array();
/**
 * @param string $url */
public function __construct($url = null)
{
	$this->parse($url);
}
/** 得到URL
 * @return string */
public function getUrl()
{
	return "$this->scheme://".($this->user ? $this->user.($this->pass ? ":$this->pass" : '').'@' : '')."$this->host".($this->port ? ":$this->port" : '')."$this->path".($this->query ? "?$this->query" : '').($this->fragment ? "#$this->fragment" : '');
}
/** 解析URL
 * @param string $url
 * @return array */
public function parse($url)
{
	$urlRes = parse_url($url);
	$this->scheme = isset($urlRes['scheme']) ? $urlRes['scheme'] : 'http';
	$this->host = isset($urlRes['host']) ? $urlRes['host'] : $_SERVER['SERVER_NAME'];
	$this->port = isset($urlRes['port']) ? $urlRes['port'] : null;
	$this->user = isset($urlRes['user']) ? $urlRes['user'] : null;
	$this->pass = isset($urlRes['pass']) ? $urlRes['pass'] : null;
	$this->path = isset($urlRes['path']) && $urlRes['path'] != '' ? $urlRes['path'] : '/';
	$this->query = isset($urlRes['query']) ? $urlRes['query'] : null;
	$this->fragment = isset($urlRes['fragment']) ? $urlRes['fragment'] : null;
	$this->urlRes = $urlRes;
	$this->queryItems = self::parseQuery($this->query);
	return $urlRes;
}
/** 组建URL
 * @param string $scheme
 * @param string $host
 * @param string $port
 * @param string $path
 * @param string $query
 * @param string $fragment
 * @param string $user
 * @param string $pass
 * @return string */
public function build($scheme, $host, $port, $path, $query, $fragment = null, $user = null, $pass = null)
{
	$this->scheme = $scheme;
	$this->host = $host;
	$this->port = $port;
	$this->user = $user;
	$this->pass = $pass;
	$this->path = $path;
	$this->query = $query;
	$this->fragment = $fragment;
	return $this->getUrl();
}
/** 生成QueryString
 * @param array $items
 * @return string */
public static function makeQuery($items)
{
	$query = '';
	$query = http_build_query($items);
	return $query;
}
/** 解析QueryString
 * @param string $queryStr
 * @return array */
public static function parseQuery($queryStr)
{
	$res = array();
	parse_str($queryStr, $res);
	return $res;
}

}

class HttpWebRequest
{
/**
 * @var HttpHeaderProcessor */
public $header = null;
/**
 * @var URLProcessor */
private $urlpc = null;
private $responseHeader = null;
private $responseBody = null;
public $requestStr = null;
public function __construct($method, $url)
{
	$this->header = new HttpHeaderProcessor();
	$this->header->method = strtoupper($method);

	$this->urlpc = new URLProcessor($url);
	$this->header->url = $this->urlpc->path.($this->urlpc->query != '' ? '?'.$this->urlpc->query : '');
	$this->header->version = 'HTTP/1.1';

	//$this->header->setHeader('Accept', 'image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*');
	$this->header->setHeader('Accept-Language', 'zh-cn');
	//$this->header->setHeader('Accept-Encoding', 'gzip, deflate');
	$this->header->setHeader('User-Agent', 'Eien.Web.HttpWebRequest v1.0.0');
	$this->header->setHeader('Host', $this->urlpc->host);
	//$this->header->setHeader('Connection', 'close');
	$this->header->setHeader('Cookie', $_SERVER['HTTP_COOKIE']);
}

/** 发送请求并接受响应 */
public function send($data = null)
{
	/*	HTTP context option listing
		method
		header
		user_agent
		content
		proxy
		request_fulluri
		max_redirects
		protocol_version
		timeout
		ignore_errors */
	$http_opts = array(
		'method' => $this->header->method,
	);

	$isPost = $this->header->method == 'POST';
	$postData = $data;
	if ($isPost)
	{
		$this->header->setHeader('Content-Type','application/x-www-form-urlencoded');

		$contentLen = 0;
		if (is_array($postData))
		{
			$postData = URLProcessor::makeQuery($data);
		}
		$http_opts['content'] = $postData;
		//$contentLen = strlen($postData);
		//$this->header->setHeader('Content-Length', $contentLen);
	}
	$http_opts['header'] = $this->header->getHeaderStr();

	$context = stream_context_create(array('http'=>$http_opts));
	return file_get_contents($this->urlpc->getUrl(), 0, $context);
}

}


?>
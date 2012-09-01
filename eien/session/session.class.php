<?php
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SESSION_CLASS', 'session/session.class.php');

/** Session配置 */
class SessionConfig
{
	public static $sessTable = ''; # Session数据表
	public static $sessWhere = 0;  # Session存在于哪: 1数据库,0独立文件
	public static $savePath;       # Session独立文件路径目录

	public static function _init()
	{
		self::$savePath = realpath(dirname(__FILE__).'/data');
	}
}
SessionConfig::_init();

/** Session类 提供session相关操作
 * @author WaiTing
 * @version 1.0.0 */
class Session
{
public static $sessName = '';      # session 名称,一般是PHPSESSID
public static $savePath = '';      # 存储路径,在数据库中即忽略此参数
public static $sessWhere = 0;      # 存储:1为数据库,0独立文件
public static $sessTable = '';     # 表名
public static $lifetimeMap = array(); # Session生命期 sessid=>lifetime
private static $execSessStart = true; # 防止多次创建 self 会话对象导致重复调用 session_start()

public static function id($sessid = null)
{
	if ($sessid === null)
	{
		return session_id();
	}
	else
	{
		return session_id($sessid);
	}
}
/**	@brief 设置客户端SESSID的COOKIE有效期.
	先 start session 才能调用此函数
 */
public static function sessidLifeTime($lifetime = null)
{
	if ($lifetime === null)
	{
		setcookie(session_name(), session_id());
	}
	else
	{
		setcookie(session_name(), session_id(), $lifetime + time());
	}
}

private $selfSession;      # 是否self session
private $sessID = null;
private $sessData = null;
private $sessWrite = false;

public function __construct($sessid = null)
{
	$sessidExists = '';
	if (array_key_exists(session_name(), $_COOKIE))
	{
		$sessidExists = $_COOKIE[session_name()];
	}
	// 是否启动self会话
	$this->selfSession = ($sessid == null || $sessid == $sessidExists);
	$this->sessID = $sessid;
	$this->sessData = array('sess' => array());

	if ($this->selfSession) // 启动self Session
	{
		if (self::$execSessStart) // 防止多次创建 self 会话对象导致重复调用 session_start()
		{
			session_start();
			self::$execSessStart = false;
		}
		$this->sessID = session_id();
		$this->sessData = &$_SESSION;
	}
	else // 载入别人的Session
	{
		$data = '';
		if (Session::$sessWhere == 1)
		{
			$data = ss_read_handler($this->sessID);
		}
		else
		{
			$data = file_get_contents(Session::$savePath.'sess_'.$this->sessID);
		}

		if ($data)
		{
			$sep_pos = strpos($data, '|');
			if ($sep_pos !== false)
				$data = substr($data, $sep_pos + 1);
			else
				$data = '';
			$data = unserialize($data);
			if ($data)
			{
				$this->sessData['sess'] = $data;
			}
		}

	}

}

public function __destruct()
{
	if (!$this->selfSession && $this->sessWrite)
	{
		if (Session::$sessWhere == 1)
		{
			ss_write_handler($this->sessID, 'sess|'.serialize($this->sessData['sess']));
		}
		else
		{
			file_put_contents(Session::$savePath.'sess_'.$this->sessID,  'sess|'.serialize($this->sessData['sess']));
		}
	}
}
public function getSessionID()
{
	return $this->sessID;
}
// session数据本身的生命期
public function setLifeTime($lifetime = null)
{
	if ($lifetime === null)
	{
		unset(Session::$lifetimeMap[$this->sessID]);
	}
	else
	{
		Session::$lifetimeMap[$this->sessID] = $lifetime;
	}
}

public function destroy()
{
	$b = false;
	if ($this->selfSession)
	{
		$b = session_destroy();
	}
	else
	{
		if (Session::$sessWhere == 1)
		{
			$b = ss_destroy_handler($this->sessID);
		}
		else
		{
			@unlink(Session::$savePath.'sess_'.$this->sessID);
		}
		$this->sessWrite = false;
	}
	return $b;
}

public function __set($name, $value)
{
	$this->sessData['sess'][$name] = $value;
	$this->sessWrite = true;
}

public function &__get($name)
{
	return $this->sessData['sess'][$name];
}

public function __isset($name)
{
	if (array_key_exists('sess', $this->sessData) == false) return false;
	return array_key_exists($name, $this->sessData['sess']);
}

public function __unset($name)
{
	unset($this->sessData['sess'][$name]);
}

}

Session::$sessTable = SessionConfig::$sessTable;
Session::$sessWhere = SessionConfig::$sessWhere;
Session::$savePath = SessionConfig::$savePath;
session_save_path(Session::$savePath); // 设置Session路径
if (Session::$sessWhere == 1)
{
	session_set_save_handler(
		'ss_open_handler', 
		'ss_close_handler', 
		'ss_read_handler', 
		'ss_write_handler', 
		'ss_destroy_handler', 
		'ss_gc_handler'
	);
}
/*
DROP TABLE IF EXISTS <=tname('sessions')>;
CREATE TABLE <=tname('sessions')> (
  sess_id char(32) NOT NULL,
  sess_text mediumtext NOT NULL,
  sess_expiry int(10) unsigned NOT NULL,
  sess_ip char(15) NOT NULL,
  sess_ctime int(10) unsigned NOT NULL,
  sess_atime int(10) unsigned NOT NULL,
  sess_aurl varchar(1024) NOT NULL default '',
  PRIMARY KEY  (sess_id)
) ENGINE=MyISAM DEFAULT CHARSET=<=config('db_charset')>;
*/
// ---------------- 以下为Session回调函数,存储为独立文件时忽略 -----------------
/** session open */
function ss_open_handler($savePath, $sessName)
{
	Session::$sessName = $sessName;
	Session::$savePath = $savePath;
	return true;
}
/** session close */
function ss_close_handler()
{
	return true;
}
/** session read */
function ss_read_handler($sessID)
{

	$rs = db::rs('SELECT sess_text FROM '.Session::$sessTable." WHERE sess_id='$sessID';", db::cnn(true));

	if ($rs->recordCount() != 0)
	{
		return $rs->fields(0);
	}
	return false;
}
/** session write */
function ss_write_handler($sessID, $text)
{
	$time = time();

	$lifetime = ini_get('session.gc_maxlifetime');
	if (array_key_exists($sessID, Session::$lifetimeMap))
	{
		$lifetime = Session::$lifetimeMap[$sessID];
	}
	$expiry = $time + $lifetime;

	$ip = ip();  # in extra_func.php
	// access url
	$aurl = get_url();

	$mdf = db::mdf(Session::$sessTable, db::cnn(true));
	$ret = $mdf->addNew(array(
		'sess_id' => $sessID,
		'sess_text' => $text,
		'sess_expiry' => $expiry,
		'sess_ip' => $ip,
		'sess_ctime' => $time,
		'sess_atime' => $time,
		'sess_aurl' => $aurl
	));
	if (!$ret)
	{
		$ret = $mdf->modify(array(
			'sess_text' => $text,
			'sess_expiry' => $expiry,
			'sess_ip' => $ip,
			'sess_atime' => $time,
			'sess_aurl' => $aurl
		), $sessID);
	}
	return $ret;
}
/** session destroy */
function ss_destroy_handler($sessID)
{
	$mdf = db::mdf(Session::$sessTable, db::cnn(true));
	return $mdf->delete($sessID);
}
/** session 垃圾收集清理 */
function ss_gc_handler($lifetime)
{
	$mdf = db::mdf(Session::$sessTable, db::cnn(true));
	return $mdf->deleteEx("WHERE sess_expiry < ".time());
}



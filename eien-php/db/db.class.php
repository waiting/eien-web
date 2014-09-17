<?php
/**	@brief eienWeb框架 数据库操作
	@author WaiTing
	@version 1.0.0 Beta */

define('EIEN_DB_CLASS', 'db/db.class.php');

/** 数据库配置 */
class DbConfig
{
	public static $db_type = 'mysql';
	public static $db_host = 'localhost';
	public static $db_user = 'root';
	public static $db_pwd = '';
	public static $db_name = '';
	public static $db_charset = 'utf8';
}

class db
{
	/**	@brief 普通文本转成SQL语句安全的文本,用做SQL语句的字符串.
		@param $str string 需要转码的字符串
		@return string
		@warning 如果没有连接MySQL数据库就使用此函数,可能会有问题! */
	public static function escape($str, $cnn = null)
	{
		$s = '';
		switch (strtolower(DbConfig::$db_type))
		{
		case 'mysql':
			$s = mysql_real_escape_string($str, ($cnn ? $cnn->mysql_cnn : self::cnn()->mysql_cnn));
			break;
		case 'sqlite':
			break;
		}
		return $s;
	}
	/**	@brief 创建数据库连接
		@param $type string DB Type.
		@return IDBConnection
	 */
	public static function cnn($new = false)
	{
		$cnn = null;
		switch (strtolower(DbConfig::$db_type))
		{
		case 'mysql':
			$cnn = $new ?
				(new MySQLConnection(
					DbConfig::$db_host,
					DbConfig::$db_user,
					DbConfig::$db_pwd,
					DbConfig::$db_name,
					DbConfig::$db_charset,
					DBCNN_PCONNECT
				))
				:
				MySQLConnection::cnn(
					DbConfig::$db_host,
					DbConfig::$db_user,
					DbConfig::$db_pwd,
					DbConfig::$db_name,
					DbConfig::$db_charset,
					DBCNN_PCONNECT
				);
			break;
		case 'sqlite':
			break;
		}
		return $cnn;
	}
	/**
		@return IDBModifier
	 */
	public static function mdf($tableName, $cnn = null)
	{
		$mdf = null;
		switch (strtolower(DbConfig::$db_type))
		{
		case 'mysql':
			$mdf = new MySQLModifier($tableName, ($cnn ? $cnn : self::cnn()));
			break;
		case 'sqlite':
			break;
		}
		return $mdf;
	}
	/**
		@return IDBRecordset
	 */
	public static function rs($source, $cnn = null)
	{
		$rs = null;
		switch (strtolower(DbConfig::$db_type))
		{
		case 'mysql':
			$rs = new MySQLRecordset($source, ($cnn ? $cnn : self::cnn()));
			break;
		case 'sqlite':
			break;
		}
		return $rs;
	}
	/**
		@return IDBTable
	 */
	public static function tbl($tableName, $cnn = null)
	{
		$tbl = null;
		switch (strtolower(DbConfig::$db_type))
		{
		case 'mysql':
			$tbl = new MySQLTable($tableName, ($cnn ? $cnn : self::cnn()));
			break;
		case 'sqlite':
			break;
		}
		return $tbl;
	}

}


/**	@brief 数据库连接接口
	@since ver 1.0.0 */
interface IDBConnection
{
	/**	@brief 连接数据库
		@return boolean */
	function connect();
	/**	@brief 连接数据库,如果参数相同,则使用先前有效的连接资源.
		@return boolean */
	function pconnect();
	/**	@brief 关闭连接
		@return boolean */
	function close();
	/**	@brief 选定要操作的数据库
		@param $database string 数据库名
		@return boolean 是否成功 */
	function selectDB($database);
	/**	@brief 设置连接校验字符集
		@param $charset string 字符集
		@pre 连接数据库之后
		@return boolean */
	function setLinkCharset($charset);
	/**	@brief 受影响的行数
		@pre 在改变数据库内容或结构之后
		@return int */
	function affectedRows();
	/**	@brief 创建一个库
		@param $database string 数据库名
		@return boolean */
	function createDB($database);
	/**	@brief 删除数据库
		@param $database string 数据库名
		@return boolean */
	function dropDB($database);
	/**	@brief 获得错误号
		@return int
		@retval 0 没有错误
		@retval 非0 出错,可用 \b error 查看错误信息 */
	function errno();
	/**	@brief 获得错误信息
		@return string
		@retval NULL 没有错误 */
	function error();
	/**	@brief 查询
		@param $sql string
		@return IDBResult */
	function query($sql);
	/**	@brief 直接查询
		@param $sql string
		@return resource */
	function directQuery($sql);
	/**	@brief 未建立缓冲区的查询

		这函数可以用来执行INSERT, UPDATE, DROP等等一些不需要查询数据的操作.
		@param $sql string
		@return resource */
	function unbufferedQuery($sql);
	/**	@brief 获得最后一次完成记录插入时的ID值.

		您可以用执行SQL里的LAST_INSERT_ID()代替此函数
		@return int */
	function insertID();
	/**	@brief 普通文本转成SQL语句安全的文本,用做SQL语句的字符串.
		@param $str string 需要换码的字符串
		@return string */
	function escape($str);
	/**	@brief 获得所有数据库的一个结果集
		@return IDBResult */
	function listDBs();
	/**	@brief 获得数据库里某表所有字段的一个结果集
		@param $table_name string
		@return IDBResult */
	function listFields($table_name);
	/**	@brief 获得数据库里所有表的一个结果集
		@return IDBResult */
	function listTables();
	/**	@brief 获得数据库表名的引用标记
		@return array 两个元素分别表示左右俩标记 */
	function tableQuotes();
}

/**	@brief 数据结果操作接口 */
interface IDBResult
{
	/**	@brief 数据记录定位
		@param $index int 0为第一条记录
		@return boolean */
	function dataSeek($index);
	/**	@brief 提取为数组 by index 或者 by fieldname

		\c $resultType: \n
		\a MYSQL_ASSOC 则 by fieldname.\n
		\a MYSQL_NUM 则 by index.\n
		\a MYSQL_BOTH 则 by index 且 by fieldname.\n
		@param $resultType int
		@return array */
	function fetchArray($resultType = MYSQL_BOTH);
	/**	@brief 提取为数组(by fieldname) 
		@return array */
	function fetchAssoc();
	/**	@brief 作为数组(by index)提取 
		@return array */
	function fetchRow();
	/**	@brief 获取来自于结果集中一字段信息,并作为一个对象返回
		@param $fieldIndex int 索引数字,从0开始.
		@return object */
	function fetchField($fieldIndex = 0);
	/**	@brief 得到数据库名
		@param $index int 索引数字,从0开始.
		@return string */
	function dbName($index);
	/**	@brief Get the length of each output in a result
		@return array */
	function fetchLengths();
	/**	@brief 提取为对象
		@param $class_name string 类名
		@param $params array 传入其构造函数的参数
		@return object */
	function fetchObject($class_name = null, $params = null);
	/**	@brief Get the flags associated with the specified field in a result
		@param $fieldIndex int 字段索引
		@return string */
	function fieldFlags($fieldIndex);
	/**	@brief Returns the length of the specified field
		@param $fieldIndex int 字段索引
		@return int */
	function fieldLen($fieldIndex);
	/**	@brief Get the name of the specified field in a result
		@param $fieldIndex int 字段索引
		@return string */
	function fieldName($fieldIndex);
	/**	@brief Set result pointer to a specified field offset
		@param $fieldIndex int 字段索引
		@return boolean */
	function fieldSeek($fieldIndex);
	/**	@brief Get name of the table the specified field is in
		@param $fieldIndex int 字段索引
		@return string */
	function fieldTable($fieldIndex);
	/**	@brief Get the type of the specified field in a result
		@param $fieldIndex int 字段索引
		@return string */
	function fieldType($fieldIndex);
	/**	@brief 释放Result资源
		@return boolean */
	function freeResult();
	/**	@brief 获取结果里的字段数
		@return int */
	function fieldsCount();
	/**	@brief 获取结果里的记录数
		@return int */
	function rowsCount();
	/**	@brief 从结果集里获取一个单元格的内容
		@param $row int 记录索引
		@param $field int 字段索引
		@return string */
	function result($row, $field = 0);
	/**	@brief 获取表名
		@param $index int 索引
		@return string */
	function tableName($index);
}

/**	@brief 记录集接口 */
interface IDBRecordset
{
	/**	@brief 打开一个数据源
		@param $source mixed 可以是sql串,可以是IDBResult,或者是mysql result resource.
		@param $dbCnn 数据库连接接口.当 \c $source 为sql串时,必须指定此参数.
		@return int 此数据源的记录数 */
	function open($source, IDBConnection $dbCnn = null);
	/**	@brief 关闭
		@return boolean */
	function close();
	/**	@brief 移动到某条记录
		@param $index int */
	function move($index);
	/**	@brief 移动到第一条 */
	function moveFirst();
	/**	@brief 移动到最后一条 */
	function moveLast();
	/**	@brief 移动到上一条 */
	function movePrev();
	/**	@brief 移动到下一条 */
	function moveNext();
	/**	@brief 获取字段值
		@param $field mixed 字段名或是数字索引。如果是null，则返回全部字段作为一个数组
		@return string/array */
	function fields( $field = null );
	/**	@brief 获得记录数
		@return int */
	function recordCount();
	/**	@brief 是否到了最后
		@return boolean */
	function eof();
	/**	@brief 是否到了最前
		@return boolean */
	function bof();
}

/**	@brief 修改器接口
	@since ver 1.0.0 */
interface IDBModifier
{
	/**	@brief 添加新记录
		@param $fields array Key/Value数组. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@return boolean */
	function addNew($fields);
	/**	@brief 修改一条记录,用主键来指定数据记录
		@param $fields array Key/Value数组. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@param $prmValue mixed 主键值
		@return boolean */
	function modify($fields, $prmValue);
	/**	@brief 修改记录,用where子句来指定数据记录
		@param $fields array Key/Value数组. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@param $where string SQL语句的WHERE子句. \c $where 必须含有WHERE字符串.
		@return boolean */
	function modifyEx($fields, $where);
	/**	@brief 删除一条记录,用主键来指定数据记录
		@param $prmValue mixed 主键值
		@return int 删除的记录数 */
	function delete($prmValue);
	/**	@brief 删除一条记录,用where子句来指定数据记录
		@param $where string SQL语句的WHERE子句. \c $where 必须含有WHERE字符串.
		@return int 删除的记录数 */
	function deleteEx($where);
}

/**	@brief 表接口 */
interface IDBTable
{
	/**	@brief 载入一条记录
		@param $prmValue mixed 主键值
		@param $fields string 要载入的字段名称,半角逗号分隔,*表示全部
		@param $after string SQL语句where子句之后的部分,比如ORDER BY, GROUP BY, LIMIT等等.
		@return int 载入的记录数 */
	function loadRecord($prmValue, $fields = '*', $after = '');
	/**	@brief 载入记录
		@param $where string SQL语句的where子句部分,可以不包含WHERE字符串
		@param $fields string 要载入的字段名称,半角逗号分隔,*表示全部
		@param $after string SQL语句where子句之后的部分,比如ORDER BY, GROUP BY, LIMIT等等.
		@return int 载入的记录数 */
	function loadRecordEx($where, $fields = '*', $after = '');
	/**	@brief 载入记录v2
		@param $fields string 要载入的字段名称,半角逗号分隔,*表示全部
		@param $join string SQL语句的表连接部分,如Left Join `tbl1`
		@param $where string SQL语句的where子句部分,可以不包含WHERE字符串
		@param $after string SQL语句where子句之后的部分,比如ORDER BY, GROUP BY, LIMIT等等.
		@return int 载入的记录数 */
	function loadRecordEx2($fields = '*', $join = '', $where = '', $after = '');
	/**	@brief 获取字段值
		@param $field mixed 字段名,或是数字索引
		@return string */
	function getField($field);
	/**	@brief 设置字段值
		@param $fields array Key/Value数组. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@pre loadRecord**()系列函数先被调用,且返回非0值.
		@warning 要使用此函数,载入记录时必须保证主键字段被载入.\n
		即loadRecord**()系列函数的 \c $fields 必须有主键字段名,或为*. */
	function setFields($fields);
	/**	@brief 下一条记录
		@return boolean */
	function next();
	/**	@brief 获取 IDBRecordset 接口
		@return IDBRecordset */
	function rs();
	/**	@brief 获取 IDBModifier 接口
		@return IDBModifier */
	function mdf();
}


//! 不连接
define('DBCNN_NOCONNECT',0);
//! 连接
define('DBCNN_CONNECT',1);
//! MySQL级别的,相同参数的连接,直接使用先前的有效连接
define('DBCNN_PCONNECT',2);

/**	@brief MYSQL连接类 */
class MySQLConnection implements IDBConnection
{
public $mysql_cnn = null;
private $db_host = null;
private $db_user = null;
private $db_pwd = null;
private $db_name = null;
private $linkcharset = null;
private $connectType = 0;
private $autoClose = true;

private static $existCnn = null;
/**	@brief 获取存在的DBCnn */
public static function cnn( $db_host = null, $db_user = null, $db_pwd = null, $db_name = null, $linkcharset = null, $connectType = DBCNN_PCONNECT )
{
	if ( !is_object(self::$existCnn) )
	{
		if ( $db_host === null || $db_user === null || $db_pwd === null || $db_name === null || $linkcharset === null )
			exit( '<strong>Fatal error</strong>: '.__METHOD__.'(): '.'参数不正确，无法构造数据连接对象'.' in <strong>'.__FILE__.'</strong> on line <strong>'.__LINE__.'</strong>');

		self::$existCnn = new MySQLConnection( $db_host, $db_user, $db_pwd, $db_name, $linkcharset, $connectType );
	}
	return self::$existCnn;
}

/**	@brief 从MYSQL连接资源句柄获得 MySQLConnection 对象
	@param $mysql_cnn resource MYSQL连接资源句柄
	@param $autoClose boolean 是否自动关闭. 若从MYSQL连接资源句柄获得 MySQLConnection 对象, 则必须为false.
	@return MySQLConnection */
public static function from($mysql_cnn, $autoClose = false)
{
	$mc = new MySQLConnection(null, null, null, null, null, DBCNN_NOCONNECT);
	$mc->mysql_cnn = $mysql_cnn;
	$mc->autoClose = $autoClose;
	return $mc;
}
/**	@brief MySQLConnection构造函数
	@param $db_host string 数据库主机
	@param $db_user string 用户
	@param $db_pwd string 密码
	@param $db_name string 数据库名
	@param $linkcharset string 校验字符集
	@param $connectType int 连接类型 */
public function __construct( $db_host, $db_user, $db_pwd, $db_name, $linkcharset, $connectType = DBCNN_PCONNECT )
{
	$this->db_host = $db_host;
	$this->db_user = $db_user;
	$this->db_pwd = $db_pwd;
	$this->db_name = $db_name;
	$this->linkcharset = $linkcharset;
	$this->connectType = $connectType;
	switch ($this->connectType)
	{
	case DBCNN_CONNECT:
		$this->connect();
		break;
	case DBCNN_PCONNECT:
		$this->pconnect();
		break;
	}
}
public function __destruct()
{
	if ($this->autoClose)
		$this->close();
}
public function connect()
{
	if ($this->autoClose)
		$this->close();
	$this->mysql_cnn = mysql_connect($this->db_host,$this->db_user,$this->db_pwd);
	if (!$this->errno())
	{
		if ($this->db_name) $this->selectDB($this->db_name);
		if (!$this->errno())
		{
			if ($this->linkcharset) $this->setLinkCharset($this->linkcharset);
		}
	}
	if ($this->errno())
	{
		$this->close();
		return false;
	}
	$this->autoClose = true;
	return $this->mysql_cnn;
}
public function pconnect()
{
	if ($this->autoClose)
		$this->close();
	
	if ( !function_exists('mysql_pconnect') )
		return $this->connect();

	$this->mysql_cnn = mysql_pconnect($this->db_host,$this->db_user,$this->db_pwd);

	if (!$this->errno())
	{
		if ($this->db_name) $this->selectDB($this->db_name);
		if (!$this->errno())
		{
			if ($this->linkcharset) $this->setLinkCharset($this->linkcharset);
		}
	}
	if ($this->errno())
	{
		$this->close();
		return false;
	}
	$this->autoClose = true;
	return $this->mysql_cnn;
}
public function close()
{
	if ($this->mysql_cnn)
	{
		$ret = mysql_close($this->mysql_cnn);
		$this->mysql_cnn = null;
		return $ret;
	}
	return false;
}
public function selectDB($database)
{
	if ($this->mysql_cnn)
	{
		$this->db_name = $database;
		return mysql_select_db($database,$this->mysql_cnn);
	}
	return false;
}
public function setLinkCharset($charset)
{
	$ret = false;
	if ($this->mysql_cnn)
	{
		$ret = $this->directQuery('SET NAMES '.$this->linkcharset.';');
	}
	return $ret;
}

public function affectedRows()
{
	if ($this->mysql_cnn)
	{
		return mysql_affected_rows($this->mysql_cnn);
	}
	return false;
}
public function createDB($database)
{
	if ($this->mysql_cnn)
	{
		return mysql_create_db($database, $this->mysql_cnn);
	}
	return false;
}
public function dropDB($database)
{
	if ($this->mysql_cnn)
	{
		return mysql_drop_db($database, $this->mysql_cnn);
	}
	return false;
}
public function errno()
{
	if ($this->mysql_cnn)
	{
		return mysql_errno($this->mysql_cnn);
	}
	return false;
}
public function error()
{
	if ($this->mysql_cnn)
	{
		return mysql_error($this->mysql_cnn);
	}
	return false;
}
public function query($sql)
{
	if ($this->mysql_cnn)
	{
		return new MySQLResult($this->directQuery($sql));
	}
	return false;
}
public function directQuery($sql)
{
	//if (function_exists('debugLog')) debugLog($sql);
	if ($this->mysql_cnn)
	{
		$ret = mysql_query($sql, $this->mysql_cnn);
		if ( $this->error() )
		{
			//echo  $this->error() . '<br />' . $sql . '<br />';
			return false;
		}
		return $ret;
	}
	return false;
}
public function unbufferedQuery($sql)
{
	if ($this->mysql_cnn)
	{
		return mysql_unbuffered_query($sql, $this->mysql_cnn);
	}
	return false;
}
public function insertID()
{
	if ($this->mysql_cnn)
	{
		return mysql_insert_id($this->mysql_cnn);
	}
	return false;
}
public function escape($str)
{
	if ($this->mysql_cnn)
	{
		return mysql_real_escape_string($str, $this->mysql_cnn);
	}
	return false;
}
public function listDBs()
{
	if ($this->mysql_cnn)
	{
		return new MySQLResult(mysql_list_dbs($this->mysql_cnn));
	}
	return false;
}
public function listFields($table_name)
{
	if ($this->mysql_cnn)
	{
		return new MySQLResult(mysql_list_fields($this->db_name,$table_name,$this->mysql_cnn));
	}
	return false;
}
public function listTables()
{
	if ($this->mysql_cnn)
	{
		return new MySQLResult(mysql_list_tables($this->db_name, $this->mysql_cnn));
	}
	return false;
}
public function tableQuotes()
{
	return array( '`', '`' );
}

}

/**	@brief MySQL结果类 */
class MySQLResult implements IDBResult
{
public $_result = null;
/**	@brief 构造查询结果对象
	@param $source mixed 可以是结果资源:resource,或是查询串:string
	@param $dbCnn IDBConnection 数据库连接接口. 当source是查询串时,此参数不能为null */
public function __construct($source, IDBConnection $dbCnn = null)
{
	if (is_resource($source))
		$this->_result = $source;
	elseif (is_string($source))
	{
		$sql = $source;
		// 先判断是不是表名
		if (preg_match('/^table:(.+)/i', $source, $matches))
		{
			$sql = 'select * from '.$matches[1].';';
		}
		if ($dbCnn)
			$this->_result = $dbCnn->directQuery($sql);
	}
}
public function __destruct()
{
	$this->freeResult();
}
public function dataSeek($index)
{
	if ($this->_result)
	{
		return mysql_data_seek($this->_result, $index);
	}
	return false;
}
public function fetchArray($resultType = MYSQL_BOTH)
{
	if ($this->_result)
	{
		return mysql_fetch_array($this->_result, $resultType);
	}
	return false;
}
public function fetchAssoc()
{
	if ($this->_result)
	{
		return mysql_fetch_assoc($this->_result);
	}
	return false;
}
public function fetchRow()
{
	if ($this->_result)
	{
		return mysql_fetch_row($this->_result);
	}
	return false;
}
public function fetchField($fieldIndex = 0)
{
	if ($this->_result)
	{
		return mysql_fetch_field($this->_result,$fieldIndex);
	}
	return false;
}
public function dbName($index)
{
	if ($this->_result)
	{
		return mysql_db_name($this->_result,$index);
	}
	return false;
}
public function fetchLengths()
{
	if ($this->_result)
	{
		return mysql_fetch_lengths($this->_result);
	}
	return false;
}
public function fetchObject($class_name = null, $params = null)
{
	if ($this->_result)
	{
		if ($class_name)
			return mysql_fetch_object($this->_result,$class_name, $params);
		else
			return mysql_fetch_object($this->_result);
	}
	return false;
}
public function fieldFlags($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_flags($this->_result,$fieldIndex);
	}
	return false;
}
public function fieldLen($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_len($this->_result,$fieldIndex);
	}
	return false;
}
public function fieldName($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_name($this->_result,$fieldIndex);
	}
	return false;
}
public function fieldSeek($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_seek($this->_result,$fieldIndex);
	}
	return false;
}
public function fieldTable($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_table($this->_result,$fieldIndex);
	}
	return false;
}
public function fieldType($fieldIndex)
{
	if ($this->_result)
	{
		return mysql_field_type($this->_result,$fieldIndex);
	}
	return false;
}
public function freeResult()
{
	if ($this->_result)
	{
		$ret = mysql_free_result($this->_result);
		$this->_result = null;
		return $ret;
	}
	return false;
}
public function fieldsCount()
{
	if ($this->_result)
	{
		return mysql_num_fields($this->_result);
	}
	return false;
}
public function rowsCount()
{
	if ($this->_result)
	{
		return mysql_num_rows($this->_result);
	}
	return false;
}
public function result($row, $field = 0)
{
	if ($this->_result)
	{
		return mysql_result($this->_result, $row, $field);
	}
	return false;
}
public function tableName($index)
{
	if ($this->_result)
	{
		return mysql_tablename($this->_result, $index);
	}
	return false;
}

}

/**	@brief MySQLRecordset记录集类 */
class MySQLRecordset implements IDBRecordset
{
public $dbCnn = null;          # 连接
public $dbResult = null;       # 数据结果对象
public $_fields = array();      # 字段数组
public $_recordCount = 0;
private $curIndex = 0;
/**	@brief MySQLRecordset 构造函数
	@param $source mixed 可以是sql串,可以是MySQLResult,或者是mysql result resource.
	@param $dbCnn IDBConnection 数据库连接接口.当 \c $source 为sql串时,必须指定此参数. */
public function __construct($source = null, IDBConnection $dbCnn = null)
{
	$this->open($source, $dbCnn);
}
public function __destruct()
{
	$this->close();
}
public function open($source, IDBConnection $dbCnn = null)
{
	$this->close();
	if ($dbCnn) $this->dbCnn = $dbCnn;
	if (is_object($source) && ($source instanceof IDBResult))
	{
		$this->dbResult = $source;
	}
	elseif (is_resource($source))
	{
		$this->dbResult = new MySQLResult($source);
	}
	elseif (is_string($source) && $this->dbCnn)
	{
		$this->dbResult = new MySQLResult($source, $this->dbCnn);
	}

	if ($this->recordCount())
		$this->moveFirst();
	return $this->_recordCount;
}
public function close()
{
	$ret = false;
	if ($this->dbResult)
	{
		$ret = $this->dbResult->freeResult();
		$this->dbResult = null;
		$this->_recordCount = 0;
	}
	return $ret;
}
public function move($index)
{
	$this->curIndex = $index;
	if ($index < $this->_recordCount)
		$this->dbResult->dataSeek($index);
	//$this->_fields = $this->dbResult->fetchAssoc();
	// 双索引
	$this->_fields = $this->dbResult->fetchArray();
}
public function moveFirst()
{
	$this->move(0);
}
public function moveLast()
{
	$this->move($this->_recordCount - 1);
}
public function movePrev()
{
	$this->move($this->curIndex - 1);
}
public function moveNext()
{
	$this->move($this->curIndex + 1);
}
public function fields( $field = null )
{
	if ( $field === null ) return $this->_fields;
	return $this->_fields[$field];
}
public function recordCount()
{
	if ($this->dbResult)
	{
		$this->_recordCount = $this->dbResult->rowsCount();
	}
	return $this->_recordCount;
}
public function eof()
{
	return $this->curIndex >= $this->_recordCount || $this->_recordCount == 0;
}
public function bof()
{
	return $this->curIndex <= -1 || $this->_recordCount == 0;
}

}

/**	@brief MySQL修改器类 */
class MySQLModifier implements IDBModifier
{
public static $tablesMap = array();
public $dbCnn = null;          # 连接
public $fieldNames = null;  # 字段名
public $tableName = '';     # 表名
public $prmKey = '';        # 主键名
public $prmAutoIncrement = false; #自动增长
/**	@brief MySQLModifier 构造函数
	@param $tableName string 数据表名
	@param $cnn IDBConnection 数据库连接接口 */
public function __construct($tableName, IDBConnection $cnn)
{
	$this->dbCnn = $cnn;
	$this->tableName = $tableName;
	$this->getTableInfo();
}
/**	@brief 获取表的一些信息,例如字段,主键 */
protected function getTableInfo()
{
	if (isset(MySQLModifier::$tablesMap[$this->tableName]))
	{
		$table = MySQLModifier::$tablesMap[$this->tableName];
		$this->prmKey = $table['prmKey'];
		$this->prmAutoIncrement = $table['autoIncrement'];
		$this->fieldNames = $table['fields'];
		return;
	}

	$sql = 'DESCRIBE '.$this->tableName.';';
	$result = new MySQLResult($sql, $this->dbCnn);
	$this->fieldNames = array();
	while ($row = $result->fetchRow())
	{
		array_push($this->fieldNames, $row[0]);
		if (preg_match('/PRI/i', $row[3]))
		{
			$this->prmKey = $row[0];
			// 判断主键是否自动增长
			if (preg_match('/auto_increment/i', $row[5]))
			{
				$this->prmAutoIncrement = true;
			}
		}
	}
	MySQLModifier::$tablesMap[$this->tableName] = array('prmKey'=>$this->prmKey, 'autoIncrement'=>$this->prmAutoIncrement, 'fields'=>$this->fieldNames);
}
/* 以下几个函数带有$fields参数,其设置方法如下:
$fields = array (
	'field_name' => field_value,
	……
);
field_value 是还未escape的值 */
public function addNew($fields)
{
	$flag = false;
	$fieldStr = '';
	$valueStr = '';
	foreach ($fields as $field => $value)
	{
		$field = is_numeric($field) ? $this->fieldNames[$field] : $field;
		if ($flag)
		{
			$fieldStr .= ',';
			$valueStr .= ',';
		}
		$fieldStr .= $field;
		$value = $this->dbCnn->escape($value);
		$valueStr .= "'$value'";

		$flag = true;
	}
	if ($valueStr == '') return false;
	$sql = "INSERT INTO ".$this->tableName." ($fieldStr) VALUES ($valueStr);";
	return $this->dbCnn->directQuery($sql);
}
public function modify($fields, $prmValue)
{
	$prmValue = $this->dbCnn->escape($prmValue);
	return $this->modifyEx($fields, 'WHERE '.$this->prmKey."='$prmValue'");
}
public function modifyEx($fields, $where)
{
	$flag = false;
	$itemsStr = '';
	foreach ($fields as $field => $value)
	{
		$field = is_numeric($field) ? $this->fieldNames[$field] : $field;
		if ($flag)
		{
			$itemsStr .= ',';
		}
		$value = $this->dbCnn->escape($value);
		$itemsStr .= "$field='$value'";
		$flag = true;
	}
	if ($itemsStr == '') return false;

	$sql = 'UPDATE '.$this->tableName." SET $itemsStr $where;";
	return $this->dbCnn->directQuery($sql);
}
public function delete($prmValue)
{
	$prmValue = $this->dbCnn->escape($prmValue);
	return $this->deleteEx('WHERE '.$this->prmKey."='$prmValue'");
}
public function deleteEx($where)
{
	$this->dbCnn->directQuery("DELETE FROM ".$this->tableName." $where;");
	$affectedCount = $this->dbCnn->affectedRows();
	if ($this->prmAutoIncrement)
	{
		// 获取最大ID值
		$res = $this->dbCnn->query("SELECT MAX($this->prmKey) FROM $this->tableName;");
		$maxID = $res->fetchRow();
		$maxID = $maxID[0];
		// 设置自动增长值
		$maxID++;
		$this->dbCnn->directQuery("ALTER TABLE $this->tableName AUTO_INCREMENT=$maxID;");
	}
	return $affectedCount;
}

}

/**	@brief MySQLTable数据表类 */
class MySQLTable implements IDBTable
{
private $_rs = null;
private $_mdf = null;
/**	@brief MySQLTable 构造函数
	@param $tableName string 数据表名
	@param $cnn IDBConnection 数据库连接接口 */
public function __construct($tableName, IDBConnection $cnn)
{
	$this->_rs = new MySQLRecordset(null, $cnn);
	$this->_mdf = new MySQLModifier($tableName, $cnn);
}
public function __destruct()
{
	$this->_rs = null;
	$this->_mdf = null;
}
public function loadRecord($prmValue, $fields = '*', $after = '')
{
	$prmValue = $this->_rs->dbCnn->escape($prmValue);
	return $this->loadRecordEx($this->_mdf->prmKey."='$prmValue'", $fields, $after);
}
public function loadRecordEx($where, $fields = '*', $after = '')
{
	return $this->loadRecordEx2($fields, '', $where, $after);
}
public function loadRecordEx2($fields = '*', $join = '', $where = '', $after = '')
{
	if (preg_match('/where(.*)/i', $where, $m))
	{
		$where = $m[1];
	}
	return $this->_rs->open("select $fields from ".$this->_mdf->tableName.($join == '' ? '' : " $join").($where == '' ? '' : " where $where")." $after;");
}
public function getField($field)
{
	return $this->_rs->fields($field);
}
public function setFields($fields)
{
	$prmValue = $this->_rs->fields($this->_mdf->prmKey);
	return $this->_mdf->modify($fields, $prmValue);
}
public function next()
{
	$this->_rs->moveNext();
	return !$this->_rs->eof();
}

public function rs()
{
	return $this->_rs;
}
public function mdf()
{
	return $this->_mdf;
}

}


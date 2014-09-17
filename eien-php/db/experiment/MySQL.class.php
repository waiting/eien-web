<?php
/**	MYSQL数据库驱动程序
	@author WT
	@version 1.0.0 */

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


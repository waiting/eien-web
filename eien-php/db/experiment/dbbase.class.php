<?php
/**	数据库基本操作接口
	@version 0.1.0
	@author WT
	@date 2012-10-01 */

/**	数据库连接接口
	@version 1.0.0 */
interface IDBConnection
{
	/**	@brief 连接数据库
		@return boolean */
	function connect();
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
		@retval 非0 出错,可用 \b error() 查看错误信息 */
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
	/**	@brief 得到数据库名
		@param $index int 索引数字,从0开始.
		@return string */
	function dbName($index);
	/**	获取一个结果集中指定字段相关的标记
		@param $fieldIndex int 字段索引
		@return string */
	function fieldFlags($fieldIndex);
	/**	@brief 返回结果集中指定字段的长度，这个长度是什么长度，还待试验。
		@param $fieldIndex int 字段索引
		@return int */
	function fieldLen($fieldIndex);
	/**	@brief 获取结果集中指定字段的名称
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
		@param $source mixed 可以是sql串,可以是IDBResult,或者直接是数据库本身API的结果集资源标识.
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

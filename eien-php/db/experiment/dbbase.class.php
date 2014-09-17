<?php
/**	���ݿ���������ӿ�
	@version 0.1.0
	@author WT
	@date 2012-10-01 */

/**	���ݿ����ӽӿ�
	@version 1.0.0 */
interface IDBConnection
{
	/**	@brief �������ݿ�
		@return boolean */
	function connect();
	/**	@brief �ر�����
		@return boolean */
	function close();
	/**	@brief ѡ��Ҫ���������ݿ�
		@param $database string ���ݿ���
		@return boolean �Ƿ�ɹ� */
	function selectDB($database);
	/**	@brief ��������У���ַ���
		@param $charset string �ַ���
		@pre �������ݿ�֮��
		@return boolean */
	function setLinkCharset($charset);
	/**	@brief ��Ӱ�������
		@pre �ڸı����ݿ����ݻ�ṹ֮��
		@return int */
	function affectedRows();
	/**	@brief ����һ����
		@param $database string ���ݿ���
		@return boolean */
	function createDB($database);
	/**	@brief ɾ�����ݿ�
		@param $database string ���ݿ���
		@return boolean */
	function dropDB($database);
	/**	@brief ��ô����
		@return int
		@retval 0 û�д���
		@retval ��0 ����,���� \b error() �鿴������Ϣ */
	function errno();
	/**	@brief ��ô�����Ϣ
		@return string
		@retval NULL û�д��� */
	function error();
	/**	@brief ��ѯ
		@param $sql string
		@return IDBResult */
	function query($sql);
	/**	@brief ֱ�Ӳ�ѯ
		@param $sql string
		@return resource */
	function directQuery($sql);
	/**	@brief δ�����������Ĳ�ѯ

		�⺯����������ִ��INSERT, UPDATE, DROP�ȵ�һЩ����Ҫ��ѯ���ݵĲ���.
		@param $sql string
		@return resource */
	function unbufferedQuery($sql);
	/**	@brief ������һ����ɼ�¼����ʱ��IDֵ.

		��������ִ��SQL���LAST_INSERT_ID()����˺���
		@return int */
	function insertID();
	/**	@brief ��ͨ�ı�ת��SQL��䰲ȫ���ı�,����SQL�����ַ���.
		@param $str string ��Ҫ������ַ���
		@return string */
	function escape($str);
	/**	@brief ����������ݿ��һ�������
		@return IDBResult */
	function listDBs();
	/**	@brief ������ݿ���ĳ�������ֶε�һ�������
		@param $table_name string
		@return IDBResult */
	function listFields($table_name);
	/**	@brief ������ݿ������б��һ�������
		@return IDBResult */
	function listTables();
	/**	@brief ������ݿ���������ñ��
		@return array ����Ԫ�طֱ��ʾ��������� */
	function tableQuotes();
}

/**	@brief ���ݽ�������ӿ� */
interface IDBResult
{
	/**	@brief ���ݼ�¼��λ
		@param $index int 0Ϊ��һ����¼
		@return boolean */
	function dataSeek($index);
	/**	@brief ��ȡΪ���� by index ���� by fieldname

		\c $resultType: \n
		\a MYSQL_ASSOC �� by fieldname.\n
		\a MYSQL_NUM �� by index.\n
		\a MYSQL_BOTH �� by index �� by fieldname.\n
		@param $resultType int
		@return array */
	function fetchArray($resultType = MYSQL_BOTH);
	/**	@brief ��ȡΪ����(by fieldname) 
		@return array */
	function fetchAssoc();
	/**	@brief ��Ϊ����(by index)��ȡ 
		@return array */
	function fetchRow();
	/**	@brief �õ����ݿ���
		@param $index int ��������,��0��ʼ.
		@return string */
	function dbName($index);
	/**	��ȡһ���������ָ���ֶ���صı��
		@param $fieldIndex int �ֶ�����
		@return string */
	function fieldFlags($fieldIndex);
	/**	@brief ���ؽ������ָ���ֶεĳ��ȣ����������ʲô���ȣ��������顣
		@param $fieldIndex int �ֶ�����
		@return int */
	function fieldLen($fieldIndex);
	/**	@brief ��ȡ�������ָ���ֶε�����
		@param $fieldIndex int �ֶ�����
		@return string */
	function fieldName($fieldIndex);
	/**	@brief Set result pointer to a specified field offset
		@param $fieldIndex int �ֶ�����
		@return boolean */
	function fieldSeek($fieldIndex);
	/**	@brief Get name of the table the specified field is in
		@param $fieldIndex int �ֶ�����
		@return string */
	function fieldTable($fieldIndex);
	/**	@brief Get the type of the specified field in a result
		@param $fieldIndex int �ֶ�����
		@return string */
	function fieldType($fieldIndex);
	/**	@brief �ͷ�Result��Դ
		@return boolean */
	function freeResult();
	/**	@brief ��ȡ�������ֶ���
		@return int */
	function fieldsCount();
	/**	@brief ��ȡ�����ļ�¼��
		@return int */
	function rowsCount();
	/**	@brief �ӽ�������ȡһ����Ԫ�������
		@param $row int ��¼����
		@param $field int �ֶ�����
		@return string */
	function result($row, $field = 0);
	/**	@brief ��ȡ����
		@param $index int ����
		@return string */
	function tableName($index);
}

/**	@brief ��¼���ӿ� */
interface IDBRecordset
{
	/**	@brief ��һ������Դ
		@param $source mixed ������sql��,������IDBResult,����ֱ�������ݿⱾ��API�Ľ������Դ��ʶ.
		@param $dbCnn ���ݿ����ӽӿ�.�� \c $source Ϊsql��ʱ,����ָ���˲���.
		@return int ������Դ�ļ�¼�� */
	function open($source, IDBConnection $dbCnn = null);
	/**	@brief �ر�
		@return boolean */
	function close();
	/**	@brief �ƶ���ĳ����¼
		@param $index int */
	function move($index);
	/**	@brief �ƶ�����һ�� */
	function moveFirst();
	/**	@brief �ƶ������һ�� */
	function moveLast();
	/**	@brief �ƶ�����һ�� */
	function movePrev();
	/**	@brief �ƶ�����һ�� */
	function moveNext();
	/**	@brief ��ȡ�ֶ�ֵ
		@param $field mixed �ֶ����������������������null���򷵻�ȫ���ֶ���Ϊһ������
		@return string/array */
	function fields( $field = null );
	/**	@brief ��ü�¼��
		@return int */
	function recordCount();
	/**	@brief �Ƿ������
		@return boolean */
	function eof();
	/**	@brief �Ƿ�����ǰ
		@return boolean */
	function bof();
}

/**	@brief �޸����ӿ�
	@since ver 1.0.0 */
interface IDBModifier
{
	/**	@brief ����¼�¼
		@param $fields array Key/Value����. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@return boolean */
	function addNew($fields);
	/**	@brief �޸�һ����¼,��������ָ�����ݼ�¼
		@param $fields array Key/Value����. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@param $prmValue mixed ����ֵ
		@return boolean */
	function modify($fields, $prmValue);
	/**	@brief �޸ļ�¼,��where�Ӿ���ָ�����ݼ�¼
		@param $fields array Key/Value����. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@param $where string SQL����WHERE�Ӿ�. \c $where ���뺬��WHERE�ַ���.
		@return boolean */
	function modifyEx($fields, $where);
	/**	@brief ɾ��һ����¼,��������ָ�����ݼ�¼
		@param $prmValue mixed ����ֵ
		@return int ɾ���ļ�¼�� */
	function delete($prmValue);
	/**	@brief ɾ��һ����¼,��where�Ӿ���ָ�����ݼ�¼
		@param $where string SQL����WHERE�Ӿ�. \c $where ���뺬��WHERE�ַ���.
		@return int ɾ���ļ�¼�� */
	function deleteEx($where);
}

/**	@brief ��ӿ� */
interface IDBTable
{
	/**	@brief ����һ����¼
		@param $prmValue mixed ����ֵ
		@param $fields string Ҫ������ֶ�����,��Ƕ��ŷָ�,*��ʾȫ��
		@param $after string SQL���where�Ӿ�֮��Ĳ���,����ORDER BY, GROUP BY, LIMIT�ȵ�.
		@return int ����ļ�¼�� */
	function loadRecord($prmValue, $fields = '*', $after = '');
	/**	@brief �����¼
		@param $where string SQL����where�Ӿ䲿��,���Բ�����WHERE�ַ���
		@param $fields string Ҫ������ֶ�����,��Ƕ��ŷָ�,*��ʾȫ��
		@param $after string SQL���where�Ӿ�֮��Ĳ���,����ORDER BY, GROUP BY, LIMIT�ȵ�.
		@return int ����ļ�¼�� */
	function loadRecordEx($where, $fields = '*', $after = '');
	/**	@brief �����¼v2
		@param $fields string Ҫ������ֶ�����,��Ƕ��ŷָ�,*��ʾȫ��
		@param $join string SQL���ı����Ӳ���,��Left Join `tbl1`
		@param $where string SQL����where�Ӿ䲿��,���Բ�����WHERE�ַ���
		@param $after string SQL���where�Ӿ�֮��Ĳ���,����ORDER BY, GROUP BY, LIMIT�ȵ�.
		@return int ����ļ�¼�� */
	function loadRecordEx2($fields = '*', $join = '', $where = '', $after = '');
	/**	@brief ��ȡ�ֶ�ֵ
		@param $field mixed �ֶ���,������������
		@return string */
	function getField($field);
	/**	@brief �����ֶ�ֵ
		@param $fields array Key/Value����. array(fieldName => fieldValue, fieldName => fieldValue, ...);
		@pre loadRecord**()ϵ�к����ȱ�����,�ҷ��ط�0ֵ.
		@warning Ҫʹ�ô˺���,�����¼ʱ���뱣֤�����ֶα�����.\n
		��loadRecord**()ϵ�к����� \c $fields �����������ֶ���,��Ϊ*. */
	function setFields($fields);
	/**	@brief ��һ����¼
		@return boolean */
	function next();
	/**	@brief ��ȡ IDBRecordset �ӿ�
		@return IDBRecordset */
	function rs();
	/**	@brief ��ȡ IDBModifier �ӿ�
		@return IDBModifier */
	function mdf();
}

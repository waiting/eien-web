<?
require_once 'db.class.php';

$cnn = new MySQLConnection("localhost", "user", "password", "dbname", "����(gbk/utf8)");

// ����Դ��ѯ
// ��1ȫ���ֶ�ȫ������
$rs = db::rs('table:��1');
// ��sql
$rs = db::rs('select * from ��1 where uid=111;');

// ��������
while ( !$rs->eof() ) 
{
	echo $rs->fields('�ֶ�1');
	echo $rs->fields('�ֶ�2');
	$rs->moveNext();
}


// �޸���������ֻ�ܵ����޸�
$mdf = db::mdf("��1");

// ���
$mdf->addNew( array( '�ֶ�1' => ����, '�ֶ�2' => ���� ) );

// �޸�
$mdf->modify(  array( '�ֶ�1' => ����, '�ֶ�2' => ���� ), ����ֵ );
$mdf->modifyEx(  array( '�ֶ�1' => ����, '�ֶ�2' => ���� ), "where uid=111" );

// ɾ��
$mdf->delete(����ֵ);
$mdf->deleteEx("where uid=111");

// ������ĵ���ӿڣ��ʺ϶�ȡ֮������Ҫ�޸ĵĲ���
$tbl = db::tbl("��1");
if ($tbl->loadRecordEx("where uid>111")) do
{
	echo $tbl->getFields('�ֶ�1');
	echo $tbl->getFields('�ֶ�2');
	// �������1000������0
	if ( $tbl->getFields('�ֶ�1') > 1000 )
		$tbl->setFields( array( '�ֶ�1' => 0 ) );

} while( $tbl->next() );


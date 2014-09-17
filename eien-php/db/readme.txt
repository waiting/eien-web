<?
require_once 'db.class.php';

$cnn = new MySQLConnection("localhost", "user", "password", "dbname", "编码(gbk/utf8)");

// 数据源查询
// 表1全部字段全部数据
$rs = db::rs('table:表1');
// 用sql
$rs = db::rs('select * from 表1 where uid=111;');

// 遍历数据
while ( !$rs->eof() ) 
{
	echo $rs->fields('字段1');
	echo $rs->fields('字段2');
	$rs->moveNext();
}


// 修改器，数据只能单表修改
$mdf = db::mdf("表1");

// 添加
$mdf->addNew( array( '字段1' => 数据, '字段2' => 数据 ) );

// 修改
$mdf->modify(  array( '字段1' => 数据, '字段2' => 数据 ), 主键值 );
$mdf->modifyEx(  array( '字段1' => 数据, '字段2' => 数据 ), "where uid=111" );

// 删除
$mdf->delete(主键值);
$mdf->deleteEx("where uid=111");

// 更方便的单表接口，适合读取之后又需要修改的操作
$tbl = db::tbl("表1");
if ($tbl->loadRecordEx("where uid>111")) do
{
	echo $tbl->getFields('字段1');
	echo $tbl->getFields('字段2');
	// 如果大于1000，就置0
	if ( $tbl->getFields('字段1') > 1000 )
		$tbl->setFields( array( '字段1' => 0 ) );

} while( $tbl->next() );


<?php
set_time_limit(0);
require_once dirname(__FILE__).'/../eien/extra/extra.func.php';
require_once dirname(__FILE__).'/../eien/filesys/folder.func.php';
require_once dirname(__FILE__).'/config.inc.php';
# 要压缩的目录路径
$compress_path = isset($_GET['path']) ? gpc($_GET['path']) : '';
# 保存到路径
$save_path = isset($_GET['save']) ? gpc($_GET['save']) : '';

$arr = preg_split( '@[/\\\\]@', $compress_path, -1, PREG_SPLIT_NO_EMPTY );

if ( count($arr) == 0 )
{
	exit('请指定要压缩的路径');
}

$compress_path = preg_match( '@[/\\\\]@', $compress_path[strlen($compress_path) - 1] ) ? $compress_path : $compress_path.'/';

$zip_name = $arr[count($arr) - 1];

$zip = new ZipArchive();
$zip->open( "{$save_path}{$zip_name}.zip", ZIPARCHIVE::OVERWRITE );

$zip->addEmptyDir($zip_name);

folder_data( $compress_path, $files, $dirs );
foreach ( $files as $f )
{
	echo $compress_path.$f.'<br />';
	$zip->addFile( $compress_path.$f, $zip_name.'/'.$f );
}

echo "<a href='data/$zip_name.zip'>$zip_name.zip</a><br />";
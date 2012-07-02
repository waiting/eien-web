<?php
set_time_limit(0);
require_once dirname(__FILE__).'/../eien/filesys/folder.func.php';

$zip = new ZipArchive();
$zip->open('wp.zip',ZIPARCHIVE::OVERWRITE);
$path = 'wp';
$zip->addEmptyDir($path);
folder_data( $path, $files, $dirs );
foreach ( $files as $f )
{
	echo $path.'/'.$f.'<br />';
	$zip->addFile( $path.'/'.$f, $path.'/'.$f );
}
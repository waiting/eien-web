<?php
set_time_limit(0);
require_once dirname(__FILE__).'/../eien/extra/extra.func.php';
require_once dirname(__FILE__).'/../eien/db/db.class.php';
require_once dirname(__FILE__).'/../eien/db/sqlbackup.class.php';
require_once dirname(__FILE__).'/../eien/db/sqlscript.class.php';
require_once dirname(__FILE__).'/../eien/filesys/file.class.php';
require_once dirname(__FILE__).'/../eien/filesys/folder.func.php';

include 'config.inc.php';

DbConfig::$db_host = $config['db_host'];
DbConfig::$db_user = $config['db_user'];
DbConfig::$db_pwd = $config['db_password'];
DbConfig::$db_name = $config['db_name'];

$data_path = $config['data_path'];

$bak_name = isset($_GET['bak_name']) ? gpc($_GET['bak_name']) : '';

if ( $bak_name == '' || !is_dir($data_path.$bak_name) )
{
	echo '请指定备份名';
	// 输出备份名
	folder_data( $data_path, $files, $dirs );
	echo '<ul>';
	foreach ( $dirs as $bak )
	{
		echo "<li>";
		echo "<a href='?bak_name=$bak' target='_blank'><strong>$bak</strong></a>(".bytes_unit(folder_bytes($data_path.$bak)).")";
		echo "</li>";
	}
	
	echo '</ul>';
	exit();
}

echo "<strong>$bak_name</strong>(".bytes_unit(folder_bytes($data_path.$bak_name)).")";

$bak = new SQLBackup( db::cnn(true), new BlockInFile("{$data_path}$bak_name/db.sql") );
$bak->resumeDB();

echo ' Resume OK!';

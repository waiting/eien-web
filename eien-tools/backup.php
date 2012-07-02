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

# 2012-06-28T09_49_43.0000
$micro = microtime(true);
$time = (int)$micro;
$bak_name = date( 'Y-m-d\TH_i_s', $time ) . strstr( $micro, '.' );

$bak_name = isset($_GET['bak_name']) ? gpc($_GET['bak_name']) : $bak_name;

$bak_path = "{$data_path}$bak_name";

make_dir_exists($bak_path); # 保证目录存在

$bak = new SQLBackup( db::cnn(true), new BlockOutFile("$bak_path/db.sql") );

$bak->backupDB();

echo "<strong>$bak_name</strong>(".bytes_unit(folder_bytes($bak_path)).") Backup OK!";

<?php
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');
require_once dirname(__FILE__).'/../eien/extra/extra.func.php';
require_once dirname(__FILE__).'/../eien/db/db.class.php';
require_once dirname(__FILE__).'/../eien/db/sqlbackup.class.php';
require_once dirname(__FILE__).'/../eien/db/sqlscript.class.php';
require_once dirname(__FILE__).'/../eien/filesys/file.class.php';
require_once dirname(__FILE__).'/../eien/filesys/filesys.func.php';

include 'config.inc.php';

DbConfig::$db_host = $config['db_host'];
DbConfig::$db_user = $config['db_user'];
DbConfig::$db_pwd = $config['db_password'];
DbConfig::$db_name = $config['db_name'];

# 2012-06-28T09_49_43.0000
$micro = microtime(true);
$time = (int)$micro;
$bak_name = date( 'Y-m-d\TH_i_s', $time ) . strstr( $micro, '.' );

$bak_name = isset($_GET['bak_name']) && $_GET['bak_name'] != '' ? gpc($_GET['bak_name']) : $bak_name;

$data_path = $config['data_path'];

$bak_path = $data_path . $bak_name;

# 是否执行备份
$bak_exec = isset($_GET['bak_exec']) ? (int)$_GET['bak_exec'] : 0;

if ( $bak_exec )
{
	$bak = new SQLBackup( db::cnn(true), new BlockOutFile("$bak_path/db.sql") );
	$bak->backupDB();
	echo "<strong>$bak_name</strong>(".bytes_unit(folder_bytes($bak_path)).") Backup OK!";
}
else
{
	echo '已存在的备份';
	// 输出备份名
	folder_data( $data_path, $files, $dirs );
	echo '<ul>';
	foreach ( $dirs as $bak )
	{
		echo "<li>";
		echo "<strong>$bak</strong>(".bytes_unit(folder_bytes($data_path.$bak)).") <a href='zip.php?path=".rawurlencode($data_path.$bak)."&save=".rawurlencode($data_path)."' target='_blank'>ZIP压缩</a>";
		echo "</li>";
	}
	foreach ( $files as $fbak )
	{
		file_name( $fbak, $ext );
		if ( $ext != 'zip' ) continue;
		echo "<li>";
		echo "<strong>$fbak</strong>(".bytes_unit(filesize($data_path.$fbak)).") <a href='unzip.php?path=".rawurlencode($data_path.$fbak)."&save=".rawurlencode($data_path)."' target='_blank'>ZIP解压</a>";
		echo "</li>";
	}
	echo '</ul>';
?>创建一个备份
<form method="get" action="?">
<input type="hidden" name="bak_exec" value="1" />
备份名:<input type="text" name="bak_name" /><br />
<input type="submit" value="备份" />
</form>

<?php
}
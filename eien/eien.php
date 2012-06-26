<?php
// eien框架初始化
define('IN_EIEN', 1);
# eien框架路径
define('EIEN_PATH', dirname(__FILE__).'/');

// 网站配置
require_once EIEN_PATH.'config.php';
require_once EIEN_PATH.'dbtables.php';
// 辅助
require_once EIEN_PATH.'extra/extra_func.php';

// 获取表名
function tname($tbl)
{
	global $config;
	return $config['table_prefix'].$config['table_names'][$tbl];
}
// 获取配置
function config($name)
{
	global $config;
	return array_key_exists($name, $config) ? $config[$name] : null;
}

// 执行一些初始化动作
mb_internal_encoding(config('page_charset'));  // 设置内部编码



# eien框架配置
require_once EIEN_PATH.'eien_config.php';


?>
<?php
// eien��ܳ�ʼ��
define('IN_EIEN', 1);
# eien���·��
define('EIEN_PATH', dirname(__FILE__).'/');

// ��վ����
require_once EIEN_PATH.'config.php';
require_once EIEN_PATH.'dbtables.php';
// ����
require_once EIEN_PATH.'extra/extra_func.php';

// ��ȡ����
function tname($tbl)
{
	global $config;
	return $config['table_prefix'].$config['table_names'][$tbl];
}
// ��ȡ����
function config($name)
{
	global $config;
	return array_key_exists($name, $config) ? $config[$name] : null;
}

// ִ��һЩ��ʼ������
mb_internal_encoding(config('page_charset'));  // �����ڲ�����



# eien�������
require_once EIEN_PATH.'eien_config.php';


?>
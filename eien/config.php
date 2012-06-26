<?php
if (!defined('IN_EIEN')) exit("No in eien framework");

/* 网站配置文件 */
$config = array();
# 系统参数
$config['page_charset'] = 'utf-8'; // 页面编码

# 数据库参数
$config['db_type']        =     'mysql';         // 数据库类型,目前仅支持mysql

$config['db_charset']     =     'utf8';          // MySQL校验字符集
$config['db_host']        =     'localhost';     // 数据库地址
$config['db_user']        =     'wt';            // 用户名
$config['db_password']    =     'wt';            // 密码
$config['db_name']        =     'ecss2';        // 数据库名
$config['db_port']        =     3306;            // 端口,一般不要修改.

# SQL相关参数(用作初始化安装的SQL脚本)
$config['sql_path'] = EIEN_PATH . 'sqlscripts/';      // SQL脚本路径
// SQL脚本,可以为string(SQL文件名), array(SQL文件名组), 或者是 -1(路径下全部文件,文件名字典顺序)
$config['sql_files'] = -1;

# COOKIE参数
$config['cookie_domain'] = '';
$config['cookie_path'] = '';

# SESSION参数
$config['session_where'] = 0;          # Session在哪存储,1数据库,0独立文件
$config['session_path'] = EIEN_PATH.'session/data/';  # Session存取路径,可以修改成你的文件夹,
                                                      # 注意该文件夹需有可写权限 755
                                                      # 仅在 $config['session_where'] = 0 时有用
//-----------------------------


?>
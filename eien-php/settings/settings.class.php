<?php
/** 网站配置类 Settings
 * @author WaiTing
 * @version 1.0.0 */

//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_SETTINGS_CLASS', 'settings/settings.class.php');
/* CREATE TABLE `{prefix}_settings` (
  `var_name` char(32) NOT NULL,
  `var_value` text NOT NULL,
  PRIMARY KEY  (`var_name`)
) ENGINE=MyISAM; */
class Settings
{
	private $table = null;       # IDBTable表接口
	private $cacheMap = array(); # 缓存
	public function __construct($tableName)
	{
		$this->table = db::tbl($tableName);
	}
	public function get($varname, $cache = true)
	{
		if ($cache && isset($this->cacheMap[$varname]))
			return $this->cacheMap[$varname];

		if ($this->table->loadRecord($varname, 'var_value'))
		{
			$this->cacheMap[$varname] = $this->table->getFields('var_value');
			return $this->cacheMap[$varname];
		}
		return null;
	}
	public function set($varname, $value)
	{
		return $this->table->mdf()->modify(array('var_value'=>$value), $varname);
	}
	public function __get($name)
	{
		return $this->get($name);
	}
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
	public function __isset($name)
	{
		return $this->get($name) !== null;
	}
	public function __unset($name)
	{
		return;
	}

}
// 解析链接
/*
function parse_links($data)
{
	$links_arr = array();
	if (preg_match_all('@<\[(.*?)\]>@', $data, $matches))
	{
		$links = $matches[1];
		$count = count($links);
		for ($i = 0; $i < $count; $i++)
		{
			array_push($links_arr, $links[$i]);
		}
	}
	return $links_arr;
}*/

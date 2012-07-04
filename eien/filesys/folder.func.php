<?php

define('EIEN_FOLDER_FUNC', 'filesys/folder.func.php');

/**获取文件夹包含文件的一些信息,如文件总数,文件夹数,总字节数*/
function folder_info($path, &$fileCount = null, &$dirCount = null)
{
	$handle = opendir($path);
	$bytes = 0;
	while ($filename = readdir($handle))
	{
		if ($filename == '.' || $filename == '..') continue;
		$fullname = $path.'/'.$filename;
		if (is_dir($fullname))
		{
			$dirCount++;
			$bytes += folder_info($fullname, $fileCount, $dirCount);
		}
		else
		{
			$fileCount++;
			$bytes += filesize($fullname);
		}
	}
	closedir($handle);
	return $bytes;
}
/**文件夹所含文件总字节数*/
function folder_bytes($path)
{
	return folder_info($path);
}
/**获取文件夹中的文件和子文件夹*/
function folder_data($path, &$fileArr, &$subFolderArr)
{
	$handle = opendir($path);
	$fileArr = array();
	$subFolderArr = array();
	while ($filename = readdir($handle))
	{
		if ($filename == '.' || $filename == '..') continue;
		if (is_dir($path.'/'.$filename))
			$subFolderArr[] = $filename;
		else
			$fileArr[] = $filename;
	}
	closedir($handle);
	asort($fileArr);
	asort($subFolderArr);
}
/** 通用删除.删除文件夹和文件*/
function common_delete($path)
{
	if (is_dir($path))
	{
		folder_data($path, $fileArr, $subFolderArr);
		foreach ($fileArr as $filename)
			common_delete($path.'/'.$filename);

		foreach ($subFolderArr as $subpath)
			common_delete($path.'/'.$subpath);

		rmdir($path);
	}
	else
	{
		unlink($path);
	}
}
/** 使目录存在 */
function make_dir_exists( $path, $cb_func_create = null )
{
	if ( $path == '' ) return '';

	$sub_dirs = preg_split( '@[/\\\\]@', $path, -1, PREG_SPLIT_NO_EMPTY );

	$fullpath = '';
	$open_basedirs = array(); # 基路径限制

	$osflag = (strtolower(PHP_SHLIB_SUFFIX) == 'dll'); # OS平台
	if ( $osflag == false ) # linux 下
	{
		$fullpath .= $path[0] == '/' ? '/' : ''; # 判断是否为根目录
		$open_basedirs = preg_split( '@:@', ini_get('open_basedir'), -1, PREG_SPLIT_NO_EMPTY );
	}
	else # win 下
	{
		if ( $path[0] == '/' || $path[0] == '\\' )
		{
			$doc_root = $_SERVER['DOCUMENT_ROOT'];
			$fullpath .= substr( $doc_root, 0, 2 ) . '/';
		}
		else if ( preg_match( '@\w:@', $sub_dirs[0] ) )
		{
			$fullpath .= $sub_dirs[0].'/';
			unset($sub_dirs[0]);
		}
		$open_basedirs = preg_split( '@;@', ini_get('open_basedir'), -1, PREG_SPLIT_NO_EMPTY );
	}
	# 验证基目录
	if ( $fullpath != '' ) // 表示不是相对路径
	{
		$is_ok = false;
		$subks = array(); // 要移除的基目录索引
		foreach ( $open_basedirs as $k => $basedir )
		{
			$basedir = str_replace( '\\', '/', realpath($basedir) ).'/';
			$temp_path = $fullpath;
			$subks = array();
			foreach ( $sub_dirs as $k => $sub )
			{
				$subks[] = $k;
				$temp_path .= $sub.'/';
				$ret = strpos( $basedir, $temp_path );
				if ( $ret !== false && $ret === 0 )
				{
					if ( $temp_path == $basedir )
					{
						$fullpath = $basedir; # 设为基路径
						$is_ok = true;
						break;
					}
				}
				else
				{
					break;
				}
			}
			if ( $is_ok ) break;
		}
		// 删除基目录
		if ( $is_ok ) foreach ( $subks as $k ) unset($sub_dirs[$k]);
	}

	foreach ( $sub_dirs as $sub )
	{
		$fullpath .= $sub . '/';
		if ( !is_dir($fullpath) )
		{
			$ret = $cb_func_create == null ? mkdir( $fullpath, 0700 ) : $cb_func_create($fullpath);
			if ( !$ret ) break;
		}
	}
	return $fullpath;
}

/** 文件名,扩展名 */
function file_name($fullpath, &$ext = null)
{
	$name = basename($fullpath);
	$pos = strrpos($name, '.');
	if ($pos !== false) $ext = substr($name, $pos + 1);
	return substr($name, 0, $pos === false ? -1 : $pos);
}



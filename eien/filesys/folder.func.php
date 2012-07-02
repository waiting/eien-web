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
function make_dir_exists($path, $cb_func_create = null)
{
	$arr = split('/', $path);
	$fullpath = '';
	foreach ($arr as $subpath)
	{
		if($subpath != '')
		{
			$fullpath .= $subpath . '/';
			if (!is_dir($fullpath))
			{
				if ($cb_func_create == null)
				{
					mkdir($fullpath, 0700);
				}
				else
				{
					$cb_func_create($fullpath);
				}
			}
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










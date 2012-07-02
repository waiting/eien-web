<?php

define('EIEN_FOLDER_FUNC', 'filesys/folder.func.php');

/**��ȡ�ļ��а����ļ���һЩ��Ϣ,���ļ�����,�ļ�����,���ֽ���*/
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
/**�ļ��������ļ����ֽ���*/
function folder_bytes($path)
{
	return folder_info($path);
}
/**��ȡ�ļ����е��ļ������ļ���*/
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
/** ͨ��ɾ��.ɾ���ļ��к��ļ�*/
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
/** ʹĿ¼���� */
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

/** �ļ���,��չ�� */
function file_name($fullpath, &$ext = null)
{
	$name = basename($fullpath);
	$pos = strrpos($name, '.');
	if ($pos !== false) $ext = substr($name, $pos + 1);
	return substr($name, 0, $pos === false ? -1 : $pos);
}










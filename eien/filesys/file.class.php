<?php
/** 文件/目录操作相关
 * @author WaiTing
 * @package Eien
 * @version 1.1.0 */
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_FILE_CLASS', 'filesys/file.class.php');

/**
 * 文件接口
 * @version 1.0.0
 */
interface IFile {
	/**
	 * 打开文件
	 * @param string $filename
	 * @param string $mode
	 */
	function open($filename,$mode);
	/**
	 * 关闭文件
	 * @return bool
	 */
	function close();
	/**
	 * 读数据
	 * @param int $length
	 * @return string
	 */
	function read($length);
	/**
	 * 写数据
	 * @param string $str
	 * @param int[optional] $length
	 * @return int
	 */
	function write($str,$length=null);
	/**
	 * 重置文件指针到头
	 * @return bool
	 */
	function rewind();
	/**
	 * 移动文件指针
	 * @param int $offset
	 * @param int[optional] $whence
	 * @return int
	 */
	function seek($offset,$whence=null);
	/**
	 * 获得文件指针位置
	 * @return int
	 */
	function tell();
	/**
	 * 获取字符
	 * @param int[optional] $length
	 * @return string
	 */
	function gets($length=null);
	/**
	 * 输出字符
	 * @param string $str
	 * @return int
	 */
	function puts($str);
	/**
	 * 文件是否结束
	 * @return bool
	 */
	function eof();
}

/**
 * 文件类
 * @version 1.0.0
 */
class File implements IFile
{
/**
 * 文件FP
 * @var resource
 */
public $fp = null;
/**
 * 文件名
 * @var string
 */
public $filename = null;         # 文件
/**
 * 文件大小
 * @var int
 */
public $filesize = 0;            # 文件大小
/**
 * 数据缓冲区
 * @var string
 */
public $bufData = null;          # 数据内容
/**
 * 文件对象构造函数
 * @param string[optional] $filename
 * @param string[optional] $mode
 * @param bool[optional] $autoload
 * @return File
 */
public function File($filename = null,$mode = 'rb',$autoload = true)
{
	if ($filename != null) $this->open($filename, $mode, $autoload);
}
/**
 * 析构函数
 */
public function __destruct()
{
	$this->close();
}
/**
 * 打开文件
 * @param string $filename
 * @param string $mode
 * @param bool[optional] $autoload
 */
public function open($filename, $mode, $autoload = true)
{
	$this->filename = $filename;
	$this->fp = fopen($filename, $mode);
	if (!strchr($mode,'w'))
	{
		$this->filesize = filesize($filename);
		if ($autoload) $this->loadData();
	}
}
/**
 * 关闭文件
 */
public function close()
{
	if ($this->fp != null) fclose($this->fp);
	$this->fp = null;
	$this->filesize = 0;
}
/**
 * 载入数据,只适用文本文件
 */
public function loadData()
{
	if ($this->fp)
	{
		$this->bufData = '';
		while ($data = $this->read($this->filesize)) $this->bufData .= $data;
		$this->rewind();
	}
}
/**
 * 读数据
 * @param int $length
 * @return string
 */
public function read($length)
{
	return fread($this->fp,$length);
}
/**
 * @param string $str
 * @param int[optional] $length
 * @return int
 */
public function write($str, $length = null)
{
	return fwrite($this->fp, $str, $length);
}
/**
 * @return bool
 */
public function rewind()
{
	return rewind($this->fp);
}
/**
 * @param int $offset
 * @param int[optional] $whence
 * @return int
 */
public function seek($offset, $whence = null)
{
	return fseek($this->fp, $offset, $whence);
}
/**
 * @return int
 */
public function tell()
{
	return ftell($this->fp);
}
/**
 * 获取字符
 * @param int[optional] $length
 * @return string
 */
public function gets($length = null)
{
	if ($length == null) return fgets($this->fp);
	return fgets($this->fp, $length);
}
/**
 * 输出字符
 * @param strng $str
 * @return int
 */
public function puts($str)
{
	return fputs($this->fp, $str, strlen($str));
}
/**
 * 文件是否结束
 * @return bool
 */
public function eof()
{
	return feof($this->fp);
}
/**
 * 文件是否存在
 * @param string $filename
 * @return bool
 */
public static function exists($filename)
{
	return file_exists($filename);
}
/**
 * 删除文件
 * @param string $filename
 * @return bool
 */
public static function delete($filename)
{
	if(File::exists($filename))
		return unlink($filename);
	return false;
}

}

/** 分块输出文件
 * @version 1.0.1
 */
class BlockOutFile extends File
{
private $dirname;     # 目录名
private $basename;    # 文件名    filename.txt
private $filetitle;   # 文件标题  filename
private $extname;     # 扩展名    .txt
private $fileno = 1;  # 文件号
private $blockSize;   # 块大小
function BlockOutFile($filename, $blockSize = 1048576)
{
	$this->blockSize = $blockSize;
	$this->dirname = dirname($filename);
	make_dir_exists($this->dirname);
	$this->basename = basename($filename);
	$pos = strrpos($this->basename, '.');
	if ($pos === false)
	{
		$this->filetitle = $this->basename;
		$this->extname = '';
	}
	else
	{
		$this->filetitle = substr($this->basename, 0, $pos);
		$this->extname = substr($this->basename, $pos);
	}
	$this->nextBlock();
}
public function nextBlock()
{
	$this->close();
	$fileno = $this->fileno;//sprintf('%03d',$this->fileno);
	$this->open($this->dirname.'/'.$this->filetitle.'_'.$fileno.$this->extname, 'w', false);
	$this->fileno++;
	return true;
}
function puts($str)
{
	$lenStr = strlen($str);
	$this->filesize += $lenStr;
	if ($this->filesize > $this->blockSize)
	{
		$this->nextBlock();
		$this->filesize = $lenStr;
	}
	return fputs($this->fp,$str);
}

}

/** 分块输入文件
 * @version 1.0.0 */
class BlockInFile extends File
{
private $dirname;     # 目录名
private $basename;    # 文件名    filename.txt
private $filetitle;   # 文件标题  filename
private $extname;     # 扩展名    .txt
private $index = 0;   # 文件索引
private $blockFiles = array();
public function BlockInFile($filename)
{
	$this->dirname = dirname($filename);
	$this->basename = basename($filename);
	$pos = strrpos($this->basename, '.');
	if ($pos === false)
	{
		$this->filetitle = $this->basename;
		$this->extname = '';
	}
	else
	{
		$this->filetitle = substr($this->basename, 0, $pos);
		$this->extname = substr($this->basename, $pos);
	}
	// 处理文件标题
	// 如果有分块数字,则搜索其他分块,并判断存在性
	if (preg_match('/(.*_)(\d+)$/',$this->filetitle,$matches))
	{
		$this->filetitle = $matches[1];
		$maxfileno = (int)$matches[2];
		$i = 1;
		for ($i = 1; $i <= $maxfileno; $i++)
		{
			$filename = $this->dirname.'/'.$this->filetitle.$i.$this->extname;
			if (File::exists($filename))
			{
				array_push($this->blockFiles,$filename);
			}
		}
		$flag = true;
		for (; $flag; $i++)
		{
			$filename = $this->dirname.'/'.$this->filetitle.$i.$this->extname;
			if ($flag = File::exists($filename))
			{
				array_push($this->blockFiles,$filename);
			}
		}
	}
	// 如果无,则自动从1开始添加并判断存在性
	else
	{
		$i = 1;
		$flag = true;
		for (; $flag; $i++)
		{
			$filename = $this->dirname.'/'.$this->filetitle."_".$i.$this->extname;
			if ($flag = File::exists($filename))
			{
				array_push($this->blockFiles,$filename);
			}
		}
	}
	$this->nextBlock();
}
public function nextBlock()
{
	$count = count($this->blockFiles);
	if ($this->index >= $count) return false;
	$this->open($this->blockFiles[$this->index],'r',false);
	$this->index++;
	return true;
}
public function eof()
{
	$b = parent::eof();
	if ($b)
	{
		return !$this->nextBlock();
	}
	return $b;
}

}


<?php
/** 文件/目录操作相关
 * @author WaiTing
 * @package Eien
 * @version 1.1.0
 * @@dependency filesys/filesys.func.php */

define('EIEN_FILE_CLASS', 'filesys/file.class.php');

/** 文件接口
 * @version 1.0.0 */
interface IFile
{
	/** 打开文件
	 * @param $filename string
	 * @param $mode string
	 * @return boolean */
	function open( $filename, $mode );
	/** 关闭文件
	 * @return boolean */
	function close();
	/** 读数据
	 * @param int $size
	 * @return string */
	function read( $size );
	/** 写数据
	 * @param $data string
	 * @param $size int[optional]
	 * @return int */
	function write( $data, $size = null );
	/** 重置文件指针到头
	 * @return boolean */
	function rewind();
	/** 移动文件指针
	 * @param $offset int
	 * @param $whence int[optional]
	 * @return int */
	function seek( $offset, $whence = null );
	/** 获得文件指针位置
	 * @return int */
	function tell();
	/** 获取字符串
	 * @param $length int[optional]
	 * @return string */
	function gets( $length = null );
	/** 输出字符
	 * @param $str string
	 * @return int */
	function puts( $str );
	/** 文件是否结束
	 * @return boolean */
	function eof();
	/** 文件大小 */
	function size();
	/** 缓冲区 */
	function buffer();
}

/** 磁盘文件
 * @version 1.0.0 */
class File implements IFile
{
	/** 文件FP
	 * @var resource */
	protected $fp = null;
	/** 文件名
	 * @var string */
	protected $filename = null;
	/** 文件大小
	 * @var int */
	protected $filesize = 0;
	/** 数据缓冲区
	 * @var string */
	protected $bufData = null;
	/** 文件对象构造函数
	 * @param $filename string[optional]
	 * @param $mode string[optional]
	 * @param $autoload boolean[optional]
	 * @return File */
	public function __construct( $filename = null, $mode = 'rb', $autoload = true )
	{
		$this->open( $filename, $mode, $autoload );
	}
	/** 析构函数 */
	public function __destruct()
	{
		$this->close();
	}
	/** 打开文件
	 * @param $filename string
	 * @param $mode string
	 * @param $autoload boolean[optional]
	 * @return boolean */
	public function open( $filename, $mode, $autoload = true )
	{
		$this->filename = $filename;
		if ( $this->filename != null )
		{
			$this->fp = fopen( $this->filename, $mode );
			if ( !strchr( $mode, 'w' ) )
			{
				$this->filesize = filesize($this->filename);
				if ( $autoload ) $this->loadData();
			}
			return $this->fp;
		}
		return false;
	}
	/** 关闭文件 */
	public function close()
	{
		$ret = true;
		if ( $this->fp != null ) $ret = fclose($this->fp);
		$this->fp = null;
		$this->filesize = 0;
		return $ret;
	}
	/** 载入数据 */
	public function loadData()
	{
		if ( $this->fp != null )
		{
			//$this->bufData = '';
			//while ($data = $this->read($this->filesize)) $this->bufData .= $data;
			$this->bufData = $this->read($this->filesize);
			$this->rewind();
		}
	}
	/** 读数据
	 * @param $size int
	 * @return string */
	public function read( $size )
	{
		return fread( $this->fp, $size );
	}
	/**
	 * @param $data string
	 * @param $size int[optional]
	 * @return int */
	public function write( $data, $size = null )
	{
		return fwrite( $this->fp, $data, $size );
	}
	/**
	 * @return boolean */
	public function rewind()
	{
		return rewind($this->fp);
	}
	/**
	 * @param $offset int
	 * @param $whence int[optional]
	 * @return int */
	public function seek( $offset, $whence = null )
	{
		return fseek( $this->fp, $offset, $whence );
	}
	/**
	 * @return int */
	public function tell()
	{
		return ftell($this->fp);
	}
	/** 获取字符串
	 * @param $length int[optional]
	 * @return string */
	public function gets( $length = null )
	{
		if ( $length === null ) return fgets($this->fp);
		return fgets( $this->fp, $length );
	}
	/** 输出字符串
	 * @param $str string
	 * @return int */
	public function puts( $str )
	{
		return fputs( $this->fp, $str/*, strlen($str)*/ );
	}
	/** 文件是否结束
	 * @return boolean */
	public function eof()
	{
		return feof($this->fp);
	}
	/** 文件大小 */
	public function size()
	{
		return $this->filesize;
	}
	/** 缓冲区 */
	public function buffer()
	{
		return $this->bufData;
	}
	//////////////////////////////////////////////////////////////////
	/** 文件是否存在
	 * @param $filename string
	 * @return bool */
	public static function exists( $filename )
	{
		return file_exists($filename);
	}
	/** 删除文件
	 * @param $filename string
	 * @return boolean */
	public static function delete( $filename )
	{
		if ( File::exists($filename) )
			return unlink($filename);
		return false;
	}

}

/** 分块输出文件
 * @version 1.0.1 */
class BlockOutFile extends File
{
	private $dirname;     # 目录名
	private $basename;    # 文件名    filename.txt
	private $filetitle;   # 文件标题  filename
	private $extname;     # 扩展名    .txt
	private $fileno = 1;  # 文件号
	private $blockSize;   # 块大小
	function __construct( $filename, $blockSize = 1048576 )
	{
		$this->blockSize = $blockSize;
		$this->dirname = dirname($filename);
		// 确保路径存在
		make_dir_exists($this->dirname); # @@use function make_dir_exists()
		$this->basename = basename($filename);
		$pos = strrpos( $this->basename, '.' );
		if ( $pos === false )
		{
			$this->filetitle = $this->basename;
			$this->extname = '';
		}
		else
		{
			$this->filetitle = substr( $this->basename, 0, $pos );
			$this->extname = substr( $this->basename, $pos );
		}
		$this->nextBlock();
	}
	public function nextBlock()
	{
		$this->close();
		$fileno = $this->fileno;
		$this->open( $this->dirname.'/'.$this->filetitle.'_'.$fileno.$this->extname, 'w', false );
		$this->fileno++;
		return true;
	}
	function puts( $str )
	{
		$lenStr = strlen($str);
		$this->filesize += $lenStr;
		if ( $this->filesize > $this->blockSize )
		{
			$this->nextBlock();
			$this->filesize = $lenStr;
		}
		return parent::puts($str);
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
	public function __construct($filename)
	{
		$this->dirname = dirname($filename);
		$this->basename = basename($filename);
		$pos = strrpos( $this->basename, '.' );
		if ( $pos === false )
		{
			$this->filetitle = $this->basename;
			$this->extname = '';
		}
		else
		{
			$this->filetitle = substr( $this->basename, 0, $pos );
			$this->extname = substr( $this->basename, $pos );
		}
		// 处理文件标题
		// 如果有分块数字,则搜索其他分块,并判断存在性
		if ( preg_match( '/(.*_)(\d+)$/', $this->filetitle, $matches ) )
		{
			$this->filetitle = $matches[1];
			$maxfileno = (int)$matches[2];
			$i = 1;
			for ( $i = 1; $i <= $maxfileno; $i++ )
			{
				$filename = $this->dirname.'/'.$this->filetitle.$i.$this->extname;
				if ( File::exists($filename) )
				{
					array_push( $this->blockFiles, $filename );
				}
			}
			$flag = true;
			for ( ; $flag; $i++ )
			{
				$filename = $this->dirname.'/'.$this->filetitle.$i.$this->extname;
				if ( $flag = File::exists($filename) )
				{
					array_push( $this->blockFiles, $filename );
				}
			}
		}
		else // 如果无,则自动从1开始添加并判断存在性
		{
			$i = 1;
			$flag = true;
			for ( ; $flag; $i++ )
			{
				$filename = $this->dirname.'/'.$this->filetitle.'_'.$i.$this->extname;
				if ( $flag = File::exists($filename) )
				{
					array_push( $this->blockFiles, $filename );
				}
			}
		}
		$this->nextBlock();
	}
	public function nextBlock()
	{
		$count = count($this->blockFiles);
		if ( $this->index >= $count ) return false;
		$this->open( $this->blockFiles[$this->index], 'r', false );
		$this->index++;
		return true;
	}
	public function eof()
	{
		$b = parent::eof();
		if ( $b )
		{
			return !$this->nextBlock();
		}
		return $b;
	}

}

/** 标准输出文件 */
class StdoutFile implements IFile
{
	private $filename;
	public function __construct( $filename = null, $mode = null )
	{
		$this->open( $filename, $mode );
	}
	public function __destruct()
	{
		$this->close();
	}
	public function open( $filename, $mode )
	{
		$this->filename = $filename;
		return ob_start();
	}
	public function close()
	{
		return ob_end_flush();
	}
	public function read( $size )
	{
		exit(__CLASS__.'::'.__FUNCTION__.'() not implemented.');
	}
	public function write( $data, $size = null )
	{
		print $data;
		return strlen($data);
	}
	public function rewind()
	{
		ob_clean();
	}
	public function seek( $offset, $whence = null )
	{
		exit(__CLASS__.'::'.__FUNCTION__.'() not implemented.');
	}
	public function tell()
	{
		exit(__CLASS__.'::'.__FUNCTION__.'() not implemented.');
	}
	public function gets( $length = null )
	{
		exit(__CLASS__.'::'.__FUNCTION__.'() not implemented.');
	}
	public function puts( $str )
	{
		print $str;
		return strlen($str);
	}
	public function eof()
	{
		exit(__CLASS__.'::'.__FUNCTION__.'() not implemented.');
	}
	public function size()
	{
		return ob_get_length();
	}
	public function buffer()
	{
		return ob_get_contents();
	}
}

<?php
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_GD_CLASS', 1);

/**
 * 此模块封装一些GD库函数
 * @package Eien.Web.Graphics
 * @author WaiTing
 * @version 1.1.0
 */
/**
 * 输出中文
 * @param resource $im
 * @param float $size
 * @param float $angle
 * @param int $x
 * @param int $y
 * @param int $color
 * @param string $font_file
 * @param string $text
 * @return array
 */
function imageTTFTextOut($im,$size,$angle,$x,$y,$color,$font_file,$text)
{
	return imagettftext($im,$size,$angle,$x,$y,$color,$font_file,Graphics::textConv($text));
}
/**
 * 输出中文
 * @param resource $im
 * @param float $size
 * @param float $angle
 * @param int $x
 * @param int $y
 * @param int $color
 * @param string $font_name
 * @param string $text
 * @return array
 */
function imageTextOut($im,$size,$angle,$x,$y,$color,$font_name,$text)
{
	return imageTTFTextOut($im,$size,$angle,$x,$y,$color,Graphics::fontfilename($font_name),$text);
}

/**
 * 颜色类
 * version 1.0.0
 */
class Color
{
# 分配的颜色
public $gd_color = null;
# 图片对象:Image
public $image = null;

/**
 * 颜色构造函数
 * @param Image $image
 * @param int $red
 * @param int $green
 * @param int $blue
 * @param mixed $mixed 若为true,则指定透明色;若为数字,则分配alpha色;默认是null分配普通色
 */
public function Color($image, $red = null, $green = null, $blue = null, $mixed = null)
{
	$this->image = $image;
	$this->alloc($red, $green, $blue, $mixed);
}
/**
 * 分配颜色
 * @param int $red
 * @param int $green
 * @param int $blue
 * @param mixed $mixed 若为true,则指定透明色;若为数字,则分配alpha色;默认是null分配普通色
 * @return gd_color
 */
public function alloc($red = null, $green = null, $blue = null, $mixed = null)
{
	if (!($this->image instanceof Image)) return $this->gd_color;
	if ($mixed !== null) // 非普通色
	{
		if ($mixed === true) // 指定透明
		{
			$color = null;
			if ($red !== null)
			{
				$color = $this->image->colorAllocate($red, $green, $blue);
			}
			$this->gd_color = $this->image->colorTransparent($color);
		}
		else // alpha
		{
			$this->gd_color = $this->image->colorAllocateAlpha($red, $green, $blue, $mixed % 128);
		}
	}
	else // 普通色
	{
		if ($red !== null)
			$this->gd_color = $this->image->colorAllocate($red, $green, $blue);
	}
	return $this->gd_color;
}
/** 指定透明色 */
public function transparent($red = null, $green = null, $blue = null)
{
	return $this->alloc($red, $green, $blue, true);
}
/** 分配alpha色 */
public function alpha($red, $green, $blue, $alpha = 0)
{
	return $this->alloc($red, $green, $blue, $alpha);
}
/**
 * 分配颜色
 * @param mixed $color 颜色,可以是字符串#XXXXXX,亦可是数字0xXXXXXX
 * @param mixed $mixed 若为true,则指定透明色;若为数字,则分配alpha色;默认是null分配普通色
 * @return gd_color
 */
public function allocColor($color, $mixed = null)
{
	$r = null;
	$g = null;
	$b = null;
	if (is_string($color))
		Color::splitHtmlColor($color, $r, $g, $b);
	elseif (is_numeric($color))
		Color::splitRGB($color, $r, $g, $b);
	return $this->alloc($r, $g, $b, $mixed);
}
// static functions -------------------------------------------
public static function from($image, $color, $mixed = null)
{
	$r = null;
	$g = null;
	$b = null;
	if (is_string($color))
		Color::splitHtmlColor($color,$r,$g,$b);
	elseif (is_numeric($color))
		Color::splitRGB($color,$r,$g,$b);
	return new Color($image,$r,$g,$b,$mixed);
}
/**
 * 分离RGB颜色数值,数值用int指定,调用此函数分离成3个分量R,G,B.
 * @param int $int_rgb
 * @param int &$red
 * @param int &$green
 * @param int &$blue
 */
public static function splitRGB($int_rgb, &$red, &$green, &$blue)
{
	$red = $int_rgb & 0xff;
	$green = ($int_rgb >> 8) & 0xff;
	$blue = ($int_rgb >> 16) & 0xff;
}
/**
 * 分离RGB颜色,颜色用#FFFFFF的方式指定,此函数分离其为3个分量R,G,B.
 * @param string $html_color
 * @param int &$red
 * @param int &$green
 * @param int &$blue
 */
public static function splitHtmlColor($html_color, &$red, &$green, &$blue)
{
	$html_color = str_replace('#','',$html_color);
	for ($i = 0; $i < 6 - strlen($html_color); $i++) $html_color .= '0';
	$red = 0 + hexdec($html_color[0].$html_color[1]);
	$green = 0 + hexdec($html_color[2].$html_color[3]);
	$blue = 0 + hexdec($html_color[4].$html_color[5]);
}

}
//--------------------------------------------------------
/**
 * 绘图类,绑定图片对象后才能使用
 * version 1.1.0
 */
class Graphics
{
	// 字体文件
	public static $fontfiles = array(
		'宋体'=>'simsun.ttc',
		'华文形楷'=>'stxingka.ttf',
		'隶书'=>'simli.ttf'
	);
	// 字体目录
	public static $fontDir;
	/**
	 * @var Image
	 */
	private $image_obj = null;      # Image Object
	/**
	 * @param Image[optional] $im_obj
	 * @return Graphics
	 */
	public function Graphics(Image $im_obj = null)
	{
		$this->bindImage($im_obj);
	}
	/**
	 * @param Image $im_obj
	 */
	public function bindImage(Image $im_obj)
	{
		$this->image_obj = $im_obj;
	}
	/**
	 * @param float $size
	 * @param float $angle
	 * @param int $x
	 * @param int $y
	 * @param mixed $color
	 * @param string $font_file
	 * @param string $text
	 * @return array
	 */
	public function ttfTextOut($size,$angle,$x,$y,$color,$font_file,$text)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagettftext($this->image_obj->getRes(),$size,$angle,$x,$y,$gd_color,$font_file,Graphics::textConv($text));
	}
	/**
	 * @param float $size
	 * @param float $angle
	 * @param int $x
	 * @param int $y
	 * @param mixed $color
	 * @param string $font_name
	 * @param string $text
	 * @return array
	 */
	public function textOut($size,$angle,$x,$y,$color,$font_name,$text)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return $this->ttfTextOut($size,$angle,$x,$y,$gd_color,Graphics::fontfilename($font_name),$text);
	}
	public function drawEnglish($fontInx, $x, $y, $str, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagestring($this->image_obj->getRes(), $fontInx, $x, $y, $str, $gd_color);
	}
	/**
	 * 在指定坐标用指定颜色填充
	 * @param int $x
	 * @param int $y
	 * @param mixed $color
	 * @return bool
	 */
	public function fill($x,$y,$color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagefill($this->image_obj->getRes(),$x,$y,$gd_color);
	}
	/**
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param mixed $color
	 * @return bool
	 */
	public function filledRectangle($x,$y,$width,$height,$color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagefilledrectangle($this->image_obj->getRes(),$x,$y,$x+$width,$y+$height,$gd_color);
	}
	public function fillToBorder($x, $y, $borderColor, $color)
	{
		$gd_color = $color;
		$gd_border = $borderColor;
		if ($color instanceof Color)
			$gd_color = $color->gd_color;
		if ($borderColor instanceof Color)
			$gd_border = $borderColor->gd_color;
		return imagefilltoborder($this->image_obj->getRes(),$x,$y,$gd_border,$gd_color);
	}
	public function arc($center_x, $center_y, $width, $height, $start, $end, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagearc($this->image_obj->getRes(), $center_x, $center_y, $width, $height, $start, $end, $gd_color);
	}
	public function line($x1, $y1, $x2, $y2, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imageline($this->image_obj->getRes(), $x1, $y1, $x2, $y2, $gd_color);
	}
	public function dashedLine($x1, $y1, $x2, $y2, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagedashedline($this->image_obj->getRes(), $x1, $y1, $x2, $y2, $gd_color);
	}
	/**
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @param mixed $color
	 * @return bool
	 */
	public function rectangle($x,$y,$width,$height,$color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagerectangle($this->image_obj->getRes(),$x,$y,$x+$width,$y+$height,$gd_color);
	}
	/**
	 * @param int $center_x
	 * @param int $center_y
	 * @param int $width
	 * @param int $height
	 * @param mixed $color
	 * @return bool
	 */
	public function ellipse($center_x, $center_y, $width, $height, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imageellipse($this->image_obj->getRes(), $center_x, $center_y, $width, $height, $gd_color);
	}
	public function filledEllipse($center_x, $center_y, $width, $height, $color)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		return imagefilledellipse($this->image_obj->getRes(), $center_x, $center_y, $width, $height, $gd_color);
	}
#---下面是静态成员函数,可直接调用----------------------------------------------------------
	/**
	 * Give the bounding box of a text using TrueType fonts
	 * @param float $size
	 * @param float $angle
	 * @param string $font_name
	 * @param string $text
	 * @return array
	 */
	public static function ttfBBox($size,$angle,$font_name,$text)
	{
		$arr = imagettfbbox($size, $angle, Graphics::fontfilename($font_name), Graphics::textConv($text));
		// 左下角
		$lbx = $arr[0]; $lby = $arr[1];
		// 右下角
		$rbx = $arr[2]; $rby = $arr[3];
		// 右上角
		$rtx = $arr[4]; $rty = $arr[5];
		// 左上角
		$ltx = $arr[6]; $lty = $arr[7];

		function point_distance($x1,$y1,$x2,$y2)
		{
			return sqrt(pow($x2-$x1,2)+pow($y2-$y1,2));
		}

		// 上边宽 公式 两点距离公式 sqrt((x2-x1)^2+(y2-y1)^2)
		//sqrt(pow($rtx-$ltx,2)+pow($rty-$lty,2));
		$arr['topwidth']=point_distance($ltx, $lty, $rtx, $rty);
		$arr['bottomwidth']=point_distance($lbx, $lby, $rbx, $rby);
		$arr['leftheight']=point_distance($ltx, $lty, $lbx, $lby);
		$arr['rightheight']=point_distance($rtx, $rty, $rbx, $rby);
		$arr['width']=($arr['topwidth']+$arr['bottomwidth'])/2;
		$arr['height']=($arr['leftheight']+$arr['rightheight'])/2;
		$arr['x']=$ltx;
		$arr['y']=$lty;
		return $arr;
	}
	/**
	 * 文本编码转换
	 * 请填写Graphics::$charset_conv数组的encoding_from和encoding_to.
	 * @param string $text
	 * @return string
	 */
	public static function textConv($text)
	{
		if (strtoupper(mb_detect_encoding($text)) == 'UTF-8')
			return $text;
		else
			return iconv(config('page_charset'), 'UTF-8//IGNORE', $text);
	}
	/**
	 * {字体名->字体文件}映射
	 * 请填写Graphics::$fontfiles数组和Graphics::$fontDir变量.
	 * @param string[optional] $font_name
	 * @return string
	 */
	public static function fontfilename($font_name = '宋体')
	{
		if (empty(Graphics::$fontfiles[$font_name])) $font_name = '宋体';
		return Graphics::$fontDir.Graphics::$fontfiles[$font_name];
	}
}
/* 在这里填写一些变量 */
Graphics::$fontDir = dirname(__FILE__).'/fonts/';

/**
 * 图片类
 * version 1.1.0
 */
class Image
{
	// 图片类型to扩展名映射
	public static $image_type = array(
		IMAGETYPE_GIF => 'gif', 
		IMAGETYPE_JPEG => 'jpeg', 
		IMAGETYPE_PNG => 'png', 
		IMAGETYPE_WBMP => 'wbmp'
	);
	// 函数to类型映射
	public static $func_type = array(
		'gif' => IMAGETYPE_GIF, 
		'jpeg' => IMAGETYPE_JPEG, 
		'png' => IMAGETYPE_PNG, 
		'wbmp' => IMAGETYPE_WBMP,
		'2wbmp' => IMAGETYPE_WBMP,
		'gd' => 0,
		'gd2' => 0
	);
	/**
	 * @var int
	 */
	public $width = 0;        # 宽度 px
	/**
	 * @var int
	 */
	public $height = 0;       # 高度 px
	/**
	 * @var int
	 */
	public $type = 0;         # 图片类型
	/**
	 * @var string
	 */
	public $whstr = null;     # 宽高字符串
	/**
	 * @var int
	 */
	public $bits = 0;
	/**
	 * @var int
	 */
	public $channels = 0;
	/**
	 * @var string
	 */
	public $mime = null;
	/**
	 * @var array
	 */
	protected $imageinfo = null,$advanceinfo = null;
	/**
	 * @var string
	 */
	protected $filename = null;      # 图片文件名
	/**
	 * @var resource
	 */
	protected $image_res = null;     # 图片资源句柄
/**********************************************/
	/**
	 * 构造函数
	 * @param mixed $imageSource 图片源,为有效路径或资源时,忽略宽/高参数
	 * @param int $width 宽
	 * @param int $height 高
	 */
	public function Image($imageSource = null, $width = 100, $height = 100)
	{
		if ($imageSource !== null)
		{
			if (is_string($imageSource))
			{
				if ($imageSource != '')
					$this->createFromFile($imageSource);
				else
					$this->createTrueColor($width, $height);
			}
			elseif (is_resource($imageSource))
			{
				$this->image_res = $imageSource;
			}
		}
	}
	public function __destruct()
	{
		$this->destroy();
	}
	/**
	 * @return resource
	 */
	public function getRes(){ return $this->image_res;}
	/**
	 * 创建并重置大小
	 * @param int $width
	 * @param int $height
	 * @param int $resizeType 1反锯齿,2不反锯齿
	 */
	public function resize($width, $height, $resizeType = 1)
	{
		$destIm = new Image('', $width, $height);
		$g = new Graphics($destIm);
		$g->fill(0,0,Color::from($destIm, null, true));
		switch ($resizeType)
		{
		case 1:
			$destIm->copyResampled($this,0,0,$this->width,$this->height,0,0,$width,$height);
			break;
		case 2:
			$destIm->copyResized($this,0,0,$this->width,$this->height,0,0,$width,$height);
			break;
		}
		$this->destroy();
		$this->image_res = $destIm->image_res;
		$this->width = $destIm->width;        # 宽度 px
		$this->height = $destIm->height;       # 高度 px
		$this->type = $destIm->type;         # 图片类型
		$this->whstr = $destIm->whstr;     # 宽高字符串
		$this->bits = $destIm->bits;
		$this->channels = $destIm->channels;
		$this->mime = $destIm->mime;
		$this->imageinfo = $destIm->imageinfo;
		$this->advanceinfo = $destIm->advanceinfo;
		$this->filename = $destIm->filename;      # 图片文件名

		$destIm->image_res = null;
	}
	/**
	 * @param string $filename
	 * @param string[optional] $type
	 */
	public function createFromFile($filename, $type = null)
	{
		$this->destroy();
		$this->filename = $filename;
		$this->imageinfo = getimagesize($this->filename, $this->advanceinfo);
		$this->width = $this->imageinfo[0];
		$this->height = $this->imageinfo[1];
		$this->type = $this->imageinfo[2];
		$this->whstr = $this->imageinfo[3];
		$this->bits = isset($this->imageinfo['bits']) ? $this->imageinfo['bits'] : null;
		$this->channels = isset($this->imageinfo['channels']) ? $this->imageinfo['channels'] : null;
		$this->mime = isset($this->imageinfo['mime']) ? $this->imageinfo['mime'] : null;
		$imagecreate = 'imagecreatefrom'.($type == null ? Image::$image_type[$this->type] : $type);
		$this->image_res = $imagecreate($this->filename);
	}
	/**
	 * @param int $width
	 * @param int $height
	 */
	public function create($width, $height)
	{
		$this->destroy();
		$this->image_res = imagecreate($width, $height);
		$this->width = $width;
		$this->height = $height;
		$this->whstr = "width=\"$width\" height=\"$height\"";
		$this->type = IMAGETYPE_GIF;
	}
	/**
	 * 创建真彩色图
	 * @param int $width
	 * @param int $height
	 */
	public function createTrueColor($width, $height)
	{
		$this->destroy();
		$this->image_res = imagecreatetruecolor($width, $height);
		$this->width = $width;
		$this->height = $height;
		$this->whstr = "width=\"$width\" height=\"$height\"";
		$this->type = IMAGETYPE_GIF;
	}
	/**
	 * Should antialias functions be used or not
	 */
	public function antialias($enabled)
	{
		return imageantialias($this->image_res, $enabled);
	}
	/**
	 * Set the blending mode for an image
	 */
	public function alphaBlending($blendMode)
	{
		return imagealphablending($this->image_res, $blendMode);
	}
	public function colorAt($x, $y)
	{
		return imagecolorat($this->image_res, $x, $y);
	}
	public function colorsForIndex($index)
	{
		return imagecolorsforindex($this->image_res, $index);
	}
	public function colorsTotal()
	{
		return imagecolorstotal($this->image_res);
	}
	/**
	 * 申请颜色
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @return int
	 */
	public function colorAllocate($red,$green,$blue)
	{
		return imagecolorallocate($this->image_res,$red,$green,$blue);
	}
	/**
	 * 申请带alpha的颜色
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @param int $alpha
	 * @return int
	 */
	public function colorAllocateAlpha($red,$green,$blue,$alpha)
	{
		return imagecolorallocatealpha($this->image_res,$red,$green,$blue,$alpha);
	}
	/**
	 * 指定的颜色是透明色
	 * @param int[optional] $gd_color
	 * @return int
	 */
	public function colorTransparent($gd_color = null)
	{
		if ($gd_color === null)
			return imagecolortransparent($this->image_res);
		return imagecolortransparent($this->image_res, $gd_color);
	}
	/**
	 * 拷贝部分.
	 */
	public function copy(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y)
	{
		return imagecopy($this->image_res,$image_src->getRes(),$x,$y,$src_x,$src_y,$src_width,$src_height);
	}
	/**
	 * 拷贝并重置大小,不消除锯齿,速度快.
	 * @param Image $image_src
	 * @param int $src_x
	 * @param int $src_y
	 * @param int $src_width
	 * @param int $src_height
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	public function copyResized(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y,$width,$height)
	{
		return imagecopyresized($this->image_res,$image_src->getRes(),$x,$y,$src_x,$src_y,$width,$height,$src_width,$src_height);
	}
	/**
	 * 拷贝并重置大小,消除锯齿,速度慢.
	 * @param Image $image_src
	 * @param int $src_x
	 * @param int $src_y
	 * @param int $src_width
	 * @param int $src_height
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return bool
	 */
	public function copyResampled(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y,$width,$height)
	{
		//$this->type = $image_src->type;
		return imagecopyresampled($this->image_res,$image_src->getRes(),$x,$y,$src_x,$src_y,$width,$height,$src_width,$src_height);
	}
	/**
	 * 画出图象
	 * @param string[optional] $filename
	 * @param string[optional] $type
	 */
	public function draw($filename = null,$type = null)
	{
		//$this->filename = $filename;
		$outtype = $type != null ? $type : Image::$image_type[$this->type];
		if ($filename == null)  // 表示要在Web上输出
		{
			$imageType = Image::$func_type[$outtype];
			if ($imageType)
			{
				$header = 'Content-type: '.image_type_to_mime_type($imageType);
				header($header);
			}
		}
		$imagedraw = 'image'.$outtype;
		$filename == null ? $imagedraw($this->image_res) : $imagedraw($this->image_res,$filename);
	}
	public function destroy()
	{
		if ($this->image_res != null)
		{
			imagedestroy($this->image_res);
			$this->width = 0;        # 宽度 px
			$this->height = 0;       # 高度 px
			$this->type = 0;         # 图片类型
			$this->whstr = null;     # 宽高字符串
			$this->bits = 0;
			$this->channels = 0;
			$this->mime = null;
			$this->imageinfo = null;
			$this->advanceinfo = null;
			$this->filename = null;      # 图片文件名
			$this->image_res = null;     # 图片资源句柄
		}
	}
}
?>
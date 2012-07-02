<?php
//if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_GD_CLASS', 'gd/gd.class.php');

/**	此模块封装一些GD库函数
 *	@package Eien.Web.Graphics
 *	@author WaiTing
 *	@version 1.1.0 */

/**	Graphics配置类
 *	@author wt */
class GraphicsConfig
{
	/**	页面编码
	 *	@var string */
	public static $page_charset = 'UTF-8';
	/**	文本转换为GD库可以识别的编码,请填写GraphicsConfig::$page_charset
	 *	@param $text string
	 *	@return string */
	public static function textConv($text)
	{
		if (strtoupper(mb_detect_encoding($text)) == 'UTF-8')
			return $text;
		else
			return iconv(self::$page_charset, 'UTF-8//IGNORE', $text);
	}
	public static $fonts = array(); # array( font_name => font_file );
	public static function updateFonts($fonts)
	{
		foreach ( $fonts as $fontName => $value )
		{
			if ( is_string($value) )
			{
				self::$fonts[$fontName] = $value;
			}
			else
			{
				unset(self::$fonts[$fontName]);
			}
			
		}
	}
	/**	{字体名->字体文件}映射,请填写GraphicsConfig::$fonts数组.
	 *	@param $fontName string[optional]
	 *	@return string */
	public static function fontfilename($fontName = '宋体')
	{
		if (empty(self::$fonts[$fontName])) $fontName = '宋体';
		return self::$fonts[$fontName];
	}
	public static function _init()
	{
		self::updateFonts(array(
			'宋体' => dirname(__FILE__).'/fonts/simsun.ttc',
			'华文形楷' => dirname(__FILE__).'/fonts/stxingka.ttf',
			'隶书' => dirname(__FILE__).'/fonts/simli.ttf',
			'文泉驿微米黑' => dirname(__FILE__).'/fonts/wqy-microhei.ttc',
			'文泉驿正黑' => dirname(__FILE__).'/fonts/wqy-zenhei.ttc',
		));
	}
}
GraphicsConfig::_init();

/**
 * 输出中文
 * @param $im resource 图片资源柄
 * @param $size float 大小
 * @param $angle float 偏转角度
 * @param $x int
 * @param $y int
 * @param $color int 分配的gd颜色索引
 * @param $font_file string TrueType字体文件
 * @param $text string 要输出的字符串(会根据配置自动转换为utf-8)
 * @return array 返回一个含有 8 个单元的数组表示了文本外框的四个角，顺序为左下角，右下角，右上角，左上角。这些点是相对于文本的而和角度无关，因此“左上角”指的是以水平方向看文字时其左上角。
 */
function imageTTFTextOut($im,$size,$angle,$x,$y,$color,$font_file,$text)
{
	return imagettftext($im,$size,$angle,$x,$y,$color,$font_file,GraphicsConfig::textConv($text));
}
/**
 * 输出中文
 * @param $im resource 图片资源柄
 * @param $size float 大小
 * @param $angle float 偏转角度
 * @param $x int
 * @param $y int
 * @param $color int 分配的gd颜色索引
 * @param $font_name string 已配置的TrueType字体名(需要调用GraphicsConfig::updateFonts配置)
 * @param $text string 要输出的字符串(会根据配置自动转换为utf-8)
 * @return array 返回一个含有 8 个单元的数组表示了文本外框的四个角，顺序为左下角，右下角，右上角，左上角。这些点是相对于文本的而和角度无关，因此“左上角”指的是以水平方向看文字时其左上角。
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
	/**	分配的颜色
	 *	@var int */
	public $gd_color = null;
	/**	图片对象
	 *	@var Image */
	public $image = null;
	
	/**
	 * 颜色构造函数
	 * @param $image Image
	 * @param $red int
	 * @param $green int
	 * @param $blue int
	 * @param $mixed bool/int 若为true,则指定透明色;若为false或者数字,则分配alpha色;默认是null分配普通色
	 */
	public function __construct(Image $image, $red = null, $green = null, $blue = null, $mixed = null)
	{
		$this->image = $image;
		$this->alloc($red, $green, $blue, $mixed);
	}
	/**
	 * 分配颜色
	 * @param $red int
	 * @param $green int
	 * @param $blue int
	 * @param $mixed bool/int 若为true,则指定透明色;若为false或者数字,则分配alpha色;默认是null分配普通色
	 * @return gd_color
	 */
	public function alloc($red = null, $green = null, $blue = null, $mixed = null)
	{
		if (!($this->image instanceof Image)) return $this->gd_color;

		if ($mixed !== null) // 非普通色
		{
			if ($mixed === true) // 指定透明
			{
				if ($red !== null)
				{
					$color = imagecolorallocate($this->image->getRes(), $red, $green, $blue);
					$this->gd_color = imagecolortransparent($this->image->getRes(),$color);
				}
				else
				{
					$this->gd_color = imagecolortransparent($this->image->getRes());
				}
			}
			else // alpha
			{
				$this->gd_color = imagecolorallocatealpha($this->image->getRes(), $red, $green, $blue, $mixed % 128);
			}
		}
		else // 普通色
		{
			if ($red !== null)
			{
				$this->gd_color = imagecolorallocate($this->image->getRes(), $red, $green, $blue);
				//var_dump($red, $green, $blue, $this->gd_color);
			}
		}
		return $this->gd_color;
	}
	/**
	 * 分配颜色
	 * @param $color string/int 颜色,可以是字符串#XXXXXX,亦可是数字0xXXXXXX
	 * @param $mixed bool/int 若为true,则指定透明色;若为false或者数字,则分配alpha色;默认是null分配普通色
	 * @return gd_color GD颜色索引 */
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
	/**
	 * 分配颜色
	 * @param $image Image 要使用颜色的目标图片对象
	 * @param $color string/int 颜色,可以是字符串#XXXXXX,亦可是数字0xXXXXXX
	 * @param $mixed bool/int 若为true,则指定透明色;若为false或者数字,则分配alpha色;默认是null分配普通色
	 * @return Color */
	public static function from(Image $image, $color, $mixed = null)
	{
		$clrObj = new Color($image);
		$clrObj->allocColor($color,$mixed);
		return $clrObj;
	}
	/** 指定透明色 */
	public static function transparent(Image $image, $color = null)
	{
		return self::from($image, $color, true);
	}
	/** 分配alpha色 */
	public static function alpha(Image $image, $color, $alpha = 0)
	{
		return self::from($image, $color, $alpha);
	}
	/** 分配普通色 */
	public static function common(Image $image, $color)
	{
		return self::from($image, $color, null);
	}
	/**
	 * 分离RGB颜色,颜色用int指定,调用此函数分离成3个分量R,G,B.
	 * @param $int_rgb int
	 * @param $red int&
	 * @param $green int&
	 * @param $blue int&
	 */
	public static function splitRGB($int_rgb, &$red, &$green, &$blue)
	{
		$red = $int_rgb & 0xff;
		$green = ($int_rgb >> 8) & 0xff;
		$blue = ($int_rgb >> 16) & 0xff;
	}
	/**
	 * 分离RGB颜色,颜色用#FFFFFF/#FFF的方式指定,此函数分离其为3个分量R,G,B.
	 * @param $htmlColor string
	 * @param $red int&
	 * @param $green int&
	 * @param $blue int&
	 */
	public static function splitHtmlColor($htmlColor, &$red, &$green, &$blue)
	{
		if (preg_match('@^#?[0-9A-Fa-f]+$@', $htmlColor))
		{
			$html_color = str_replace('#','',$htmlColor);
			$len = strlen($html_color);
			switch ($len)
			{
			case 3:
				$red = (int)hexdec($html_color[0].$html_color[0]);
				$green = (int)hexdec($html_color[1].$html_color[1]);
				$blue = (int)hexdec($html_color[2].$html_color[2]);
				break;
			case 6:
				$red = (int)hexdec($html_color[0].$html_color[1]);
				$green = (int)hexdec($html_color[2].$html_color[3]);
				$blue = (int)hexdec($html_color[4].$html_color[5]);
				break;
			default:
				die('HtmlColor["'.$htmlColor.'"]\'s length is error, don\'t be converted!');
				break;
			}
		}
		else
		{
			$namedColors = array(
				"aliceblue"=>"#F0F8FF","antiquewhite"=>"#FAEBD7",
				"aqua"=>"#00FFFF","aquamarine"=>"#7FFFD4",
				"azure"=>"#F0FFFF","beige"=>"#F5F5DC",
				"bisque"=>"#FFE4C4","black"=>"#000000",
				"blanchedalmond"=>"#FFEBCD","blue"=>"#0000FF",
				"blueviolet"=>"#8A2BE2","brown"=>"#A52A2A",
				"burlywood"=>"#DEB887","cadetblue"=>"#5F9EA0",
				"chartreuse"=>"#7FFF00","chocolate"=>"#D2691E",
				"coral"=>"#FF7F50","cornflowerblue"=>"#6495ED",
				"cornsilk"=>"#FFF8DC","crimson"=>"#DC143C",
				"cyan"=>"#00FFFF","darkblue"=>"#00008B",
				"darkcyan"=>"#008B8B","darkgoldenrod"=>"#B8860B",
				"darkgray"=>"#A9A9A9","darkgreen"=>"#006400",
				"darkkhaki"=>"#BDB76B","darkmagenta"=>"#8B008B",
				"darkolivegreen"=>"#556B2F","darkorange"=>"#FF8C00",
				"darkorchid"=>"#9932CC","darkred"=>"#8B0000",
				"darksalmon"=>"#E9967A","darkseagreen"=>"#8FBC8B",
				"darkslateblue"=>"#483D8B","darkslategray"=>"#2F4F4F",
				"darkturquoise"=>"#00CED1","darkviolet"=>"#9400D3",
				"deeppink"=>"#FF1493","deepskyblue"=>"#00BFFF",
				"dimgray"=>"#696969","dodgerblue"=>"#1E90FF",
				"firebrick"=>"#B22222","floralwhite"=>"#FFFAF0",
				"forestgreen"=>"#228B22","Fuchsia"=>"#FF00FF",
				"gainsboro"=>"#DCDCDC","ghostwhite"=>"#F8F8FF",
				"gold"=>"#FFD700","goldenrod"=>"#DAA520",
				"gray"=>"#808080","green"=>"#008000",
				"greenyellow"=>"#ADFF2F","honeydew"=>"#F0FFF0",
				"hotpink"=>"#FF69B4","indianred"=>"#CD5C5C",
				"indigo"=>"#4B0082","ivory"=>"#FFFFF0",
				"khaki"=>"#F0E68C","lavender"=>"#E6E6FA",
				"lavenderblush"=>"#FFF0F5","lawngreen"=>"#7CFC00",
				"lemonchiffon"=>"#FFFACD","lightblue"=>"#ADD8E6",
				"lightcoral"=>"#F08080","lightcyan"=>"#E0FFFF",
				"lightgoldenrodyellow"=>"#FAFAD2","lightgreen"=>"#90EE90",
				"lightgrey"=>"#D3D3D3","lightpink"=>"#FFB6C1",
				"lightsalmon"=>"#FFA07A","lightseagreen"=>"#20B2AA",
				"lightskyblue"=>"#87CEFA","lightslategray"=>"#778899",
				"lightsteelblue"=>"#B0C4DE","lightyellow"=>"#FFFFE0",
				"lime"=>"#00FF00","limegreen"=>"#32CD32",
				"linen"=>"#FAF0E6","magenta"=>"#FF00FF",
				"maroon"=>"#800000","mediumaquamarine"=>"#66CDAA",
				"mediumblue"=>"#0000CD","mediumorchid"=>"#BA55D3",
				"mediumpurple"=>"#9370DB","mediumseagreen"=>"#3CB371",
				"mediumslateblue"=>"#7B68EE","mediumspringgreen"=>"#00FA9A",
				"mediumturquoise"=>"#48D1CC","mediumvioletred"=>"#C71585",
				"midnightblue"=>"#191970","mintcream"=>"#F5FFFA",
				"mistyrose"=>"#FFE4E1","moccasin"=>"#FFE4B5",
				"navajowhite"=>"#FFDEAD","navy"=>"#000080",
				"oldlace"=>"#FDF5E6","olive"=>"#808000",
				"olivedrab"=>"#6B8E23","orange"=>"#FFA500",
				"orangered"=>"#FF4500","orchid"=>"#DA70D6",
				"palegoldenrod"=>"#EEE8AA","palegreen"=>"#98FB98",
				"paleturquoise"=>"#AFEEEE","palevioletred"=>"#DB7093",
				"papayawhip"=>"#FFEFD5","peachpuff"=>"#FFDAB9",
				"peru"=>"#CD853F","pink"=>"#FFC0CB",
				"plum"=>"#DDA0DD","powderblue"=>"#B0E0E6",
				"purple"=>"#800080","red"=>"#FF0000",
				"rosybrown"=>"#BC8F8F","royalblue"=>"#4169E1",
				"saddlebrown"=>"#8B4513","salmon"=>"#FA8072",
				"sandybrown"=>"#F4A460","seagreen"=>"#2E8B57",
				"seashell"=>"#FFF5EE","sienna"=>"#A0522D",
				"silver"=>"#C0C0C0","skyblue"=>"#87CEEB",
				"slateblue"=>"#6A5ACD","slategray"=>"#708090",
				"snow"=>"#FFFAFA","springgreen"=>"#00FF7F",
				"steelblue"=>"#4682B4","tan"=>"#D2B48C",
				"teal"=>"#008080","thistle"=>"#D8BFD8",
				"tomato"=>"#FF6347","turquoise"=>"#40E0D0",
				"violet"=>"#EE82EE","wheat"=>"#F5DEB3",
				"white"=>"#FFFFFF","whitesmoke"=>"#F5F5F5",
				"yellow"=>"#FFFF00","yellowgreen"=>"#9ACD32",
			);
			self::splitHtmlColor($namedColors[$htmlColor], $red, $green, $blue);
		}
	}
}

//--------------------------------------------------------
/**
 * 绘图类,绑定图片对象后才能使用
 * version 1.1.0
 */
class Graphics
{
	/**	Image Object
	 *	@var Image */
	private $imageObj = null;
	/** 构造函数
	 * @param $im_obj Image[optional]
	 * @return Graphics */
	public function __construct(Image $im = null)
	{
		$this->bindImage($im);
	}
	/**
	 * @param Image $im */
	public function bindImage(Image $im)
	{
		$this->imageObj = $im;
	}
	/** 解析颜色参数到gd_color
	 * @param $color Color/string/int 颜色
	 * @param $mixed bool/int
	 * @return gd_color
	 */
	private function _colorParam($color, $mixed = null)
	{
		$gd_color = $color;
		if ($color instanceof Color)
		{
			$gd_color = $color->gd_color;
		}
		else // string or int
		{
			$color = Color::from($this->imageObj, $color, $mixed);
			$gd_color = $color->gd_color;
		}
		return $gd_color;
	}
	/** 输出中文
	 * @param $size float 大小
	 * @param $angle float 偏转角度
	 * @param $x int
	 * @param $y int
	 * @param $color Color/string/int 颜色
	 * @param $fontfile string TrueType字体文件
	 * @param $text string 要输出的字符串(会根据配置自动转换为utf-8)
	 * @return array 返回一个含有8个单元的数组表示了文本外框的四个角，顺序为左下角，右下角，右上角，左上角。这些点是相对于文本的而和角度无关，因此“左上角”指的是以水平方向看文字时其左上角。
	 */
	public function ttfTextOut($size,$angle,$x,$y,$color,$fontfile,$text)
	{
		return imagettftext($this->imageObj->getRes(),$size,$angle,$x,$y,$this->_colorParam($color),$fontfile,GraphicsConfig::textConv($text));
	}
	/** 输出中文 */
	public function _textOut($size,$angle,$x,$y,$color,$fontname,$text)
	{
		return $this->ttfTextOut($size,$angle,$x,$y,$color,GraphicsConfig::fontfilename($fontname),$text);
	}
	/** 输出中文,并修正坐标
	 * @param $size float 大小
	 * @param $angle float 偏转角度
	 * @param $x int
	 * @param $y int
	 * @param $color Color/string/int 颜色
	 * @param $fontname string 已配置的TrueType字体名(需要调用GraphicsConfig::updateFonts配置)
	 * @param $text string 要输出的字符串
	 * @return array 返回一个含有 8 个单元的数组表示了文本外框的四个角，顺序为左下角，右下角，右上角，左上角。这些点是相对于文本的而和角度无关，因此“左上角”指的是以水平方向看文字时其左上角。
	 */
	public function textOut($size,$angle,$x,$y,$color,$fontname,$text)
	{
		$bound = self::ttfBBox($size, $angle, $fontname, $text);
		return $this->_textOut($size,$angle,$x-$bound['x'],$y-$bound['y'],$color,$fontname,$text);
	}
	/**输出英文*/
	public function drawEnglish($fontInx, $x, $y, $str, $color)
	{
		return imagestring($this->imageObj->getRes(), $fontInx, $x, $y, $str, $this->_colorParam($color));
	}
	/** 用颜色填充背景 */
	public function fillBackground($color)
	{
		return $this->filledRectangle(0, 0, $this->imageObj->width, $this->imageObj->height, $color);
	}
	/**
	 * 在指定坐标用指定颜色填充
	 * @param $x int
	 * @param $y int
	 * @param $color Color/string/int
	 * @return bool
	 */
	public function fill($x,$y,$color)
	{
		return imagefill($this->imageObj->getRes(),$x,$y,$this->_colorParam($color));
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
		return imagefilledrectangle($this->imageObj->getRes(),$x,$y,$x+$width-1,$y+$height-1,$this->_colorParam($color));
	}
	public function fillToBorder($x, $y, $borderColor, $color)
	{
		return imagefilltoborder($this->imageObj->getRes(),$x,$y,$this->_colorParam($borderColor),$this->_colorParam($color));
	}
	public function arc($center_x, $center_y, $width, $height, $start, $end, $color)
	{
		return imagearc($this->imageObj->getRes(), $center_x, $center_y, $width, $height, $start, $end, $this->_colorParam($color));
	}
	public function line($x1, $y1, $x2, $y2, $color)
	{
		return imageline($this->imageObj->getRes(), $x1, $y1, $x2, $y2, $this->_colorParam($color));
	}
	public function dashedLine($x1, $y1, $x2, $y2, $color)
	{
		return imagedashedline($this->imageObj->getRes(), $x1, $y1, $x2, $y2, $this->_colorParam($color));
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
		return imagerectangle($this->imageObj->getRes(),$x,$y,$x+$width-1,$y+$height-1,$this->_colorParam($color));
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
		return imageellipse($this->imageObj->getRes(), $center_x, $center_y, $width, $height, $this->_colorParam($color));
	}
	public function filledEllipse($center_x, $center_y, $width, $height, $color)
	{
		return imagefilledellipse($this->imageObj->getRes(), $center_x, $center_y, $width, $height, $this->_colorParam($color));
	}
#---下面是静态成员函数,可直接调用----------------------------------------------------------
	/**	获得一个用TrueType字体输出文本的边界框
	 * @param float $size
	 * @param float $angle
	 * @param string $fontname
	 * @param string $text
	 * @return array */
	public static function ttfBBox($size,$angle,$fontname,$text)
	{
		$arr = imagettfbbox($size, $angle, GraphicsConfig::fontfilename($fontname), GraphicsConfig::textConv($text));
		// 左下角
		$lbx = $arr[0]; $lby = $arr[1];
		// 右下角
		$rbx = $arr[2]; $rby = $arr[3];
		// 右上角
		$rtx = $arr[4]; $rty = $arr[5];
		// 左上角
		$ltx = $arr[6]; $lty = $arr[7];
		
		// 获得平行与屏幕的外接矩形
		// 左角上
		$x_lt = min(array($lbx,$rbx,$rtx,$ltx));
		$y_lt = min(array($lby,$rby,$rty,$lty));
		// 右下角
		$x_rb = max(array($lbx,$rbx,$rtx,$ltx));
		$y_rb = max(array($lby,$rby,$rty,$lty));

		$arr['x'] = $x_lt;
		$arr['y'] = $y_lt;
		$arr['width'] = $x_rb - $x_lt + 1;
		$arr['height'] = $y_rb - $y_lt + 1;
		/*// 上边宽 公式 两点距离公式 sqrt((x2-x1)^2+(y2-y1)^2)
		//sqrt(pow($rtx-$ltx,2)+pow($rty-$lty,2));
		//$arr = array();
		$arr['topwidth']=$rtx-$ltx+1;//self::point_distance($ltx, $lty, $rtx, $rty);
		$arr['bottomwidth']=$rbx-$lbx+1;//self::point_distance($lbx, $lby, $rbx, $rby);
		$arr['leftheight']=$lby-$lty+1;//self::point_distance($ltx, $lty, $lbx, $lby);
		$arr['rightheight']=$rby-$rty+1;//self::point_distance($rtx, $rty, $rbx, $rby);
		$arr['width']=($arr['topwidth']+$arr['bottomwidth'])/2;
		$arr['height']=($arr['leftheight']+$arr['rightheight'])/2;
		$arr['x']=$ltx;
		$arr['y']=$lty;//*/
		return $arr;
	}
	private static function point_distance($x1,$y1,$x2,$y2)
	{
		return sqrt(pow($x2-$x1,2)+pow($y2-$y1,2));
	}
}

/** 图片类
 * @version 1.1.0 */
class Image
{
	/** 图片类型 to MIME 映射 */
	public static $image_type = array(
		IMAGETYPE_GIF => 'gif', 
		IMAGETYPE_JPEG => 'jpeg', 
		IMAGETYPE_PNG => 'png', 
		IMAGETYPE_WBMP => 'wbmp'
	);
	/** 函数 to 图片类型映射 */
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
	 * @var int */
	public $width = 0,        # 宽度 px
	$height = 0,              # 高度 px
	$type = 0,                # 图片类型
	$bits = 0,
	$channels = 0;
	/** 宽高字符串
	 * @var string */
	public $whstr = null;
	/** MIME
	 * @var string */
	public $mime = null;
	/** 图片信息
	 * @var array */
	protected $imageinfo = null,$advanceinfo = null;
	/** 图片文件名
	 * @var string */
	protected $filename = null;
	/** 图片资源句柄
	 * @var resource */
	protected $imageRes = null;

	/**
	 * 构造函数
	 * @param $imageSource resource/string[optional] 图片源, 指定有效路径或资源时忽略宽/高参数; 指定空字符串则创建一张空图; 指定null则什么都不做. default = null
	 * @param $width int[optional] 宽度 default = 300
	 * @param $height int[optional] 高度 default = 200 */
	public function __construct($imageSource = null, $width = 300, $height = 200)
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
				$this->imageRes = $imageSource;
			}
		}
	}
	public function __destruct()
	{
		$this->destroy();
	}
	/**
	 * @return resource */
	public function getRes(){ return $this->imageRes; }
	/** 创建并重置大小
	 * @param $width int
	 * @param $height int
	 * @param $resizeType int[optional] default = 1 {1反锯齿,2不反锯齿} */
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
		$this->imageRes = $destIm->imageRes;
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

		$destIm->imageRes = null;
	}
	/** 从文件创建图片对象
	 * @param string $filename
	 * @param string[optional] $type 默认将自动获取图片类型 */
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
		$this->imageRes = $imagecreate($this->filename);
	}
	/**
	 * @param int $width
	 * @param int $height */
	public function create($width, $height)
	{
		$this->destroy();
		$this->imageRes = imagecreate($width, $height);
		$this->width = $width;
		$this->height = $height;
		$this->whstr = "width=\"$width\" height=\"$height\"";
		$this->type = IMAGETYPE_GIF;
	}
	/**
	 * 创建真彩色图
	 * @param int $width
	 * @param int $height */
	public function createTrueColor($width, $height)
	{
		$this->destroy();
		$this->imageRes = imagecreatetruecolor($width, $height);
		$this->width = $width;
		$this->height = $height;
		$this->whstr = "width=\"$width\" height=\"$height\"";
		$this->type = IMAGETYPE_GIF;
	}
	/** Should antialias functions be used or not */
	public function antialias($enabled)
	{
		return imageantialias($this->imageRes, $enabled);
	}
	/** Set the blending mode for an image */
	public function alphaBlending($blendMode)
	{
		return imagealphablending($this->imageRes, $blendMode);
	}
	public function colorAt($x, $y)
	{
		return imagecolorat($this->imageRes, $x, $y);
	}
	public function colorsForIndex($index)
	{
		return imagecolorsforindex($this->imageRes, $index);
	}
	public function colorsTotal()
	{
		return imagecolorstotal($this->imageRes);
	}
	/** 拷贝 */
	public function copy(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y)
	{
		return imagecopy($this->imageRes,$image_src->getRes(),$x,$y,$src_x,$src_y,$src_width,$src_height);
	}
	/** 拷贝并重置大小,不消除锯齿,速度快.
	 * @param Image $image_src
	 * @param int $src_x
	 * @param int $src_y
	 * @param int $src_width
	 * @param int $src_height
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return bool */
	public function copyResized(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y,$width,$height)
	{
		return imagecopyresized($this->imageRes,$image_src->getRes(),$x,$y,$src_x,$src_y,$width,$height,$src_width,$src_height);
	}
	/** 拷贝并重置大小,消除锯齿,速度慢.
	 * @param Image $image_src
	 * @param int $src_x
	 * @param int $src_y
	 * @param int $src_width
	 * @param int $src_height
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 * @return bool */
	public function copyResampled(Image $image_src,$src_x,$src_y,$src_width,$src_height,$x,$y,$width,$height)
	{
		return imagecopyresampled($this->imageRes,$image_src->getRes(),$x,$y,$src_x,$src_y,$width,$height,$src_width,$src_height);
	}
	/** 画出图象
	 * @param string[optional] $filename
	 * @param string[optional] $type 默认为创建时的类型gif */
	public function draw($filename = null,$type = null)
	{
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
		$filename == null ? $imagedraw($this->imageRes) : $imagedraw($this->imageRes,$filename);
	}
	/**	销毁图片资源 */
	public function destroy()
	{
		if ($this->imageRes != null)
		{
			imagedestroy($this->imageRes);
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
			$this->imageRes = null;     # 图片资源句柄
		}
	}
}

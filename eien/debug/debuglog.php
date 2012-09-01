<?php
/** 调试
 * @@dependency filesys/file.class.php */

define('EIEN_DEBUGLOG', 'debug/debuglog.php');

/** 调试日志 */
class debug
{
	private static $logpath;
	private static $debuglog;
	public static function _init()
	{
		self::$logpath = dirname(__FILE__) . '/debug.log';
	}
	public static function log( $str )
	{
		if ( !is_object(self::$debuglog) )
		{
			// @@use class File
			if ( file_exists(self::$logpath) )
				self::$debuglog =  new File( self::$logpath, 'a' );
			else
				self::$debuglog =  new File( self::$logpath, 'w' );
		}
		$micro = microtime(false);
		$micro = (float)substr( $micro, 0, strpos( $micro, ' ' ) );
		$micro = round( $micro, 3 );
		self::$debuglog->puts( $str . ' [' . date('Y-m-d\TH:i:s') . strstr( $micro, '.' ) . ']' . "\r\n" );
	}

}
debug::_init();
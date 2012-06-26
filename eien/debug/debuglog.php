<?
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_DEBUGLOG', 1);
/**
调试类
*/
$debugPath = EIEN_PATH.'debug/debug.log';

$debuglog = null;
$gdebugcount = 0;
function debugLog($str)
{
	global $debuglog,$gdebugcount,$debugPath;
	if ($debuglog == null)
	{
		//if (file_exists($debugPath))
		//	$debuglog =  new File($debugPath, 'a');
		//else
			$debuglog =  new File($debugPath, 'w');
	}

	$debuglog->puts(++$gdebugcount.' - '.$str."\r\n".''."\r\n");

}


?>
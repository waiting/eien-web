<?
/**
 * 辅助函数
 * @package Eien.Web.ExtraFunc
 * @author WaiTing
 * @version 1.1.0
 */
if (!defined('IN_EIEN')) exit("No in eien framework");
define('EIEN_EXTRA_FUNC', 1);
/**
 * FlashShow,用于向网页中嵌入FLASH插件,并提供一些常用操作
 * 输出遵循XHTML1.0
 */
function flash_show($mains, $params = array(), $flaVars = array())
{
	if (!is_array($mains))
	{
		return 'flash show 参数错误!';
	}
	$flash = isset($mains['flash']) ? $mains['flash'] : '';
	$cx = isset($mains['cx']) ? $mains['cx'] : 160;
	$cy = isset($mains['cy']) ? $mains['cy'] : 120;
	$id = isset($mains['id']) ? $mains['id'] : '';
	$other = isset($mains['other']) ? $mains['other'] : '';

	$params['movie'] = $flash;
	
	// 自定义参数
	// 透明
	if (isset($mains['transparent']))
	{
		if ($mains['transparent'])
		{
			$params['wmode'] = 'transparent';
		}
		else
		{
			$params['wmode'] = 'opaque';
		}
	}
	// 脚本访问权
	if (isset($mains['scriptAccess']))
	{
		$params['allowScriptAccess'] = $mains['scriptAccess'];
	}
	// 背景色
	if (isset($mains['bgcolor']))
	{
		$params['wmode'] = 'opaque';
		$params['bgcolor'] = $mains['bgcolor'];
	}


	$embed = '<embed' . ($id != '' ? ' name="' . $id . '"' : '') . ' src="' . $flash . '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="' . $cx . '" height="' . $cy . '" ';
	$s = '';
	$s .= '<object' . ($other !== '' ? ' '.$other : '') . ($id != '' ? ' id="' . $id . '"' : '') . ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="' . $cx . '" height="' . $cy . '">';

	$sFlaVars = '';
	$i = 0;
	foreach ($flaVars as $varname => $varvalue)
	{
		if ($i != 0)
		{
			$sFlaVars .= '&';
		}
		$sFlaVars .= $varname . '=' . $varvalue;
		$i++;
	}

	foreach ($params as $name => $value)
	{
		$s .= '<param name="' . $name . '" value="' . $value . '" />';
		$embed .= $name . '="' . $value . '" ';
	}

	if ($sFlaVars != '')
	{
		$s .= '<param name="FlashVars" value="' . $sFlaVars . '" />';
		$embed .= 'FlashVars="' . $sFlaVars . '" ';
	}

	$embed .= '/>';
	$s .= $embed;
	$s .= '</object>';

	return $s;
}

/**
 * get mime type
 */
$mimetypes = array(
	'3gp' => 'video/3gpp',
	'aab' => 'application/x-authoware-bin',
	'aam' => 'application/x-authoware-map',
	'aas' => 'application/x-authoware-seg',
	'ai' => 'application/postscript',
	'aif' => 'audio/x-aiff',
	'aifc' => 'audio/x-aiff',
	'aiff' => 'audio/x-aiff',
	'als' => 'audio/X-Alpha5',
	'amc' => 'application/x-mpeg',
	'ani' => 'application/octet-stream',
	'asc' => 'text/plain',
	'asd' => 'application/astound',
	'asf' => 'video/x-ms-asf',
	'asn' => 'application/astound',
	'asp' => 'application/x-asap',
	'asx' => 'video/x-ms-asf',
	'au' => 'audio/basic',
	'avb' => 'application/octet-stream',
	'avi' => 'video/x-msvideo',
	'awb' => 'audio/amr-wb',
	'bcpio' => 'application/x-bcpio',
	'bin' => 'application/octet-stream',
	'bld' => 'application/bld',
	'bld2' => 'application/bld2',
	'bmp' => 'application/x-MS-bmp',
	'bpk' => 'application/octet-stream',
	'bz2' => 'application/x-bzip2',
	'cal' => 'image/x-cals',
	'ccn' => 'application/x-cnc',
	'cco' => 'application/x-cocoa',
	'cdf' => 'application/x-netcdf',
	'cgi' => 'magnus-internal/cgi',
	'chat' => 'application/x-chat',
	'class' => 'application/octet-stream',
	'clp' => 'application/x-msclip',
	'cmx' => 'application/x-cmx',
	'co' => 'application/x-cult3d-object',
	'cod' => 'image/cis-cod',
	'cpio' => 'application/x-cpio',
	'cpt' => 'application/mac-compactpro',
	'crd' => 'application/x-mscardfile',
	'csh' => 'application/x-csh',
	'csm' => 'chemical/x-csml',
	'csml' => 'chemical/x-csml',
	'css' => 'text/css',
	'cur' => 'application/octet-stream',
	'dcm' => 'x-lml/x-evm',
	'dcr' => 'application/x-director',
	'dcx' => 'image/x-dcx',
	'dhtml' => 'text/html',
	'dir' => 'application/x-director',
	'dll' => 'application/octet-stream',
	'dmg' => 'application/octet-stream',
	'dms' => 'application/octet-stream',
	'doc' => 'application/msword',
	'dot' => 'application/x-dot',
	'dvi' => 'application/x-dvi',
	'dwf' => 'drawing/x-dwf',
	'dwg' => 'application/x-autocad',
	'dxf' => 'application/x-autocad',
	'dxr' => 'application/x-director',
	'ebk' => 'application/x-expandedbook',
	'emb' => 'chemical/x-embl-dl-nucleotide',
	'embl' => 'chemical/x-embl-dl-nucleotide',
	'eps' => 'application/postscript',
	'eri' => 'image/x-eri',
	'es' => 'audio/echospeech',
	'esl' => 'audio/echospeech',
	'etc' => 'application/x-earthtime',
	'etx' => 'text/x-setext',
	'evm' => 'x-lml/x-evm',
	'evy' => 'application/x-envoy',
	'exe' => 'application/octet-stream',
	'fh4' => 'image/x-freehand',
	'fh5' => 'image/x-freehand',
	'fhc' => 'image/x-freehand',
	'fif' => 'image/fif',
	'fm' => 'application/x-maker',
	'fpx' => 'image/x-fpx',
	'fvi' => 'video/isivideo',
	'gau' => 'chemical/x-gaussian-input',
	'gca' => 'application/x-gca-compressed',
	'gdb' => 'x-lml/x-gdb',
	'gif' => 'image/gif',
	'gps' => 'application/x-gps',
	'gtar' => 'application/x-gtar',
	'gz' => 'application/x-gzip',
	'hdf' => 'application/x-hdf',
	'hdm' => 'text/x-hdml',
	'hdml' => 'text/x-hdml',
	'hlp' => 'application/winhlp',
	'hqx' => 'application/mac-binhex40',
	'htm' => 'text/html',
	'html' => 'text/html',
	'hts' => 'text/html',
	'ice' => 'x-conference/x-cooltalk',
	'ico' => 'application/octet-stream',
	'ief' => 'image/ief',
	'ifm' => 'image/gif',
	'ifs' => 'image/ifs',
	'imy' => 'audio/melody',
	'ins' => 'application/x-NET-Install',
	'ips' => 'application/x-ipscript',
	'ipx' => 'application/x-ipix',
	'it' => 'audio/x-mod',
	'itz' => 'audio/x-mod',
	'ivr' => 'i-world/i-vrml',
	'j2k' => 'image/j2k',
	'jad' => 'text/vnd.sun.j2me.app-descriptor',
	'jam' => 'application/x-jam',
	'jar' => 'application/java-archive',
	'jnlp' => 'application/x-java-jnlp-file',
	'jpe' => 'image/jpeg',
	'jpeg' => 'image/jpeg',
	'jpg' => 'image/jpeg',
	'jpz' => 'image/jpeg',
	'js' => 'application/x-javascript',
	'jwc' => 'application/jwc',
	'kjx' => 'application/x-kjx',
	'lak' => 'x-lml/x-lak',
	'latex' => 'application/x-latex',
	'lcc' => 'application/fastman',
	'lcl' => 'application/x-digitalloca',
	'lcr' => 'application/x-digitalloca',
	'lgh' => 'application/lgh',
	'lha' => 'application/octet-stream',
	'lml' => 'x-lml/x-lml',
	'lmlpack' => 'x-lml/x-lmlpack',
	'lsf' => 'video/x-ms-asf',
	'lsx' => 'video/x-ms-asf',
	'lzh' => 'application/x-lzh',
	'm13' => 'application/x-msmediaview',
	'm14' => 'application/x-msmediaview',
	'm15' => 'audio/x-mod',
	'm3u' => 'audio/x-mpegurl',
	'm3url' => 'audio/x-mpegurl',
	'ma1' => 'audio/ma1',
	'ma2' => 'audio/ma2',
	'ma3' => 'audio/ma3',
	'ma5' => 'audio/ma5',
	'man' => 'application/x-troff-man',
	'map' => 'magnus-internal/imagemap',
	'mbd' => 'application/mbedlet',
	'mct' => 'application/x-mascot',
	'mdb' => 'application/x-msaccess',
	'mdz' => 'audio/x-mod',
	'me' => 'application/x-troff-me',
	'mel' => 'text/x-vmel',
	'mi' => 'application/x-mif',
	'mid' => 'audio/midi',
	'midi' => 'audio/midi',
	'mif' => 'application/x-mif',
	'mil' => 'image/x-cals',
	'mio' => 'audio/x-mio',
	'mmf' => 'application/x-skt-lbs',
	'mng' => 'video/x-mng',
	'mny' => 'application/x-msmoney',
	'moc' => 'application/x-mocha',
	'mocha' => 'application/x-mocha',
	'mod' => 'audio/x-mod',
	'mof' => 'application/x-yumekara',
	'mol' => 'chemical/x-mdl-molfile',
	'mop' => 'chemical/x-mopac-input',
	'mov' => 'video/quicktime',
	'movie' => 'video/x-sgi-movie',
	'mp2' => 'audio/x-mpeg',
	'mp3' => 'audio/x-mpeg',
	'mp4' => 'video/mp4',
	'mpc' => 'application/vnd.mpohun.certificate',
	'mpe' => 'video/mpeg',
	'mpeg' => 'video/mpeg',
	'mpg' => 'video/mpeg',
	'mpg4' => 'video/mp4',
	'mpga' => 'audio/mpeg',
	'mpn' => 'application/vnd.mophun.application',
	'mpp' => 'application/vnd.ms-project',
	'mps' => 'application/x-mapserver',
	'mrl' => 'text/x-mrml',
	'mrm' => 'application/x-mrm',
	'ms' => 'application/x-troff-ms',
	'mts' => 'application/metastream',
	'mtx' => 'application/metastream',
	'mtz' => 'application/metastream',
	'mzv' => 'application/metastream',
	'nar' => 'application/zip',
	'nbmp' => 'image/nbmp',
	'nc' => 'application/x-netcdf',
	'ndb' => 'x-lml/x-ndb',
	'ndwn' => 'application/ndwn',
	'nif' => 'application/x-nif',
	'nmz' => 'application/x-scream',
	'nokia-op-logo' => 'image/vnd.nok-oplogo-color',
	'npx' => 'application/x-netfpx',
	'nsnd' => 'audio/nsnd',
	'nva' => 'application/x-neva1',
	'oda' => 'application/oda',
	'oom' => 'application/x-AtlasMate-Plugin',
	'pac' => 'audio/x-pac',
	'pae' => 'audio/x-epac',
	'pan' => 'application/x-pan',
	'pbm' => 'image/x-portable-bitmap',
	'pcx' => 'image/x-pcx',
	'pda' => 'image/x-pda',
	'pdb' => 'chemical/x-pdb',
	'pdf' => 'application/pdf',
	'pfr' => 'application/font-tdpfr',
	'pgm' => 'image/x-portable-graymap',
	'pict' => 'image/x-pict',
	'pm' => 'application/x-perl',
	'pmd' => 'application/x-pmd',
	'png' => 'image/png',
	'pnm' => 'image/x-portable-anymap',
	'pnz' => 'image/png',
	'pot' => 'application/vnd.ms-powerpoint',
	'ppm' => 'image/x-portable-pixmap',
	'pps' => 'application/vnd.ms-powerpoint',
	'ppt' => 'application/vnd.ms-powerpoint',
	'pqf' => 'application/x-cprplayer',
	'pqi' => 'application/cprplayer',
	'prc' => 'application/x-prc',
	'proxy' => 'application/x-ns-proxy-autoconfig',
	'ps' => 'application/postscript',
	'ptlk' => 'application/listenup',
	'pub' => 'application/x-mspublisher',
	'pvx' => 'video/x-pv-pvx',
	'qcp' => 'audio/vnd.qcelp',
	'qt' => 'video/quicktime',
	'qti' => 'image/x-quicktime',
	'qtif' => 'image/x-quicktime',
	'r3t' => 'text/vnd.rn-realtext3d',
	'ra' => 'audio/x-pn-realaudio',
	'ram' => 'audio/x-pn-realaudio',
	'rar' => 'application/x-rar-compressed',
	'ras' => 'image/x-cmu-raster',
	'rdf' => 'application/rdf+xml',
	'rf' => 'image/vnd.rn-realflash',
	'rgb' => 'image/x-rgb',
	'rlf' => 'application/x-richlink',
	'rm' => 'audio/x-pn-realaudio',
	'rmf' => 'audio/x-rmf',
	'rmm' => 'audio/x-pn-realaudio',
	'rmvb' => 'audio/x-pn-realaudio',
	'rnx' => 'application/vnd.rn-realplayer',
	'roff' => 'application/x-troff',
	'rp' => 'image/vnd.rn-realpix',
	'rpm' => 'audio/x-pn-realaudio-plugin',
	'rt' => 'text/vnd.rn-realtext',
	'rte' => 'x-lml/x-gps',
	'rtf' => 'application/rtf',
	'rtg' => 'application/metastream',
	'rtx' => 'text/richtext',
	'rv' => 'video/vnd.rn-realvideo',
	'rwc' => 'application/x-rogerwilco',
	's3m' => 'audio/x-mod',
	's3z' => 'audio/x-mod',
	'sca' => 'application/x-supercard',
	'scd' => 'application/x-msschedule',
	'sdf' => 'application/e-score',
	'sea' => 'application/x-stuffit',
	'sgm' => 'text/x-sgml',
	'sgml' => 'text/x-sgml',
	'sh' => 'application/x-sh',
	'shar' => 'application/x-shar',
	'shtml' => 'magnus-internal/parsed-html',
	'shw' => 'application/presentations',
	'si6' => 'image/si6',
	'si7' => 'image/vnd.stiwap.sis',
	'si9' => 'image/vnd.lgtwap.sis',
	'sis' => 'application/vnd.symbian.install',
	'sit' => 'application/x-stuffit',
	'skd' => 'application/x-Koan',
	'skm' => 'application/x-Koan',
	'skp' => 'application/x-Koan',
	'skt' => 'application/x-Koan',
	'slc' => 'application/x-salsa',
	'smd' => 'audio/x-smd',
	'smi' => 'application/smil',
	'smil' => 'application/smil',
	'smp' => 'application/studiom',
	'smz' => 'audio/x-smd',
	'snd' => 'audio/basic',
	'spc' => 'text/x-speech',
	'spl' => 'application/futuresplash',
	'spr' => 'application/x-sprite',
	'sprite' => 'application/x-sprite',
	'spt' => 'application/x-spt',
	'src' => 'application/x-wais-source',
	'stk' => 'application/hyperstudio',
	'stm' => 'audio/x-mod',
	'sv4cpio' => 'application/x-sv4cpio',
	'sv4crc' => 'application/x-sv4crc',
	'svf' => 'image/vnd',
	'svg' => 'image/svg-xml',
	'svh' => 'image/svh',
	'svr' => 'x-world/x-svr',
	'swf' => 'application/x-shockwave-flash',
	'swfl' => 'application/x-shockwave-flash',
	't' => 'application/x-troff',
	'tad' => 'application/octet-stream',
	'talk' => 'text/x-speech',
	'tar' => 'application/x-tar',
	'taz' => 'application/x-tar',
	'tbp' => 'application/x-timbuktu',
	'tbt' => 'application/x-timbuktu',
	'tcl' => 'application/x-tcl',
	'tex' => 'application/x-tex',
	'texi' => 'application/x-texinfo',
	'texinfo' => 'application/x-texinfo',
	'tgz' => 'application/x-tar',
	'thm' => 'application/vnd.eri.thm',
	'tif' => 'image/tiff',
	'tiff' => 'image/tiff',
	'tki' => 'application/x-tkined',
	'tkined' => 'application/x-tkined',
	'toc' => 'application/toc',
	'toy' => 'image/toy',
	'tr' => 'application/x-troff',
	'trk' => 'x-lml/x-gps',
	'trm' => 'application/x-msterminal',
	'tsi' => 'audio/tsplayer',
	'tsp' => 'application/dsptype',
	'tsv' => 'text/tab-separated-values',
	'tsv' => 'text/tab-separated-values',
	'ttf' => 'application/octet-stream',
	'ttz' => 'application/t-time',
	'txt' => 'text/plain',
	'ult' => 'audio/x-mod',
	'ustar' => 'application/x-ustar',
	'uu' => 'application/x-uuencode',
	'uue' => 'application/x-uuencode',
	'vcd' => 'application/x-cdlink',
	'vcf' => 'text/x-vcard',
	'vdo' => 'video/vdo',
	'vib' => 'audio/vib',
	'viv' => 'video/vivo',
	'vivo' => 'video/vivo',
	'vmd' => 'application/vocaltec-media-desc',
	'vmf' => 'application/vocaltec-media-file',
	'vmi' => 'application/x-dreamcast-vms-info',
	'vms' => 'application/x-dreamcast-vms',
	'vox' => 'audio/voxware',
	'vqe' => 'audio/x-twinvq-plugin',
	'vqf' => 'audio/x-twinvq',
	'vql' => 'audio/x-twinvq',
	'vre' => 'x-world/x-vream',
	'vrml' => 'x-world/x-vrml',
	'vrt' => 'x-world/x-vrt',
	'vrw' => 'x-world/x-vream',
	'vts' => 'workbook/formulaone',
	'wav' => 'audio/x-wav',
	'wax' => 'audio/x-ms-wax',
	'wbmp' => 'image/vnd.wap.wbmp',
	'web' => 'application/vnd.xara',
	'wi' => 'image/wavelet',
	'wis' => 'application/x-InstallShield',
	'wm' => 'video/x-ms-wm',
	'wma' => 'audio/x-ms-wma',
	'wmd' => 'application/x-ms-wmd',
	'wmf' => 'application/x-msmetafile',
	'wml' => 'text/vnd.wap.wml',
	'wmlc' => 'application/vnd.wap.wmlc',
	'wmls' => 'text/vnd.wap.wmlscript',
	'wmlsc' => 'application/vnd.wap.wmlscriptc',
	'wmlscript' => 'text/vnd.wap.wmlscript',
	'wmv' => 'audio/x-ms-wmv',
	'wmx' => 'video/x-ms-wmx',
	'wmz' => 'application/x-ms-wmz',
	'wpng' => 'image/x-up-wpng',
	'wpt' => 'x-lml/x-gps',
	'wri' => 'application/x-mswrite',
	'wrl' => 'x-world/x-vrml',
	'wrz' => 'x-world/x-vrml',
	'ws' => 'text/vnd.wap.wmlscript',
	'wsc' => 'application/vnd.wap.wmlscriptc',
	'wv' => 'video/wavelet',
	'wvx' => 'video/x-ms-wvx',
	'wxl' => 'application/x-wxl',
	'x-gzip' => 'application/x-gzip',
	'xar' => 'application/vnd.xara',
	'xbm' => 'image/x-xbitmap',
	'xdm' => 'application/x-xdma',
	'xdma' => 'application/x-xdma',
	'xdw' => 'application/vnd.fujixerox.docuworks',
	'xht' => 'application/xhtml+xml',
	'xhtm' => 'application/xhtml+xml',
	'xhtml' => 'application/xhtml+xml',
	'xla' => 'application/vnd.ms-excel',
	'xlc' => 'application/vnd.ms-excel',
	'xll' => 'application/x-excel',
	'xlm' => 'application/vnd.ms-excel',
	'xls' => 'application/vnd.ms-excel',
	'xlt' => 'application/vnd.ms-excel',
	'xlw' => 'application/vnd.ms-excel',
	'xm' => 'audio/x-mod',
	'xml' => 'text/xml',
	'xmz' => 'audio/x-mod',
	'xpi' => 'application/x-xpinstall',
	'xpm' => 'image/x-xpixmap',
	'xsit' => 'text/xml',
	'xsl' => 'text/xml',
	'xul' => 'text/xul',
	'xwd' => 'image/x-xwindowdump',
	'xyz' => 'chemical/x-pdb',
	'yz1' => 'application/x-yz1',
	'z' => 'application/x-compress',
	'zac' => 'application/x-zaurus-zac',
	'zip' => 'application/zip', 
	'php' => 'text/plain',
);
function get_mime($filename, $ext = null)
{
	$flag = (strtolower(PHP_SHLIB_SUFFIX) == 'dll');
	$mime = 'application/octet-stream';
	if (function_exists('mime_content_type'))
	{
		if ($flag)
		{
			$php_exe_path = getenv('PHPRC').'\\php.exe';
			$cmd = "$php_exe_path -r \"echo mime_content_type('$filename');\"";
			$mime = `$cmd`;
		}
		else
		{
			$mime = mime_content_type($filename);
		}
	}
	else
	{
		global $mimetypes;
		if ($ext && isset($mimetypes[$ext]))
		{
			$mime = $mimetypes[$ext];
		}
	}

	return $mime;
}
/**	@brief 分页
	@param $addr string 地址串,{p}将会替换成页号
	@param $p int 页号
	@param $pc int 总页数
	@param $sc int 显示数 -1则全部显示
 */
function splitPage($addr, $p, $pc, $sc = -1, $first = '首页'/*'&lsaquo;&lsaquo;&lsaquo;'*/, $prev = '上页'/*'&lsaquo;&lsaquo;'*/, $next = '下页'/*'&rsaquo;&rsaquo;'*/, $last = '末页'/*'&rsaquo;&rsaquo;&rsaquo;'*/)
{
	$pages = array();
	if ($pc > 0)
	{
		array_push($pages, array('name' => $first, 'url' => str_replace('{p}', 1, $addr)));
		array_push($pages, array('name' => $prev, 'url' => str_replace('{p}', ($p > 1 ? $p - 1 : 1), $addr)));
		if ($sc > $pc)
		{
			for ($i = 0; $i < $pc; $i++)
			{
				array_push($pages, array('name' => $i + 1, 'url' => iifstr($i + 1 != $p, str_replace('{p}', $i + 1, $addr))));
			}
		}
		else
		{
			$prevCount = $prevCount2 = ceil($sc / 2) - 1;
			$nextCount2 = $sc - ($prevCount2 + 1);
			if ($prevCount > $p - 1)
			{
				$prevCount = $p - 1;
			}
			$nextCount = $sc - ($prevCount + 1);

			if ($nextCount > $pc - $p)
			{
				$nextCount = $pc - $p;
			}
			$prevCount = $sc - ($nextCount + 1);

			if ($p - $prevCount > 1)
			{
				array_push($pages, array(
					'name' => '...',
					'url' => str_replace('{p}', ($p - $prevCount - 1 - $nextCount2 < 1 ? 1 : $p - $prevCount - 1 - $nextCount2), $addr)
				));
			}

			$prevPages = array();
			$i = 0;
			$q = $p;
			while ($q > 1 && $i < $prevCount)
			{
				$q--;
				array_push($prevPages, array('name' => $q, 'url' => str_replace('{p}', $q, $addr)));
				$i++;
			}
			while ($pg = array_pop($prevPages))
			{
				array_push($pages, $pg);
			}

			array_push($pages, array('name' => $p, 'url' => ''));

			$i = 0;
			$q = $p;
			while ($q < $pc && $i < $nextCount)
			{
				$q++;
				array_push($pages, array('name' => $q, 'url' => str_replace('{p}', $q, $addr)));
				$i++;
			}

			if ($p + $nextCount < $pc)
			{
				array_push($pages, array(
					'name' => '...',
					'url' => str_replace('{p}', ($p + $nextCount + 1 + $prevCount2 > $pc ? $pc : $p + $nextCount + 1 + $prevCount2), $addr)
				));
			}
		}
		array_push($pages, array('name' => $next, 'url' => str_replace('{p}', ($p < $pc ? $p + 1 : $pc), $addr)));
		array_push($pages, array('name' => $last, 'url' => str_replace('{p}', $pc, $addr)));
	}

	return $pages;
}
/**
 * 获取当前文档的URL
 * @return string 返回当前文档的URL
 */
function get_url()
{
	//$URL=$_SERVER['PHP_SELF'].iifstr($_SERVER['QUERY_STRING']!='','?'.$_SERVER['QUERY_STRING']);
	return $_SERVER['REQUEST_URI'];
}
/**
 * 给定一个值，若真，则输出指定字符串，否则输出另外字符串
 * @param boolean $bool
 * @param string $str1
 * @param string $str2
 * @return string
 */
function iifstr($bool, $str1, $str2 = '')
{
	return $bool ? $str1 : $str2;
}
/**
 */
function limitWords($str, $n, $chn = true)
{
	$len = strlen($str);
	$retStr = '';
	$flag = 0;
	for ($i = 0; $i < $len; $i++)
	{
		if ($n == 0) break;
		$ch = ord($str[$i]);
		if ($ch & 0x80)
		{
			$retStr .= $str[$i].($i + 1 < $len ? $str[$i + 1] : '');
			$i++;
			$n--;
		}
		else
		{
			$flag++;
			$retStr .= $str[$i];
			if ($chn)
			{
				if ($flag == 2)
				{
					$n--;
					$flag = 0;
				}
			}
			else
			{
				$n--;
			}
		}
	}
	/*if (strlen($retStr) < $len)
	{
		$retStr .= '…';
	}*/
	return $retStr;
}
/**
 * 处理文章中的HTML特殊字符
 * @param string $string 要处理的字符串
 * @param boolean $bIsHtml 若为true,则结果可包含html标签; 为false,则把html标签转化为&??;
 * @return string
 */
function str_html($string, $bIsHtml = false)
{
	if (!$bIsHtml)
	{
		$string = htmlspecialchars($string);
	}
	$string = str_replace(' ','&nbsp;',$string);
	$string = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$string);
	$string = nl2br($string);
	return $string;
}
/**
 * 此函数将把PHP字符串转换成单行形式
 * 把换行转化为\n
 * @param string $string
 * @return string
 */
function str_source($string)
{
	return addcslashes($string,"\'\"\x0..\x19");
}
/**
 * 从GET,POST,COOKIE获取字符串. ',",\,NULL
 * @param string $string
 * @param bool[optional] $haveSlashes true,结果包含\; false,结果祛除\
 * @return string
 */
function GPC($string, $haveSlashes = false)
{
	$magic_quotes_gpc = ini_get('magic_quotes_gpc');
	if($haveSlashes) return $magic_quotes_gpc ? $string : addslashes($string);
	return $magic_quotes_gpc ? stripslashes($string) : $string;
}

function arrGPC($arr, $haveSlashes = false)
{
	$newarr = array();
	foreach ($arr as $k => $v)
	{
		if (is_array($v))
		{
			$newarr[$k] = arrGPC($v, $haveSlashes);
		}
		else
		{
			$newarr[$k] = GPC($v, $haveSlashes);
		}
	}
	return $newarr;
}
/**
 * 从脚本接收字符串
 * 由于脚本编码是UTF-8,所以需要转换
 * 此函数需要设置全局变量$page_charset,指定当前页面编码
 * @param string $str
 * @return string
 */
function strFromScript($str, $do_gpc = false, $haveSlashes = false)
{
	$page_charset = config('page_charset');
	return $do_gpc ? GPC(iconv('UTF-8',"{$page_charset}//IGNORE",$str), $haveSlashes) : iconv('UTF-8',"{$page_charset}//IGNORE",$str);
}

/**
 * 从Script接受数据
 * 专门处理GET POST数组的
 * @param array $arr
 * @param bool[optional] $do_gpc
 * @param bool[optional] $haveSlashes
 * @return array
 */
function arrFromScript($arr, $do_gpc = false, $haveSlashes = false){
	$newarr = array();
	foreach($arr as $k => $v)
	{
		if (is_array($v))
		{
			$newarr[strFromScript($k, $do_gpc, $haveSlashes)] = arrFromScript($v, $do_gpc, $haveSlashes);
		}
		else
		{
			$newarr[strFromScript($k, $do_gpc, $haveSlashes)] = strFromScript($v, $do_gpc, $haveSlashes);
		}
	}
	return $newarr;
}
/**
 * 返回用户IP地址
 * @return string
 */
function IP()
{
	$ip = "Unknown";
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	else if (isset($_SERVER["HTTP_CLIENT_IP"]))
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	else if (isset($_SERVER["REMOTE_ADDR"]))
		$ip = $_SERVER["REMOTE_ADDR"];
	else if (getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	return $ip;
}
/**
 * 字节大小
 */
function asByteSize($size){
	if ($size < 1024)
	{
		return $size . ' B';
	}
	elseif ($size < 1024 * 1024)
	{
		return ceil($size * 100 / 1024) / 100 . ' KB';
	}
	else
	{
		return ceil($size * 100 / (1024 * 1024)) / 100 . ' MB';
	}
}
// 加密
function site_encode($data)
{
	return base64_encode($data);
}
// 解密
function site_decode($encodedata)
{
	return base64_decode($encodedata);
}
#获取文件夹包含文件的一些信息,如文件总数,总字节数
function infoOfFolder($path, &$fileCount = null, &$dirCount = null)
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
			$bytes += infoOfFolder($fullname, $fileCount, $dirCount);
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
function bytesOfFolder($path)
{
	return infoOfFolder($path);
}
# 获取文件夹中的文件和子文件夹
function dataOfFolder($path, &$fileArr, &$subFolderArr)
{
	$handle = opendir($path);
	$fileArr = array();
	$subFolderArr = array();
	while ($filename = readdir($handle))
	{
		if ($filename == '.' || $filename == '..') continue;
		if (is_dir($path.'/'.$filename))
			array_push($subFolderArr, $filename);
		else
			array_push($fileArr, $filename);
	}
	closedir($handle);
	asort($fileArr);
	asort($subFolderArr);
}
# 通用删除.删除文件夹和文件
function commonDelete($path)
{
	if (is_dir($path))
	{
		dataOfFolder($path, $fileArr, $subFolderArr);
		foreach ($fileArr as $filename)
		{
			commonDelete($path.'/'.$filename);
		}
		foreach ($subFolderArr as $subpath)
		{
			commonDelete($path.'/'.$subpath);
		}
		rmdir($path);
	}
	else
	{
		unlink($path);
	}
}
# 文件名(strip extension name)
function file_name($fullpath)
{
	$name = basename($fullpath);
	$pos = strrpos($name, '.');
	return substr($name, 0, $pos === false ? -1 : $pos);
}
/** 使目录存在 */
function make_dir_exists($path, $cb_func_create = null)
{
	$arr = split('/',$path);
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

?>
<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\Cache\Config;

class MimeType {
	/**
	 * Os tipos de documento mais utilizados
	 *
	 * @var array
	 */
	static $content_types =[
		'plain'    => 'text/plain',
		'json'     => 'application/json',
		'bas'      => 'text/plain',
		'c'        => 'text/plain',
		'h'        => 'text/plain',
		'asp'      => 'text/plain',
		'text'     => 'text/plain',
		'stm'      => 'text/html',
		'xmltext'  => 'text/xml',
		'css'      => 'text/css',
		'richtext' => 'text/richtext',
		'ics'      => 'text/calendar',
		'ifb'      => 'text/calendar',
		'sgml'     => 'text/sgml',
		'sgm'      => 'text/sgml',
		'tsv'      => 'text/tab-separated-values',
		'wml'      => 'text/vnd.wap.wml',
		'wmls'     => 'text/vnd.wap.wmlscript',
		'etx'      => 'text/x-setext',
		'htc'      => 'text/x-component',
		'csv'      => 'text/tab-separated-values',
		'txt'      => 'text/plain',
		'ini'      => 'text/plain',
		'conf'     => 'text/plain',
		'asc'      => 'text/plain',
		'xml'      => 'text/xml',
		'xsl'      => 'text/xml',
		'sh'       => 'text/x-sh',
		'tcl'      => 'text/x-tcl',
		'js'       => 'text/x-javascript',
		'html'     => 'text/html',
		'shtml'    => 'text/html',
		'htm'      => 'text/html',
		'php'      => 'text/x-httpd-php',
		'php4'     => 'text/x-httpd-php',
		'php3'     => 'text/x-httpd-php',
		'phtml'    => 'text/x-httpd-php',

		'xlsx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xltx'     => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'potx'     => 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'ppsx'     => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'pptx'     => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'sldx'     => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'docx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dotx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'xlam'     => 'application/vnd.ms-excel.addin.macroEnabled.12',
		'xlsb'     => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
		'rtx'      => 'application/richtext',
		'rtf'      => 'application/rtf',
		'exe'      => 'application/octet-stream',
		'bin'      => 'application/octet-stream',
		'dms'      => 'application/octet-stream',
		'lha'      => 'application/octet-stream',
		'lzh'      => 'application/octet-stream',
		'class'    => 'application/octet-stream',
		'so'       => 'application/octet-stream',
		'dll'      => 'application/octet-stream',
		'vsd'      => 'application/x-visio',
		'vst'      => 'application/x-visio',
		'vsw'      => 'application/x-visio',

		'xhtml+xml' => 'application/xhtml+xml',
		'rdf'      => 'application/rdf+xml',
		'mathml'   => 'application/mathml+xml',
		'grxml'    => 'application/srgs+xml',
		'xul'      => 'application/vnd.mozilla.xul+xml',
		'vxml'     => 'application/voicexml+xml',
		'xht'      => 'application/xhtml+xml',
		'dtd'      => 'application/xml-dtd',
		'xslt'     => 'application/xslt+xml',
		'zip'      => 'application/x-compressed',
		'ppt'      => 'application/ms-powerpoint',
		'pps'      => 'application/ms-powerpoint',
		'pot'      => 'application/ms-powerpoint',
		'xls'      => 'application/msexcel',
		'doc'      => 'application/msword',
		'dot'      => 'application/msword',
		'pdf'      => 'application/pdf',
		'ez'       => 'application/andrew-inset',
		'hqx'      => 'application/mac-binhex40',
		'cpt'      => 'application/mac-compactpro',
		'oda'      => 'application/oda',
		'ogg'      => 'application/ogg',
		'ai'       => 'application/postscript',
		'eps'      => 'application/postscript',
		'ps'       => 'application/postscript',
		'smi'      => 'application/smil',
		'smil'     => 'application/smil',
		'gram'     => 'application/srgs',
		'mif'      => 'application/vnd.mif',
		'wbxml'    => 'application/vnd.wap.wbxml',
		'wmlc'     => 'application/vnd.wap.wmlc',
		'wmlsc'    => 'application/vnd.wap.wmlscriptc',
		'bcpio'    => 'application/x-bcpio',
		'vcd'      => 'application/x-cdlink',
		'pgn'      => 'application/x-chess-pgn',
		'cpio'     => 'application/x-cpio',
		'csh'      => 'application/x-csh',
		'dcr'      => 'application/x-director',
		'dir'      => 'application/x-director',
		'dxr'      => 'application/x-director',
		'dvi'      => 'application/x-dvi',
		'spl'      => 'application/x-futuresplash',
		'gtar'     => 'application/x-gtar',
		'hdf'      => 'application/x-hdf',
		'tex'      => 'application/x-tex',
		'phps'     => 'application/x-httpd-php-source',
		'skp'      => 'application/x-koan',
		'skd'      => 'application/x-koan',
		'skt'      => 'application/x-koan',
		'skm'      => 'application/x-koan',
		'latex'    => 'application/x-latex',
		'nc'       => 'application/x-netcdf',
		'cdf'      => 'application/x-netcdf',
		'crl'      => 'application/x-pkcs7-crl',
		'shar'     => 'application/x-shar',
		'swf'      => 'application/x-shockwave-flash',
		'sit'      => 'application/x-stuffit',
		'sv4cpio'  => 'application/x-sv4cpio',
		'sv4crc'   => 'application/x-sv4crc',
		'tgz'      => 'application/x-tar',
		'tar'      => 'application/x-tar',
		'texinfo'  => 'application/x-texinfo',
		'texi'     => 'application/x-texinfo',
		't'        => 'application/x-troff',
		'tr'       => 'application/x-troff',
		'roff'     => 'application/x-troff',
		'man'      => 'application/x-troff-man',
		'me'       => 'application/x-troff-me',
		'ms'       => 'application/x-troff-ms',
		'ustar'    => 'application/x-ustar',
		'src'      => 'application/x-wais-source',
		'crt'      => 'application/x-x509-ca-cert',
		'hta'      => 'application/hta',
		'hlp'      => 'application/winhlp',
		'gz'       => 'application/x-gzip',
		'asx'      => 'application/x-mplayer2',
		'mdb'      => 'application/x-msaccess',

		'gif'      => 'image/gif',
		'png'      => 'image/png',
		'jpeg'     => 'image/jpeg',
		'jpg'      => 'image/jpeg',
		'jpe'      => 'image/jpeg',
		'tif'      => 'image/tiff',
		'tiff'     => 'image/tiff',
		'bmp'      => 'image/bmp',
		'cgm'      => 'image/cgm',
		'ief'      => 'image/ief',
		'svg'      => 'image/svg+xml',
		'djvu'     => 'image/vnd.djvu',
		'djv'      => 'image/vnd.djvu',
		'wbmp'     => 'image/vnd.wap.wbmp',
		'ras'      => 'image/x-cmu-raster',
		'ico'      => 'image/x-icon',
		'pnm'      => 'image/x-portable-anymap',
		'pbm'      => 'image/x-portable-bitmap',
		'pgm'      => 'image/x-portable-graymap',
		'ppm'      => 'image/x-portable-pixmap',
		'rgb'      => 'image/x-rgb',
		'xbm'      => 'image/x-xbitmap',
		'xpm'      => 'image/x-xpixmap',
		'xwd'      => 'image/x-xwindowdump',

		'au'       => 'audio/basic',
		'snd'      => 'audio/basic',
		'wav'      => 'audio/x-wav',
		'mid'      => 'audio/midi',
		'midi'     => 'audio/midi',
		'kar'      => 'audio/midi',
		'mpga'     => 'audio/mpeg',
		'mp2'      => 'audio/mpeg',
		'mp3'      => 'audio/mpeg',
		'aif'      => 'audio/x-aiff',
		'aiff'     => 'audio/x-aiff',
		'aifc'     => 'audio/x-aiff',
		'g722'     => 'audio/G722',
		'g7221'    => 'audio/G7221',
		'g723'     => 'audio/G723',
		'g726-16'  => 'audio/G726-16',
		'g726-24'  => 'audio/G726-24',
		'gsm'      => 'audio/GSM',
		'g726-32'  => 'audio/G726-32',
		'g726-40'  => 'audio/G726-40',
		'g728'     => 'audio/G728',
		'g729'     => 'audio/G729',
		'g729d'    => 'audio/G729D',
		'g729e'    => 'audio/G729E',
		'ilbc'     => 'audio/iLBC',
		'rm'       => 'audio/x-pn-realaudio',
		'ram'      => 'audio/x-pn-realaudio',
		'm3u'      => 'audio/x-mpegurl',
		'rpm'      => 'audio/x-pn-realaudio-plugin',
		'ra'       => 'audio/x-realaudio',

		'avi'      => 'video/x-msvideo',
		'mp4'      => 'video/mp4',
		'dv'       => 'video/DV',
		'mpeg'     => 'video/mpeg',
		'mpg'      => 'video/mpeg',
		'mpe'      => 'video/mpeg',
		'mpeg4'    => 'video/mpeg4-generic',
		'raw'      => 'video/raw',
		'quicktime' => 'video/quicktime',
		'qt'       => 'video/quicktime',
		'mov'      => 'video/quicktime',
		'mxu'      => 'video/vnd.mpegurl',
		'movie'    => 'video/x-sgi-movie',
		'lsf'      => 'video/x-la-asf',
		'lsx'      => 'video/x-la-asf',
		'asf'      => 'video/x-ms-asf',
		'asr'      => 'video/x-ms-asf',
		'asx'      => 'video/x-ms-asf',

		'pdb'      => 'chemical/x-pdb',
		'xyz'      => 'chemical/x-xyz',

		'igs'      => 'model/iges',
		'iges'     => 'model/iges',
		'msh'      => 'model/mesh',
		'mesh'     => 'model/mesh',
		'silo'     => 'model/mesh',
		'wrl'      => 'model/vrml',
		'vrml'     => 'model/vrml',

		'ice'      => 'x-conference/x-cooltalk',
	];
	/**
	 * Lista de charset
	 *
	 * @var array
	 */
	static $charset_list = [
		'US-ASCII'    => 'Latin-USA',
		'ISO-8859-1'  => 'Latin-West Europe',
		'ISO-8859-2'  => 'Latin-East Europe',
		'ISO-8859-3'  => 'Latin-West Europe / Turkey',
		'ISO-8859-4'  => 'Latin-North & West Europe',
		'ISO-8859-5'  => 'Cyrillic',
		'ISO-8859-6'  => 'Arabic',
		'ISO-8859-7'  => 'Greek',
		'ISO-8859-8'  => 'Hebrew',
		'ISO-8859-15' => 'Latin-West Europe',
		'DEC-MCS'     => 'Latin-West Europe',
		'IBM437'      => 'Latin-West Europe',
		'IBM850'      => 'Latin-West Europe',
		'IBM852'      => 'Latin-East Europe',
		'IBM861'      => 'Latin-Iceland',
		'IBM862'      => 'Hebrew',
		'IBM866'      => 'Cyrillic',
		'windows-1250' => 'Latin-East Europe',
		'windows-1251' => 'Cyrillic',
		'windows-1252' => 'Latin-West Europe',
		'windows-1254' => 'Latin-Turkey',
		'UTF-8'       => 'Gothic-Unicode 3.1 Plane 1'
	];
	static function getExtension($name) {
		$p = pathinfo($name);
		if (!isset($p['extension'])) $p['extension'] = $p['basename'];
		return $p['extension'];
	}
	static function getMimeType($name) {
		$ext = self::getExtension($name);
		if (isset(self::$content_types[$ext])) return self::$content_types[$ext];
		return $ext;
	}
	static function getIcon($name) {
		$ext = self::getExtension($name);
		$mimetype = self::getMimeType($name);

		$p = explode('/', $mimetype);
		if ($p[0] == 'audio') $icon = 'wav';
		elseif ($p[0] == 'video') $icon = 'mov';
		elseif ($p[0] == 'text') {
			$icon = 'txt';
			if ($ext == 'php') $icon = 'web';
			elseif ($ext == 'html') $icon = 'web';
			elseif ($ext == 'shtml') $icon = 'web';
			elseif ($ext == 'htm') $icon = 'web';
			elseif ($ext == 'php4') $icon = 'web';
			elseif ($ext == 'php3') $icon = 'web';
			elseif ($ext == 'phtml') $icon = 'web';
		} elseif ($p[0] == 'image') {
			$icon = 'img';
			if ($ext == 'php') $icon = 'web';
			elseif ($ext == 'html') $icon = 'web';
		} elseif ($p[0] == 'application') {
			$icon = 'link';
			if ($ext == 'xlsx') $icon = 'xls';
			elseif ($ext == 'xltx') $icon = 'xls';
			elseif ($ext == 'xlam') $icon = 'xls';
			elseif ($ext == 'xlsb') $icon = 'xls';
			elseif ($ext == 'xls') $icon = 'xls';
			elseif ($ext == 'doc') $icon = 'doc';
			elseif ($ext == 'dot') $icon = 'doc';
			elseif ($ext == 'docx') $icon = 'doc';
			elseif ($ext == 'dotx') $icon = 'doc';
			elseif ($ext == 'ppt') $icon = 'ppt';
			elseif ($ext == 'pps') $icon = 'ppt';
			elseif ($ext == 'pot') $icon = 'ppt';
			elseif ($ext == 'potx') $icon = 'ppt';
			elseif ($ext == 'ppsx') $icon = 'ppt';
			elseif ($ext == 'pptx') $icon = 'ppt';
			elseif ($ext == 'sldx') $icon = 'ppt';
			elseif ($ext == 'vsd') $icon = 'vsd';
			elseif ($ext == 'vst') $icon = 'vsd';
			elseif ($ext == 'vsw') $icon = 'vsd';
		} else $icon = 'file';

		
		$c = Config::singleton();
		return "{$c->host}{$c->ed['icons']}/{$icon}.gif";
	}
}

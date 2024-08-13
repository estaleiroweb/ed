<?php
/**
 * @author Helbert Fernandes
 * @version 1.5 
 * @log 06/09/2007 20:00 [Helbert Fernandes]: agregação com .ini e suas funcionalidades
 * @param string $class_name Nome da classe que está sendo instanciada
 * 
 * Carrega classes que estão sendo instanciadas utilizando autoload.ini
 * [diretorio]
 * _= ; [opcional] e define que a classe será procurada também em seus subdiretórios hieraquizados com _
 * *= ; [opcional] e define que a classe será procurada no diretório
 * classe=arquivo ; [opcional] se utilizado, procura somente classes que tenha ese arquivo com path
 *
 * Import conf file (idem parser_ini_string com include, exec e parser de variáveis
 * - Inclusão de arquivos: 
 *      #include <filename>
 *      #include "filename"
 *      #include 'filename'
 *      #include filename
 * - Execução de arquivos: 
 *      #exec <filename>
 *      #exec "filename"
 *      #exec 'filename'
 *      #exec filename
 *
 * Variaveis GLOBAIS resultantes:
 *      $__autoload->dir      - Caminho completo da pasta do link simbólico ou index.php
 *      $__autoload->fileName - __FILE__ do autoload.php;
 *      $__autoload->iniDir   - Caminho completo da pasta onde foi encontrada o 'autoload.ini' 
 *      $__autoload->lf       - uso de \n ou <br>\n
 *      $__autoload->schema   - Protocolo de requisição
 *      $__autoload->host     - Nome do servidor
 *      $__autoload->url      - URL simples
 *      $__autoload->fullUrl  - URL completa
 *      $__autoload->referer  - URL referência de onde veio
 *      $__autoload->conf     - Configuração do autoload tratada
 *
 * Code examples:
 *   for($i=0;$i<=4;$i++) error('Nivel '+$i,$i,$errorLevels[$i]); //mostra todos as mensagens de erros
 */
//$_SESSION=array();
function __autoload($class_name=false) {
	global $__autoload;
	if(!@$__autoload) {
		$thisFile=getThisSymbLink();
		//if(@$_SESSION['__autoload']['file'] && !is_link($thisFile)) $thisFile=$_SESSION['__autoload']['file'];
		$autoload_file=Parse_Conf::trFileSess($thisFile,'autoload');
		$__autoload=Parse_Conf::loadSess($autoload_file);
		//print "$autoload_file\n";
		//if(@$_SESSION['__autoload'][$thisFile]) $__autoload=$_SESSION['__autoload'][$thisFile];
		//if(file_exists($autoload_file) && filemtime($autoload_file)>strtotime('-1 HOUR')) $__autoload=unserialize(file_get_contents($autoload_file));
		if(!$__autoload) {
			verbose('Start $__autoload GLOBAL variable');
			$__autoload=new StdClass;
			$__autoload->thisFile=$thisFile;
			$__autoload->dir=dirname($__autoload->thisFile);
			$__autoload->fileName=__FILE__;
			$__autoload->dirFileName=dirname(__FILE__);
			$__autoload->iniDir=null;
			$__autoload->lf=null;
			$__autoload->schema=null;
			$__autoload->host=null;
			$__autoload->port=null;
			$__autoload->url=null;
			$__autoload->fullUrl=null;
			$__autoload->path=null;
			$__autoload->referer=null;
			$__autoload->urlHistory=array();
			
			$iniFile=loadFileDefault(preg_replace('/\.php$/i','.ini',basename(__FILE__)));
			$__autoload->iniDir=$dirname=$__autoload->loadFileDefault;
			$__autoload->conf=array();
			foreach ($iniFile as $k=>$v) $__autoload->conf[str_replace('//','/',$k[0]==='/'?"$k/":"$dirname/$k/")]=$v;
			Parse_Conf::saveSess($autoload_file,$__autoload);
		}
		if (@$_SERVER['SHELL']) {
			$a=$GLOBALS['argv'];
			$__autoload->lf="\n";
			$__autoload->schema='file';
			($host=@$_SERVER['SSH_CONNECTION']) ||($host='localhost');
			$__autoload->host=$host;
			if(preg_match('/(\d+)\s*$/',$host,$ret)) $__autoload->port=$ret[1];
			$__autoload->url=array_shift($a);
			foreach($a as &$v) $v='"'.addslashes($v).'"';
			$__autoload->fullUrl=$__autoload->url.' '.implode(',',$a);
			$__autoload->path=$__autoload->url;
		}
		else{
			$__autoload->lf="<br>\n";
			$__autoload->referer=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
			$__autoload->schema=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']='on')?'https':strtolower(preg_replace('/[^a-z]/i','',@$_SERVER['SERVER_PROTOCOL']))).'://';
			($__autoload->host=@$_SERVER['SERVER_ADDR']) || ($__autoload->host=@$_SERVER['HTTP_HOST']);
			$__autoload->port=@$_SERVER['SERVER_PORT'];
			$__autoload->url=$__autoload->schema.$__autoload->host.$_SERVER['SCRIPT_NAME'];
			$__autoload->fullUrl=isset($_SERVER['REQUEST_URI'])?$__autoload->schema.$__autoload->host.$_SERVER['REQUEST_URI']:($__autoload->url.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
			$p=parse_url($__autoload->fullUrl);
			$__autoload->path=substr($p['path'],-1)=='/'?substr($p['path'],0,len($p['path'])-1):dirname($p['path']);
			//show($__autoload->path);
		}
		if(@$__autoload->urlHistory[0]['fullUrl']!=$__autoload->fullUrl) array_unshift($__autoload->urlHistory,array(
			'schema'=>$__autoload->schema,
			'host'=>$__autoload->host,
			'fullUrl'=>$__autoload->fullUrl,
		));
		//$_SESSION['__autoload']['file']=$thisFile;
		//$_SESSION['__autoload'][$thisFile]=$__autoload;
	}
	if(!$class_name) return array('__autoload'=>$__autoload);
	$class_name=str_replace('\\','/',$class_name);
	//verbose('FIND CLASS '.$class_name);
	foreach ($__autoload->conf as $path=>$v) {
		if($v && !array_key_exists('_',$v)){
			if(isset($v['*']));
			elseif(array_key_exists($class_name,$v) && is_file($file=$path.$v[$class_name])) return incFile($file);
			//elseif()
			else continue;
		} 
		else {
			$path.=(($pa=dirname($pc=preg_replace('/_+/','/',$class_name)))=='.'?$pc:$pa).'/';
		}
		//verbose($path.$class_name.'.php');
		if (is_file($file=$path.$class_name.'.php') || is_file($file=$path.$class_name.'.inc')) return incFile($file);
	}
	
	//error("Doesn't exist class/trait/interface '$class_name' ($path) in the system.",4);
}
function desenvolvimento($message=''){
	print "<div class='container alert alert-danger'>Em desenvolvimento.... $message</div>";
}
function incFile($file){
	verbose('REQUIRE '.$file);
	require_once($file);
}
function findFile($fileName,$path,$recursive=true){
	if(!is_array($path)) $path=array_unique(preg_split('/\s*[;]+\s*/',$path));
	foreach($path as $dirname) while(true) {
		$fullFileName="$dirname/$fileName";
		if(is_file($fullFileName)) {
			verbose('Found '.$fullFileName);
			return $dirname;
		}
		if(!$recursive || $dirname=='/') break;
		$dirname=dirname($dirname);
	}
	return false;
}
function loadFileDefault($fileName,$dieIfErro=true){
	global $__autoload;
	static $saved=array();
	
	$__autoload->loadFileDefault='';
	if(isset($saved[$fileName])) return $saved[$fileName];
	$files=array(preg_replace('/(\.\w+)$/','_'.hostname().'\1',$fileName),$fileName);
	foreach($files as $idx=>$fileName) {
		if(!($dirname=findFile($fileName,array($__autoload->dir,$__autoload->dirFileName)))) {
			if(!$idx) continue;
			if($dieIfErro) die($fileName.' inexistente');
			return $saved[$fileName]=false;
		}
		//show(__FUNCTION__.'['.__LINE__."]: $dirname/$fileName\n");
		$__autoload->loadFileDefault=$dirname;
	}
	return $saved[$fileName]=parse_conf_file($dirname.'/'.$fileName,true);
}
function parse_conf_file($filename,$process_sections=false) {
	$o=new Parse_Conf($filename,$process_sections);
	return $o->conf;
}
function parse_conf_string($string,$process_sections=false) {
	$o=new Parse_Conf(false,$process_sections);
	$o->get_string($string);
	return $o->conf;
}
function checkHost($url,$port=80){
	$urlSplit=parse_url($url);
	$host=@$urlSplit['host'];
	if(!$host || $host=='localhost' || $host=='127.0.0.1') return true;
	($p=@$urlSplit['port']) || ($p=$port);
	return (bool)@fsockopen($urlSplit['host'], $p, $errno, $errstr, 5);
}
function goURL($url='/'){
	header('HTTP/1.0 301 Moved');
	header('Location: '.$url);
	exit;
}
function value2String($value){
	if(is_null($value)) return 'NULL';
	if(is_bool($value)) return $value?'True':'False';
	if(is_numeric($value)) return $value;
	if(is_array($value) || is_object($value)) $value=serialize($value);
	return '"'.escapeString($value).'"';
	
}
function escapeString($str){
	return addcslashes($str, "\"'\\\0..\37\177");
}
function escapeStringForce($str){
	return addcslashes($str, "\"'%_\\\0..\37!@\177..\377");
}
function nmap($host,$port){
	set_error_handler('error_handler', E_ALL & ~E_NOTICE & ~E_WARNING);
	$conexao=@fsockopen($host, $port,$erro,$erro,15);
	if(($ret=(bool)$conexao)) @fclose($conexao);
	//print "Testing $host:$port ".($ret?'[OK]':'[ERROR]')."\n";
	restore_error_handler();

	return $ret;
}
function strip_accents($val){
	if(is_array($val)){
		foreach($val as $k=>$v) $val[$k]=strip_accents($v);
		return $val;
	}
	$val=mb_convert_encoding($val,$GLOBALS['encode'],mb_detect_encoding($val));
	$val=iconv($GLOBALS['encode'],'ASCII//TRANSLIT', $val);
	return preg_replace('/[^ -~]/','',$val);
	//return strtr($val,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}
function len($val){ return mb_strlen($val,$GLOBALS['encode']); }
function htmlScpChar($text,$quotes=ENT_NOQUOTES){ return htmlentities($text,$quotes,$GLOBALS['encode']); }
function htmlConvert($str) { return mb_convert_encoding($str,$GLOBALS['encode']); }
function pr($str) { print htmlConvert($str); }
function prt($str) { print trim(`date '+[%F %T.%N]:'`).' '.$str; }
function makeBox($message='',$title='',$class='makeBox',$tag='div'){
	$title=$title?"<h3>{$title}</h3>":'';
	if($message) $message="<pre>{$message}</pre>";
	return htmlConvert("<{$tag} class='{$class}'>{$title}{$message}</{$tag}>\n");
}
function veboseDefaultStyle(){
	global $__autoload;
	static $done=false;
	if($done || @$_SERVER['SHELL']) return;
	$done=true;
	$file='verbose.css';
	$path=findFile($file,array($__autoload->dir,$__autoload->dirFileName));
	if($path) print "<style>\n".file_get_contents("$path/$file")."</style>\n";
}
function verbose($text=true,$class=null){ 
	if(is_bool($text)) return $GLOBALS['__VERBOSE']=$text;
	if(!isset($GLOBALS['__VERBOSE']) || !$GLOBALS['__VERBOSE']) return;
	showme($text,$class);
}
function get_debug_backtrace(){
	$bt=debug_backtrace();
	$lOld=null;
	while($bt){
		$l=$bt[0];
		if(!preg_match('/^(show(me)?|verbose|error|(get|trace|compile)_debug_backtrace)$/',$l['function'])) break;
		$lOld=$l;
		array_shift($bt);
	}
	if($lOld) {
		if($bt) {
			array_unshift($bt,$bt[0]);
			$bt[0]['file']='CALLER:'.$lOld['file'];
			$bt[0]['line']=$lOld['line'];
		} else $bt=array($lOld);
	}
	return $bt;
}
function trace_debug_backtrace(&$bt){
	$l=array_shift($bt);
	$caller=@$l['file']?$l['file'].'['.$l['line'].']:':''; //file[line]:
	$caller.=@$l['class']?$l['class'].$l['type']:''; //class-> or class:: (if exists)
	if(@$l['function']) {
		$caller.=$l['function'].'('; //function(
		$args=@$l['args']?array_values(@$l['args']):array();
		if(@$_SERVER['SHELL'] || is_string(@$GLOBALS['__VERBOSE'])){
			//$caller.=implode(',',array_map('gettype',$args)).");\n"; //arg1,arg2,...);
			$caller.=implode(',',array_map('json_encode',$args)).");\n"; //arg1,arg2,...);
		}
		else { //Browser print type
			$caller=htmlScpChar($caller);
			$out=array();
			foreach($args as $k=>$v) $out[]='<span title="'.htmlScpChar(print_r($v,true),ENT_QUOTES).'">'.gettype($v).'</span>';
			$caller.=implode(',',$out); //arg1,arg2,...
			$caller.=');';
		}	
	}
	return $caller;
}
function compile_debug_backtrace($bt,$number=false) {
	if(!$bt)return '';
	$k=1;
	$trace=array();
	while($bt) $trace[$k++]=trace_debug_backtrace($bt);
	if(@$_SERVER['SHELL'] || is_string(@$GLOBALS['__VERBOSE'])){
		if($number) foreach($trace as $k=>&$v) $v=$k.': '.$v;
		return implode('',$trace);
	}
	else { //Browser print type
		if($number) foreach($trace as $k=>&$v) $v='<b style="width:20px;">'.$k.'</b>: '.$v;
		return '<pre><div>'.implode('</div><div>',$trace).'</div></pre>';
	}
}
function showme($text=true,$classStyle='makeBox'){ 
	$idUser=class_exists('Secure')?Secure::$idUser:1;
	return $idUser==2 || $idUser==0?show($text,$classStyle):null; 
}
function show($text=true,$classStyle='makeBox'){ 
	if(is_object($text) && method_exists($text,'__debugInfo')) $text=$text->__debugInfo();
	$text=print_r($text,true);
	$bt=get_debug_backtrace();
	//Find the file and function caller
	$caller=trace_debug_backtrace($bt);
	$trace=$GLOBALS['__VERBOSE_TRACE']?compile_debug_backtrace($bt):'';
	if(@$_SERVER['SHELL'] || is_string(@$GLOBALS['__VERBOSE'])){
		$caller.=$trace;
		$out=$caller?$caller."\n":'';
		if($text)$out.="$text\n";
		$out.="\n";
		//$out=mb_convert_encoding($out,$GLOBALS['encode']);
		if(is_string(@$GLOBALS['__VERBOSE'])) return file_put_contents($GLOBALS['__VERBOSE'],$out,FILE_APPEND);
	}
	else { //Browser print type
		$out=makeBox($trace.htmlScpChar($text),$caller,$classStyle,'pre');
	}

	if($classStyle===false) return $out;
	print $out;
}
function preBuildTable($arr,$showKey='#',$maxLength=30,$countRecords=true){
	if(!$arr) return;
	$result='';
	
	if($arr instanceof Conn_Main_result) {
		$keys=array();
		foreach($arr->fetch_fields() as $v) $keys[$v->name]=$v->name;
		//print_r($keys);
		$fn=function(&$arr){ return $arr->fetch_assoc(); };
	} 
	else {
		$keys=array_keys(reset($arr));
		$fn=function(&$arr){ return $arr?array_shift($arr):false; };
	}
	//print_r(array($fn,$pr));return;
	
	if($showKey) array_unshift($keys,$showKey);
	$head=array();
	foreach($keys as $k) {
		$head['length'][$k]=min(max(1,len($k)),$maxLength);
		$head['align'][$k]=STR_PAD_LEFT;
		$head['alignHead'][$k]=$showKey==$k?STR_PAD_LEFT:STR_PAD_RIGHT;
	}
	
	$idx=0;
	$lines=array();
	while($line=$fn($arr)) {
	//foreach($arr as $line) {
		if($showKey) $line[$showKey]=$idx++;
		foreach($head['length'] as $k=>$len) {
			$v=@$line[$k];
			if(is_object($v)) $v=$line[$k]=($v instanceof Conn_Main_result_field)?$v():json_encode($v);
			elseif(is_array($v)) $v=$line[$k]=json_encode($v);
			$head['length'][$k]=min(max($len,len($v)),$maxLength);
			if($v && !is_numeric($v)) $head['align'][$k]=STR_PAD_RIGHT;
		}
		$lines[]=$line;
	}
	foreach($keys as $k) {
		$head['orgname'][$k]=Conn::fit_Field($k,$head['length'][$k],@$head['alignHead'][$k]);
		$head['line'][$k]=str_repeat('-',$head['length'][$k]);
	}
	//print_r($head);
	
	$result.=Conn::fit_showHeadLineTop($head['orgname'],true);
	$result.=Conn::fit_Line($head['orgname'],true);
	$result.=Conn::fit_showHeadLineBottom($head['orgname'],true);

	foreach($lines as $idx=>$line)if(is_array($line)){
		if($showKey) $line=array_merge(array($showKey=>$idx),$line);
		foreach($line as $k=>$v) $line[$k]=Conn::fit_Field($v,$head['length'][$k],$head['align'][$k]);
		$result.=Conn::fit_Line($line,true);
	} 
	else{
		$result.=print_r($line,true);
		$result.="\n";
	}
	$result.=Conn::fit_showFootLine($head['orgname'],true);
	if($countRecords) $result.=count($lines)." Records\n";
	$result.="\n";
	return $result;
}
function showTable($arr,$showKey='#',$maxLength=30,$countRecords=true,$classStyle='makeBox'){
	if(!$arr) return;
	$result=preBuildTable($arr,$showKey,$maxLength,$countRecords,$classStyle);

	if(@$_SERVER['SHELL']) print $result;
	else print makeBox($result,'',$classStyle,'pre');
}
function error($message='',$level=0,$title=false){
	$message=print_r($message,true);
	$trace=$GLOBALS['__VERBOSE_TRACE']?compile_debug_backtrace(get_debug_backtrace(),true):'';
	$level=min(floor(abs($level)),FATAL_ERROR);
	$title=$GLOBALS['errorLevels'][$level].($title?': ':'').$title;
	if(@$_SERVER['SHELL']) {
		$title=mb_convert_encoding($title,$GLOBALS['encode'],mb_detect_encoding($title));
		print '###'.strip_tags($title)."\n";
		if($trace) {
			$trace=mb_convert_encoding($trace,$GLOBALS['encode'],mb_detect_encoding($trace));
			print "$trace\n";
		}
		if($message) {
			$message=mb_convert_encoding($message,$GLOBALS['encode'],mb_detect_encoding($message));
			print "$message\n";
		}
	}
	else {
		$class=strtolower($GLOBALS['errorLevels'][$level]).'_error';
		$message=htmlScpChar($message);
		print makeBox($trace.$message,$title,'makeBox errorBox '.$class);
	}
	if($level==FATAL_ERROR) exit;
	return !$level;
}
function error_handler($errno, $errstr, $errfile, $errline, $errcontext,$bt=array(),$trigger=true) {
	global $__TRAP_ERROR, $__TRAP_ERROR_CALLBACK, $__TRAP_ERROR_CALLBACK_PARAMETERS;

	//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_STRICT
	if($__TRAP_ERROR & $errno && $__TRAP_ERROR_CALLBACK) call_user_func_array($__TRAP_ERROR_CALLBACK,$__TRAP_ERROR_CALLBACK_PARAMETERS);

	if(!@$GLOBALS['show_debug_all_erros'] || $errno==8192) return;
	$message=array();
	error($errstr,0,"<label>ERROR [$errno] </label><label>File: </label><i>$errfile [$errline]</i>");
	if($trigger) @trigger_error($errstr, $errno);
}
function disable_error_handler(){
	set_error_handler('error_handler', 0);
}
function fixLink($link){
	do{
		$old=$link;
		$link=preg_replace('/([\/\\\])[^\/\\\]+?\1\.\.\1/','\1',$link);
	} while ($link!=$old);
	return $link;
}
function getThisSymbLink(){
	$bt=debug_backtrace();
	while($bt) {
		$oBt=@array_pop($bt); 
		//print "<div>{$oBt['class']}.{$oBt['function']}</div>";
		if(preg_match('/(require|include)(_once)?/i',$oBt['function'])) {
			$file_refer=file($oBt['file']);
			$file_refer=$file_refer[$oBt['line']-1];
			$file_refer=preg_replace('/^.*?(?:require|include)(?:_once)?\s*/i','return ',trim($file_refer));
			$file_refer=str_replace(array("\x5C\x5C","\x5C\x27","\x5C\x22"),array('\x5C','\x27','\x22'),$file_refer);
			$link=eval(preg_replace('/\$(\w+)/','$GLOBALS[\'\1\']',$file_refer));
			$d=dirname($link);
			if($d[0]=='.') $link=dirname($oBt['file']).'/'.$link;
			$link=fixLink($link);
			if(realpath($link)==__FILE__) return $link;
		}
	}
	return __FILE__;
}
function hostname(){
	return gethostname();
}
function field_split($fields){ return preg_split('/\s*[,;]\s*/',trim($fields)); }
function toBool($value) {
	if(is_string($value)) {
		$value=strtolower($value);
		if($value==='false' || $value==='falso' || $value==='off' || $value==='desligado' || $value==='0') return false;
	}
	return $value?true:false;
}
function json_stripSlashes(&$value=null,$id=null){
	return $value=json_stripSlashes_queue((string)$value,$id);
}
function json_stripSlashes_get(){
	return json_stripSlashes_queue();
}
function json_stripSlashes_clear(){ 
	return json_stripSlashes_queue(true);
}
function json_stripSlashes_queue($value=null,$id=null){
	static $fns=array();
	static $cont=0;
	
	if($id) {
		if(preg_match('/^%STRIP_SLASHES#\d+%$/',$id)) $name=$id;
		else return $id;
	}
	else {
		$id=null;
		$name='%STRIP_SLASHES#'.$cont.'%';
		$cont++;
	}
	$out=$name;
	if(is_null($value) || is_bool($value)) {
		$out=$id?$fns[$name]:$fns;
		if($value===true) {
			$fns=array();
			$cont=0;
		}
	}
	else $fns[$name]=$value;
	return $out;
}
function json_encode_full($mixed,$t='',$tab=''){
	if(is_array($mixed)) {
		$lf="\n";
		$lft=$lf.$tab;
		$lftt=$lft.$t;
		$d=array('[]','{}');
		$out=array(array(),array());
		$cont=0;
		$isObj=0;
		foreach($mixed as $k=>$v) {
			if($k!==$cont++) $isObj=1;
			$v=json_encode_full($v,$tab.$t,$t);
			$out[0][]=$v;
			$out[1][]=json_encode_full($k).':'.$v;
		}
		$d=$d[$isObj];
		$out=$d[0].$lftt.implode(','.$lftt,$out[$isObj]).$lft.$d[1];
		return len($out)>70?$out:str_replace(array($t,$lf),'',$out);
	}
	if(is_object($mixed)) {
		if(method_exists($mixed,'__debugInfo')) return json_encode_full($mixed->__debugInfo());
		return json_encode_full(array(
			'type'=>'object',
			'name'=>get_class($mixed),
			'content'=>get_object_vars($mixed),
			'methods'=>get_class_methods($mixed),
		),$tab.$t,$t);
	}
	if(is_bool($mixed)) return $mixed?'true':'false';
	if(is_null($mixed)) return 'null';
	if(is_numeric($mixed)) return $mixed;
	if(is_resource($mixed)) {
		return json_encode_full(array(
			'type'=>'resource',
			'name'=>(string)$mixed,
			'content'=>get_resource_type($mixed),
		),$tab.$t,$t);
	}
	return '"'.addcslashes($mixed,"\x00..\x1F\x7E..\xFF'\"\\").'"';
}
function json_encode_ex($mixed,$tab=''){
	return preg_replace('/^\s+/','',json_encode_full($mixed,"\t",$tab));
}
function json_encode2($mixed){
	return strtr(
		preg_replace('/([\'"])(%STRIP_SLASHES#\d+%)\1/','\2',json_encode($mixed)),
		json_stripSlashes_queue()
	);
}
function getFreeRandomPort($host='127.0.0.1',$start=1000,$end=65536) {
	$i=0;
	do {
		$i++;
		$port=rand($start, $end);
		$nmap=nmap($host,$port);
	} while(!$nmap && $i<100);
	if(!$nmap) return $port;
}
function init($class,$array){
	$obj=new $class;
	if($array) $obj->loadArray($array);
	if(method_exists($obj,'init')) $obj->init();
	return $obj;
}
function logFile($file,$chunk_size=0){
	global $logFile;
	
	if(!$logFile) {
		$logFile=array();
		while (@ob_end_clean());
		@ob_start('logFile_buffer',$chunk_size);
	}
	$logFile[]=$file;
}
function logFile_buffer($buffer){
	global $logFile;
	foreach($logFile as $file) file_put_contents($file,$buffer,FILE_APPEND);
	@flush();
}
function checkRequestMethod(){
	$data=file_get_contents('php://input');
	$m=@$_SERVER['REQUEST_METHOD'];
	if(!$m) return;
	if(@$data[0]=='{') $GLOBALS['_'.$m]==json_decode($data);
	elseif(!in_array($m,array('GET','POST'))) parse_str($data,$GLOBALS['_'.$m]);
}
function array_transposer($arr){
	foreach($arr as $v) if(!is_array($v)) return [$arr];
	$aHead=array_keys($arr);
	return array_map(
		function ($n) use ($aHead) { return array_combine($aHead,$n);},
		call_user_func_array('array_map',array_merge([function() {return func_get_args();}],$arr))
	);
}
/**
 * Através de uma string do primeiro parametro, 
 * instancia uma classe e executa um método com parametros seguintes
 * 
 * @param string $fn nome da classe e/ou metodo a ser executado seguindo tabela conceito abaixo
 *                   Valor: Ação
 *                   'class': new class($args); #__construct
 *                   'class()': (new class)($args); #__invoke
 *                   'class->method': (new class)->method($args); #instanced method
 *                   'class::method': (new class)::method($args); #estatic method
 *                   'class->method()': idem class->method
 *                   'class::method()': idem class::method
 * @param array $args argumentos a serem passados
 * @return mixed Valor/Objeto retornado do Método/Classe
 */
function callback_array($fn,array $args=[]){
	$class=$type=$param='';
	$method='newInstance';
	if(preg_match('/^([a-z_]\w*)(\(.*\))?$/i',$fn,$ret)) {
		$class=$ret[1];
		$param=@$ret[2];
	} else if(preg_match('/^([a-z_]\w*)(::|->)([a-z_]\w*)(?:\((.*)\))?$/i',$fn,$ret)) {
		$class=$ret[1];
		$type=$ret[2];
		$method=$ret[3];
		$param=@$ret[4];
	} else return;
	if(!class_exists($class)) return;

	//$param=array_merge($param?explode(',',$param):[],$args);

	if($type=='::') $cb="{$class}::{$method}";
	else{
		if($type) $class=new $class;
		elseif($param=='()') {
			$class=new $class;
			$method='__invoke';
		} else $class=new ReflectionClass($class);
		$cb=[$class,$method];
	}
	if(!method_exists($class,$method)) return;
	return call_user_func_array($cb,$args);
}
/**
 * Idem callback_array parefaseando call_user_func_array e call_user_func
 * 
 * @param string $fn nome da classe e/ou metodo a ser executado seguindo tabela conceito abaixo
 * @param mixed ... Argumentos a serem passados
 * @return mixed Valor/Objeto retornado do Método/Classe
 */
function callback($fn){
	$args=func_get_args();
	array_shift($args);
	return callback_array($fn,$args);
}

class Parse_Conf {
	public static $ttl='-1 HOUR';
	public $save_cache=true;
	public $filename='';
	public $dirname='';
	public $group='';
	public $key='';
	public $process_sections=false;
	public $conf=array();
	public $errors=array();
	
	function __construct($filename_string=false,$process_sections=false,$save_cache=true){
		$this->process_sections=$process_sections;
		$this->save_cache=$save_cache;
		//if(!isset($_SESSION['__Parse_Conf'])) $_SESSION['__Parse_Conf']=array();
		if($filename_string) $this->get_file($filename_string) || $this->get_string($filename_string);
	}
	static function trFileSess($fullfileName,$pre=null){ 
		if(!$pre) $pre=__CLASS__ .'_'. preg_replace(array('/[\/\\\.]/','/^_+/','/_+$/'),array('_','',''),dirname($fullfileName));
		return session_save_path().'/'.$pre.'_'.basename($fullfileName).'.serialized';
	}
	static function fileSess($file){ 
		return session_save_path().'/'.$file; 
	}
	static function loadSess($file){ 
		if(file_exists($file) && filemtime($file)>strtotime(self::$ttl)){
			verbose('Load Session '.$file);
			return unserialize(file_get_contents($file));
		}
		return false;
	}
	static function saveSess($fileSess,$val){ 
		verbose('Save Session '.$fileSess);
		@file_put_contents($fileSess,serialize($val));
		chown($fileSess,'apache');
		chgrp($fileSess,'apache');
	}
	function get_file($filename=false) {
		if(!$filename) $filename=$this->filename;
		$this->filename=$filename;
		$this->dirname=dirname($filename);
		if(!is_file($filename)) return false;
		
		verbose('Parse file '.$filename);
		$fileSess=self::trFileSess($filename);
		if($this->save_cache && $conf=self::loadSess($fileSess)) return $this->conf=$conf;
		
		$string=@file_get_contents($filename);
		if(!$string) return false;
		
		$conf=$this->get_string($string,$this->process_sections);
		if($this->save_cache) self::saveSess($fileSess,$conf);

		return $conf;
	}
	function parse_ini_string($string,$process_sections=false) {
		verbose('Parse string '.$string);
		$string=preg_replace(array('/^\s*#.*[\r\n]+/','/[\r\n]+\s*#.*/',), '', $string);
		//print "\n------\n$string\n-------\n";
		if(!$string) return false;
		$tmpName = tempnam(sys_get_temp_dir(), 'ini');
		$tmpHandle = fopen($tmpName, 'w');
		fwrite($tmpHandle,$string);
		fclose($tmpHandle);
		$parsed=parse_ini_file($tmpName,$process_sections);
		
		unlink($tmpName);
		return $parsed;
	}
	/*
	 * Import conf file (idem parser_ini_string com include, exec e parser de variáveis
	 * - Inclusão de arquivos: 
	 *      #include <filename>
	 *      #include "filename"
	 *      #include 'filename'
	 *      #include filename
	 * - Execução de arquivos: 
	 *      #exec <filename>
	 *      #exec "filename"
	 *      #exec 'filename'
	 *      #exec filename
	 */
	function get_string($string=false) {
		global $__autoload;
		if(!$string) return false;
		{//Load Information
			$out=function_exists('parse_ini_string')?parse_ini_string($string,$this->process_sections):$this->parse_ini_string($string,$this->process_sections);
			$linesInc=preg_grep('/^\s*#/i',preg_split('/[\r\n]+/',$string));
			if(!$out && !$linesInc) return false;
		}
		{//Parse variables
			foreach($out as $grp=>$lines){
				if(is_array($lines)) {
					$this->group=$this->eVar($grp);
					$this->conf[$this->group]=array();
					foreach($lines as $this->key=>$value) $this->conf[$this->group][$this->eVar($this->key)]=$this->eVar($value);
				} 
				else {
					$this->key=$this->eVar($grp);
					$this->conf[$this->key]=$this->eVar($lines);
				}
			}
		}
		{//Import and execute #files
			foreach($linesInc as $line) if(preg_match('/^\s*#\s*(include|exec)(?:\s*<([^>]+)>|\s*"([^"]+)"|\s*\'([^\']+)\'|\s+(.+))/i',$line,$ret)) {
				($include=@$ret[2]) || ($include=@$ret[3]) || ($include=@$ret[4]) || ($include=@$ret[5]);
				$include=$this->eVar($include);
	//show([$include]);die('Manutenção: '.__LINE__);
				$class=__CLASS__;
				$o=new $class(false,$this->process_sections);
				$o->conf=&$this->conf;
				$fnInc=strtolower($ret[1]);
				verbose('#'.$fnInc.' '.$include);
				
				if($fnInc=='include') $r=$o->get_file($include);
				else {
					$o->filename=$include;
					$o->dirname=dirname($include);
					$r=$o->get_string(@`$include`);
				}
				if($r===false) print "#{$fnInc} $include: not exists{$__autoload->lf}";
			}
		}
		return $this->conf;
	}
	/**
	* Troca:
	* {$this} pelo diretório do arquivo que está parseando
	* {$this[array_item]} por $this->conf[array_item]
	* {$variable} por $GLOBALS[variable]
	* {$variable[array_item]} por $GLOBALS[variable][array_item]
	*
	* Não Troca:
	* {$this->variable} ou {$this->fn()}
	**/
	private function eVar($value){
		if(preg_match_all('/\{\$([^\{\}\(\)\[:>-]+)([^\}]*)\}/',$value,$ret,PREG_SET_ORDER)) foreach($ret as $v) {
			if(strtolower($v[1])=='this') $v[1]=$v[2]?($v[2][0]=='['?'$this->conf':'$this'):'$this->dirname';
			else $v[1]='$GLOBALS["'.$v[1].'"]';
			//print "oldValue=$value  => {$v[1]}{$v[2]};\n";
			$value=str_replace($v[0],@eval('return '.$v[1].$v[2].';'),$value);
			//print "newValue=$value\n";
		}
		return $value;
	}
}
class ED_Links {
	static protected function smartLnk($link){
		global $__autoload;
		if($link[0]=='/' || $link[0]=='.') return $link;
		$path=$__autoload->path;
		return $path.'/'.$link;
	}
	static protected function lnk($value,$link=null,$attrs=array(),$alt=null){
		if($attrs) $link.=(strpos($link,'?')?'&':'?').http_build_query($attrs);
		$title=$alt?' title="'.htmlentities($alt).'"':'';
		$link=self::smartLnk($link);
		return "<a href='$link'$title>$value</a>";
	}
	static protected function smartId($value,$fieldName,$fields,$obj,$link,$k,$alt=null){
		if($value=='') return '';
		$id=array_key_exists($k,$fields)?$fields[$k]:$fieldName;
		return self::lnk($value,$link,array($k=>$id),$alt);
	}
	static protected function filter($value,$link,$dataList_name,$filter=array(),$dataTab_name=null,$alt=null){
		// self::filter('valor','/inventory/acme/','dataList_name',array('Device'=>'VSC*'),'dataTab_name');
		/*{
			"URL":"http://portalfsc/inventory/acme/",
			"DataTab":{"SBC Acme Inventory":{"tabActived":"Host Routes"}},
			"DataList":{
				"Host Routes":{
					"reset":"",
					"outFormat":"",
					"default":"",
					"showFilter":"1",
					"showRecCount":"1",
					"showNavBars":"1",
					"showTable":"1",
					"page":"1",
					"pages":"140",
					"order":"",
					"group":"",
					"lstFields":"Device,dest_network,netmask,gateway,description",
					"widthField":"32",
					"width":"[]",
					"fields":"{\"Device\":\"Device\",\"idDevice\":\"id Device\",\"DtCol\":\"Dt Col\",\"dest_network\":\"dest network\",\"netmask\":\"netmask\",\"gateway\":\"gateway\",\"description\":\"description\",\"last_modified_date\":\"last modified date\"}",
					"lines":"10",
					"values":{"Device":"*","idDevice":"*","DtCol":"*","dest_network":"*","netmask":"*","gateway":"*","description":"*","last_modified_date":"*"}
				}
			}
		}*/
		$link=self::smartLnk($link);
		$content=array('URL'=>$link,'DataList'=>array($dataList_name=>array('values'=>$filter)));
		if($dataTab_name) $content['DataTab']=array($dataTab_name=>array('tabActived'=>$dataList_name));
		$href=json_encode($content);
		$title=$alt?' title="'.htmlentities($alt).'"':'';
		return "<a href='/easyData/fn/urlRedir.php?$href'$title>$value</a>";
	}
	
	static public function Email($value=null,$fieldName=null,$fields=array(),$obj=null){
		return "<a href='mailto:$value'>$value</a>";
	}

	static public function field_idUser($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::smartId($value,$fieldName,$fields,$obj,'users_edit.php','idUser','Detalhes deste usuário');
	}
	static public function field_idGrpUsr($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::smartId($value,$fieldName,$fields,$obj,'group_users.php','idGrpUsr','Detalhes deste grupo de usuário');
	}
	static public function field_idCargo($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::smartId($value,$fieldName,$fields,$obj,'posts.php','idCargo','Detalhes deste Cargo');
	}
	static public function field_idFile($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::smartId($value,$fieldName,$fields,$obj,'files.php','idFile','Detalhes deste arquivo');
	}
	static public function field_idGrpFile($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::smartId($value,$fieldName,$fields,$obj,'group_files.php','idGrpFile','Detalhes deste grupo de arquivos');
	}
	
	static public function filter_GrpUsr($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::filter($value,'permitions.php','Permitions',array('GrpUsr'=>$fields['GrpUsr']),null,'Filtra permissões por grupo de usuãrios');
	}
	static public function filter_GrpFile($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::filter($value,'permitions.php','Permitions',array('GrpFile'=>$fields['GrpFile']),null,'Filtra permissões por grupo de arquivos');
	}
	static public function filter_Domain($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::filter($value,'users.php','Users',array('Domain'=>$fields['Domain']),null,'Filtra usuários por Domínio');
	}
	static public function filter_Post($value=null,$fieldName=null,$fields=array(),$obj=null){
		return self::filter($value,'users.php','Users',array('Cargo'=>$fields['Cargo']),null,'Filtra usuários por Cargo');
	}
}

{//Variaveis iniciais
	$show_debug_all_erros=true;
	//function error_handler... if($__TRAP_ERROR & $errno && $__TRAP_ERROR_CALLBACK) call_user_func_array($__TRAP_CALLBACK,$__TRAP_CALLBACK_PARAMETERS);
	$__VERBOSE_TRACE=false;
	//$__VERBOSE_TRACE=true;
	$__TRAP_ERROR=0;
	$__TRAP_ERROR_CALLBACK=NULL; // call_user_func_array => 'function' | array('obj|class','method')
	$__TRAP_ERROR_CALLBACK_PARAMETERS=array();
	$errorLevels=array(0=>'Message',1=>'Normal',2=>'Warning',3=>'Critical',4=>'Fatal');
	define ('MESSAGE',0);
	define ('NORMAL_MESSAGE',1);
	define ('WARNING',2);
	define ('CRITICAL_ERROR',3);
	define ('FATAL_ERROR',4);
	checkRequestMethod();
}
{//Configurações iniciais
	set_error_handler('error_handler', E_ALL & ~E_NOTICE & ~E_STRICT); // & ~E_WARNING

	mb_detect_order('ASCII,UTF-8,ISO-8859-1,MS-ANSI,UTF-16,WINDOWS-1252,CP1252,UCS-2,UCS-2BE,UCS-2LE,UTF-16BE,UTF-16LE,eucjp-win,sjis-win');
	$encode=mb_detect_encoding('aeiouáéíóú');
	mb_internal_encoding($encode);
	mb_http_output($encode);
	mb_http_input($encode);
	mb_regex_encoding($encode);
	mb_language('uni');

	@date_default_timezone_set('America/Sao_Paulo');
	//@date_default_timezone_set('Brazil/EAST');
	if(@$_SERVER['SHELL']) session_set_save_handler(new FileSessionHandler(true), true);
}

//print_r(__autoload()); //Mostra todos os caminhos de configuração

__autoload();
if (!session_id()) try {
	session_start();
} catch (Exception $e) {

}
if(@$__autoload->thisFile) $_SESSION['__autoload']['file']=$__autoload->thisFile;

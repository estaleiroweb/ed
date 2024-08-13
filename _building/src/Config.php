<?php
 /**
 * Page-Level DocBlock OutHtml
 * @package Easy
 * @subpackage Config
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.1 em 03/10/2006 15:00 - Helbert Fernandes
 */
 
 /**
 * Configuração de todos os caminhos desde a raiz das libs necessárias para executar o sistema configIni e securyIni e hostIni
 *
 * @example
 * <code>
 * <?php $config=Config::singleton(); ?>
 * </code>
 */
class Config {
	static private $instance;
	private $readonly=array('pathAutoload'=>'','site'=>'/','pathParts'=>'','easyData'=>'','schema'=>'','host'=>'','root'=>'/var/www/html','path'=>'','url'=>'','fullurl'=>'','mounted'=>false,'ini'=>'','referer'=>'','cr'=>"\r\n",'tab'=>"\t",'autoload'=>array(),);
	public $hosts=array();
	public $print=array(
		'cr','tab',
		'host','easyData','FCKeditor','css','js','imgs',
	);

	static public function singleton()   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	private function __construct() {
		$this->readonly['autoload']=$this->getFunctionDetails('__autoload');
		$this->readonly['pathAutoload']=$this->getDir(@$this->readonly['autoload']['fileName']);
		$scripName=isset($_SERVER['SCRIPT_FILENAME'])?$_SERVER['SCRIPT_FILENAME']:$_SERVER['PATH_TRANSLATED'];
		$this->readonly['pathParts']=pathinfo($scripName);
		$this->readonly['file']=$this->readonly['pathParts']['basename'];
		if (isset($_SERVER['SHELL'])) {
			$this->readonly['url']=$this->readonly['filename']=$scripName;
			$pathSplit=pathinfo($this->url);
		}else{
			$pathSplit=pathinfo($scripName);
			$this->readonly['schema']=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']='on')?'https':strtolower(preg_replace('/[^a-z]/i','',@$_SERVER['SERVER_PROTOCOL']))).'://';
			($host=@$_SERVER['HTTP_HOST']) || ($host=@$_SERVER['SERVER_NAME']);
			$this->readonly['host']=$this->schema.$host;
			$this->readonly['url']=$this->host.$_SERVER['SCRIPT_NAME'];
			$this->readonly['fullurl']=isset($_SERVER['REQUEST_URI'])?$this->host.$_SERVER['REQUEST_URI']:($this->readonly['url'].(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
			$this->readonly['filename']=$scripName;
			$this->readonly['root']=is_link($_SERVER['DOCUMENT_ROOT'])?readlink($_SERVER['DOCUMENT_ROOT']):$_SERVER['DOCUMENT_ROOT'];
		}
		
		$this->readonly['path']=$pathSplit['dirname'];
		$this->readonly['site']=$this->readonly['pathAutoload']?$this->getUrlReferer($this->readonly['pathAutoload']):'/';
		$this->readonly['easyData']=preg_replace('/\/php\/$/','',"{$this->readonly['site']}/{$this->readonly['autoload']['staticVariables']['class'][__CLASS__]['path']}");
		//$this->readonly['easyData']=preg_replace('/^'.preg_quote($this->root,'/').'/','',realpath("{$this->readonly['autoload']['staticVariables']['class'][__CLASS__]['path']}/.."));
		
		$this->readonly['referer']=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$this->readonly['ini']="{$this->root}{$this->easyData}/ini";
		//Carrega as configurações de Ambiente
		$cmds=array('configIni','securyIni','hostIni');
		foreach ($cmds as $cmd) $this->$cmd();

		if (!isset($this->readonly['cr'])) $this->readonly['cr']="\r\n";
		if (!isset($this->readonly['tab'])) $this->readonly['tab']="\t";
		//print '<pre>'.print_r($this,true).'</pre>';exit;
	}
	function parse_ini_file($file){
		return ($f=$this->getFullFile($file))?parse_ini_file($f,true):false;
	}
	function getFullFile($file){
		if (
		 is_file($f=("{$this->readonly['pathAutoload']}/$file")) ||
		 is_file($f=("{$this->ini}/$file") )
		) return $f;
		return false;
	}
	function getUrlReferer($path){
		$root=preg_quote($this->root,'/');
		return preg_replace("/^{$root}/",'',$path);
	}
	function getDir($file){
		return dirname($file);
	}
	private function configIni() { $this->parser_ini($this->parse_ini_file('config.ini')); }
	private function securyIni() { $this->parser_ini($this->parse_ini_file('secury.ini')); }
	private function hostIni(){ $this->parser_ini($this->parse_ini_file('host.ini'),'hosts'); }
	function __get($nm) { if (isset($this->readonly[$nm])) return $this->readonly[$nm]; }
	function __set($nm, $val) {
		switch ($nm) {
			case 'domain': case 'user':
			if (!isset($this->readonly[$nm])) $this->readonly[$nm]=$val;
			break;
		}
		return;
	}
	private function parser_ini($ini,$att='readonly') {
		if (!$ini) return array();
		foreach ($ini as $k1=>$v1) {
			if (is_array($v1)) foreach ($v1 as $k2=>$v2) eval("\$this->{$att}[\"$k1\"][\"$k2\"]=\"$v2\";");
			else eval("\$this->{$att}[\"$k1\"]=\"$v1\";");
		}
	}
	public function getJScript(){
		if (!$this->mounted && !(isset($_REQUEST['tblData_outFormat']) && $_REQUEST['tblData_outFormat']!='')) {
			$this->readonly['mounted']=true;
			$cmd=array();
			$out=array("<script language='JavaScript' type='text/JavaScript'>","{$this->tab}window.path={");
			foreach ($this->print as $v) $cmd[]="{$this->tab}{$this->tab}{$v}: '".strtr($this->$v,array("\r"=>"\\r","\n"=>"\\n","\t"=>"\\t",'"'=>"\\\""))."'";
			$out[]=implode(",".$this->cr,$cmd);
			$out[]="$this->tab}$this->cr</script>$this->cr";
			return implode($this->cr,$out);
		}
	}
	function getFunctionDetails($func=false) {
		$out=array();
		if ($func==false) {
			$funcs = get_defined_functions();
			foreach ($funcs['user'] as $func) $out[$func]=$this->getFunctionDetails($func);
		}else {
			try {
				$func = new ReflectionFunction($func);
			} catch (ReflectionException $e) {
				return $out;
			}
			$args = array();
			foreach ($func->getParameters() as $param) $arg[$param->getName()]=array(
				'defaultValue'=>$param->isDefaultValueAvailable()?$param->getDefaultValue():null,
				'isOptional'=>$param->isOptional(),
				'isPassedByReference'=>$param->isPassedByReference(),
				'class'=>($o=$param->getClass())?$o->getName():null,
			);
			$out=array(
				'name'=>$func->name,
				'isInternal'=>$func->isInternal(),
				'isUserDefined'=>$func->isUserDefined(),
				'fileName'=>$func->getFileName(),
				'startLine'=>$func->getStartLine(),
				'endLine'=>$func->getEndLine(),
				'staticVariables'=>$func->getStaticVariables(),
				'returnsReference'=>$func->returnsReference(),
				'numberOfParameters'=>$func->getNumberOfParameters(),
				'numberOfRequiredParameters'=>$func->getNumberOfRequiredParameters(),
				'docComment'=>$func->getDocComment(),
				'parameters'=>$args,
			);
		}
		return $out;
	}
}

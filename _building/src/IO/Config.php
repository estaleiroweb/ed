<?php
namespace Sys;

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
	use \Traits\OO\Singleton;

	private $readonly;
	public $hosts=array();
	public $print=array('cr','tab','host','easyData','FCKeditor','css','js','imgs',);
	private $__mounted=false;

	private function __construct() {
		global $_;
		//$al=spl_autoload_functions(); $call=\System\Callable_Details::singleton(); $al=$call(is_array($al)?$al[0]:$al);
		//$this->readonly['autoload']=$this->getFunctionDetails($al);
		{$this->readonly=array(
			'pathAutoload'=>$_->dirName(),
			'filename'=>$s=$_->scripName(),
			'url'=>$_->url(),
			'fullurl'=>$_->fullUrl(),
			'referer'=>$_->referer(),
			'pathParts'=>$s=pathinfo($s),
			'file'=>$s['basename'],
			'path'=>$s['dirname'],
			'schema'=>$_->schema(),
			'host'=>$_->host(),
			'root'=>$_->root(),
			'cr'=>"\r\n",
			'tab'=>"\t",
		);}
		$this->readonly['site']=$this->readonly['pathAutoload']?$this->getUrlReferer($this->readonly['pathAutoload']):'/';
		
		//print_r($this->readonly);exit;
		
		//Carrega as configurações de Ambiente
		$cmds=array('configIni','securyIni','hostIni');
		foreach ($cmds as $cmd) $this->$cmd();

		if (!isset($this->readonly['cr'])) $this->readonly['cr']="\r\n";
		if (!isset($this->readonly['tab'])) $this->readonly['tab']="\t";
		//print '<pre>'.print_r($this,true).'</pre>';exit;
	}
	function __get($nm) { if (isset($this->readonly[$nm])) return $this->readonly[$nm]; }
	function __set($nm, $val) {
		switch ($nm) {
			case 'domain': case 'user':
			if (!isset($this->readonly[$nm])) $this->readonly[$nm]=$val;
			break;
		}
		return;
	}
	
	private function getUrlReferer($path){
		$root=preg_quote($this->root,'/');
		return preg_replace("/^{$root}/",'',$path);
	}
	private function configIni() { $this->parser_ini('config.ini'); }
	private function securyIni() { $this->parser_ini('secury.ini'); }
	private function hostIni()   { $this->parser_ini('host.ini','hosts'); }
	private function parser_ini($filename,$att='readonly') {
		$filename=$_->findFileDefault($filename);
		if(!$filename) return array();
		$ini=parse_ini_file($filename,true);
		//print_r($ini); exit;
		foreach ($ini as $k1=>$v1) {
			if (is_array($v1)) foreach ($v1 as $k2=>$v2) $this->exec("\$this->{$att}[\"$k1\"][\"$k2\"]",$v2);
			else $this->exec("\$this->{$att}[\"$k1\"]",$v1);
		}
	}
	private function exec($name,$value){
		global $_;
		
		$value=preg_replace('/\{\$this\[/','{$this->readonly[',$value);
		$exe="{$name}=\"$value\";";
		//print "CMD:\n\t$exe\n";
		return eval($exe);
	}
	
	public function getJScript(){
		if (!$this->__mounted && !(isset($_REQUEST['tblData_outFormat']) && $_REQUEST['tblData_outFormat']!='')) {
			$this->__mounted=true;
			$cmd=array();
			$out=array("<script language='JavaScript' type='text/JavaScript'>","{$this->tab}window.path={");
			foreach ($this->print as $v) $cmd[]="{$this->tab}{$this->tab}{$v}: '".strtr($this->$v,array("\r"=>"\\r","\n"=>"\\n","\t"=>"\\t",'"'=>"\\\""))."'";
			$out[]=implode(",".$this->cr,$cmd);
			$out[]="$this->tab}$this->cr</script>$this->cr";
			return implode($this->cr,$out);
		}
	}
}

<?php
class ExternalPlugins {
	protected $config,$OutHtml;
	protected $context='';
	protected $filesType=array(
		'css'=>'style', 
		'js'=>'script',
	);
	static private $done=array();
	
	public function __construct($context=null){
		if($context) $this->context=$context;
		$this->OutHtml=OutHtml::singleton();
		$this->config=loadFileDefault('config.ini');
		//show($this->config);
		$this->load_context($context);
	}
	public function load_context($context=null){
		if(!$context) $context=$this->context;
		if(!$context || @self::$done[$context]) return;
		self::$done[$context]=true;
		if(!@$this->config[$context]) $this->help();
		foreach($this->filesType as $k=>$v) $this->head($k,$v);
	}
	public function head($type,$headType){
		if(!array_key_exists($type,$this->config[$this->context])) return;
		$er='/^'.preg_quote($type.'File','/').'/';
		foreach($this->config[$this->context] as $k=>$v) if(preg_match($er,$k)) {
			$this->$headType($this->config[$this->context][$k],$type);
		}
	}
	public function script($file,$pathContext){
		$this->OutHtml->script($file,$this->config[$this->context][$pathContext]);
	}
	public function style($file,$pathContext){
		$this->OutHtml->style($file,$this->config[$this->context][$pathContext]);
	}
	public function help(){
		error('Arquivo config.ini não está configurado 

Adicione as linhas como exemplo:
['.$this->context.']
basedir="{$this[\'easyData\'][\'js\']}/base_path"
css="{$this[\''.$this->context.'\'][\'basedir\']}/path/css"
js="{$this[\''.$this->context.'\'][\'basedir\']}/path/js"
cssFile="file_name_min_without_extention"
cssFileTheme="file_name_min_without_extention"
cssFileXXX="file_name_min_without_extention"
jsFile="file_name_min_without_extention"',FATAL_ERROR);
	}
}
<?php
namespace DB;

class Dsn {
	use \Show;
	
	private static $instance=[];
	private $default=[];
	private $dsn=[];
	public static $file='dsn.ini';
	
	public static function singleton($file=null)   {
		if(!$file) $file=self::$file;
		if (!isset(self::$instance[$file])) {
			$c = __CLASS__;
			self::$instance[$file]=new $c($file);
		}
		return self::$instance[$file];
	}
	public function __construct($file){
		$this->dsn=\_::loadFileDefault($file);
		//show($this->dsn);
		if (isset($this->dsn['default'])) {
			$this->default=$this->dsn['default'];
			unset($this->dsn['default']);
		}
	}
	private function erroDsnFile($erro) {
		return "ERRO: $erro\nReconfigure config.ini ou abra o arquivo '".__FILE__."', \nna classe '".__CLASS__."' e propriedade '\$file', \npreencha corretamente com o caminho completo e nome de arquivo que cont�m as conex�es.\n";
	}
	public function __get($dsn){
		if (isset($this->dsn[$dsn])) return $this->convert2Line(array_merge($this->default,$this->dsn[$dsn])).'#'.$dsn;
	}
	public function __put($dsn,$value){ }
	public function __toString(){ return $this->show($this,false); }
	public function getAll(){
		$ret=[];
		foreach ($this->dsn as $k=>$v) $ret[$k]=$this->$k;
		return $ret;
	}
	public function getDefault(){ return $this->default; }
	public function convert2Line($p){
		if(@$p['url']) return $p['url'];
		return $this->mountURL(@$p['host'],@$p['port'],@$p['scheme'],@$p['user'],@$p['pass'],@$p['db'],$p);
	}
	public function mountURL($host=false,$port=false,$scheme=false,$user=false,$pass=false,$db=false,$parameters=[]){
		($scheme) || ($scheme=@$this->default['scheme']) || ($scheme='mysqli');
		($host)   || ($host  =@$this->default['host'])   || ($host=ini_get('mysqli.default_host')) || ($host='localhost');
		($port)   || ($port  =@$this->default['port']);
		($user)   || ($user  =@$this->default['user']);
		($pass)   || ($pass =@$this->default['pass']);
		($db)     || ($db    =@$this->default['db']);
		
		$host=rawurlencode($host);
		if($user) $host=rawurlencode($user).($pass==''?'':':'.rawurlencode($pass)).'@'.$host;
		$db=$db?'/'.rawurlencode($db):'';
		foreach($parameters as $k=>$v) if($v=='' || preg_match('/^(url|host|scheme|port|user|pass|db)$/',$k)) unset($parameters[$k]);
		$parameters=$parameters?'?'.http_build_query($parameters):'';
		return $scheme.'://'.$host.$db.$parameters;
	}
	public function splitURL($url){
		$out=parse_url($url);
		foreach($out as $k=>&$v) if(preg_match('/^(host|user|pass|db|fragment)$/',$k)) $v=rawurldecode($v);
		if(@$out['query']) parse_str($out['query'],$out['query']);
		if(@$out['path']) $out['db']=preg_replace(['/^\/+/','/\/+/'],['','.'],$out['path']);
		//if(@$out['path']) $out['db']=preg_replace('/^\/?([^\/]+).*?$/','\1',$out['path']);
		$out['url']=$url;
		return $out;
	}
}
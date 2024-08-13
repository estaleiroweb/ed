<?php
class ParseConf {
	public $save_cache=true;
	public $filename='';
	public $dirname='';
	public $group='';
	public $key='';
	public $process_sections=false;
	public $conf=array();
	public $errors=array();
	
	function __construct($filename_string=false,$process_sections=false,$save_cache=0){
		$this->process_sections=$process_sections;
		$this->save_cache=$save_cache;
		if(!isset($_SESSION['__ParseConf'])) $_SESSION['__ParseConf']=array();
		if($filename_string) $this->get_file($filename_string) || $this->get_string($filename_string);
	}
	function get_file($filename=false) {
		if(!$filename) $filename=$this->filename;
		$this->filename=$filename;
		if($this->save_cache && isset($_SESSION['__ParseConf'][$filename])) {
			verbose('Get from cache Parsed file '.$filename);
			$this->dirname=$_SESSION['__ParseConf'][$filename]['dirname'];
			$conf=$_SESSION['__ParseConf'][$filename]['conf'];
			if($conf) $this->conf=array_merge_recursive($this->conf,$conf);
		}
		else{
			verbose('Parse file '.$filename);
			$_SESSION['__ParseConf'][$filename]=array(
				'dirname'=>$this->dirname=dirname($filename),
				'conf'=>false,
			);
			if(!is_file($filename) || !($string=@file_get_contents($filename))) return false;
			$conf=$_SESSION['__ParseConf'][$filename]['conf']=$this->get_string($string,$this->process_sections);
		}

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
				if($r===false) print "#{$fnInc} $include: not exists{$GLOBALS['__autoload']->lf}";
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

	
	public static function ParseConf_file($filename,$process_sections=false) {
		$o=new ParseConf($filename,$process_sections);
		return $o->conf;
	}
	public static function ParseConf_string($string,$process_sections=false) {
		$o=new ParseConf(false,$process_sections);
		$o->get_string($string);
		return $o->conf;
	}
}

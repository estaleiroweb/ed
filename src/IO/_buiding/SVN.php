<?php
class SVN {
	public $cfgDir, $svnDir, $file, $mess, $debug;
	private $isOpen=false;
	
	function __construct($cfgDir='',$svnDir='',$debug=false){
		$this->debug=$debug;
		$this->mess="Update: ".strftime("%T %T");
		$this->open($cfgDir,$svnDir);
	}
	function pr($text){ if($this->debug) print $text; }
	function open($cfgDir='',$svnDir=''){
		if ($this->isOpen) return;
		if (!$cfgDir) $cfgDir=$this->cfgDir;
		if (!$svnDir) $svnDir=$this->svnDir;
		if (!$cfgDir || !$svnDir) return;
		$svnDir=preg_replace('/^(file:\/\/)?/i','file://',$svnDir);
		$this->cfgDir=$cfgDir; #/var/www/html/bkps/JUNIPER/svnCfg
		$this->svnDir=$svnDir; #/var/lib/svn/repositorios/Juniper
		if (!is_dir($this->cfgDir)) `mkdir -p {$this->cfgDir}`;
		if (is_dir($this->cfgDir)) {
			$this->pr(`svn checkout {$this->svnDir} {$this->cfgDir}`);
			$this->pr(`svn update {$this->cfgDir}`);
		} else die('ERRO no diretório');
		$this->isOpen=true;
	}
	function close(){
		if(!$this->isOpen) return;
		$this->pr(`cd {$this->cfgDir}; svn commit -m "{$this->mess}"`);
		$this->isOpen=false;
	}
	function createFile($file,$data){ file_put_contents("{$this->cfgDir}/$file",$data); }
	function update($file='') {
		if (!$file) $file=$this->file;
		if (!$this->isOpen || !$file) return;
		if ($file[0]!='/') $file="{$this->cfgDir}/$file";
		$this->pr(`svn info $file | grep "Repository Root"`?`svn update $file`:`svn add $file`);
	}
}
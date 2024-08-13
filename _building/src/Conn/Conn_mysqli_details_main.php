<?php
class Conn_mysqli_details_main extends OverLoadElements {
	static $instance=array();
	static $doLog=false;
	static $path='/var/www/easyData/conn_details'; 
	protected $indexTypes=array('INDEX'=>1,'KEY'=>1,'UNIQUE'=>2,'PRIMARY'=>4,'FOREIGN'=>8,'FULLTEXT'=>16,'SPATIAL'=>32,);
	protected $readonly=array();
	protected $protect=array('conn'=>null,'saveFile'=>false,'force'=>false,);
	protected $deltaUpdate=600;  /*segundos 600seg=10min*/
	
	static public function singleton($fullName,$objClass='table',$force=false) {
		$k="{$fullName}.{$objClass}";
		if (!array_key_exists($k,self::$instance)) {
			$fn=preg_replace('/[a-z]+$/i',$objClass,__CLASS__);
			self::$instance[$k]=new $fn($fullName,$force);
			$n=self::$instance[$k]->fullName.'.'.$objClass;
			if($n!=$k) self::$instance[$n]=&self::$instance[$k];
		}
		return self::$instance[$k];
	}
	function __construct($fullName,$force=false){
		$this->protect['force']=$force;
		$tbl=$this->splitFullName($fullName);
		$this->log();
		$this->setError(false);
		$class=preg_replace('/^.*_/i','',get_class($this));
		$dsn=$this->conn->dsn['dsnName'];
		$this->buildMainValues($class,$dsn,$tbl);
		if(file_exists($fullFileName=$this->getFullFileName())) $this->readonly=$this->loadFile($fullFileName);
		$this->buildMainValues($class,$dsn,$tbl);
		$this->readonly['fullName']=$this->getFullName();
		$this->checkUpdate($force);
		unset($this->protect['conn']);
	}
	function __destruct(){
		if($this->saveFile) {
			$this->mkdir($this->getFullPath());
			$this->saveFile($this->getFullFileName(),$this->readonly);
		}
	}
	protected function getFullPath(){ return self::$path."/{$this->readonly['dsn']}/{$this->readonly['db']}/{$this->readonly['class']}/{$this->readonly['name']}"; }
	protected function getFileName(){ return "{$this->readonly['name']}.{$this->readonly['class']}"; }
	protected function getFullFileName(){ return $this->getFullPath().'/'.$this->getFileName(); }
	protected function getFullName(){ return "`{$this->readonly['db']}`.`{$this->readonly['name']}`"; }
	protected function getVersion(){
		if(!@$this->protect['version']) $this->protect['version']=$this->conn->get_server_info()+0;
		return $this->protect['version'];
	}
	protected function getObj(){ return $this->readonly; }
	function buildMainValues($class,$dsn,$tbl){
		$this->readonly['class']=$class;
		$this->readonly['dsn']=$dsn;
		$this->readonly['db']=$tbl['db'];
		if(@$tbl['table']) $this->readonly['table']=$tbl['table'];
		$this->readonly['name']=$tbl['name'];
	}
	protected function existsFile($fullFileName){ return file_exists($fullFileName)?$fullFileName:false; }
	protected function dateUpdateFile($fullFileName){ return $fullFileName?filectime($fullFileName):false; }
	protected function dateAccessFile($fullFileName){ return $fullFileName?fileatime($fullFileName):false; }
	protected function loadFile($fullFileName){ return $fullFileName?unserialize(file_get_contents($fullFileName)):false; }
	protected function saveFile($fullFileName,$value){
		$this->log();
		return ;//FIXME retirar quando pronto
		file_put_contents($fullFileName,serialize($value));
	}
	protected function mkdir($path){ `mkdir -p "$path"`; }
	protected function renewDir($path){ 
		$this->mkdir($path);
		`rm -f "$path/*"`;
		return $path;
	}
	protected function getStatus(){ return false; }
	protected function rebuildObj(){ return false; }
	protected function dateUpdateObj(){ 
		$s=$this->getStatus();
		return $s?strtotime($s['dtUpdate']):false;
	}
	protected function checkUpdate($force){
		$this->log();//print get_class($this).':'.__FUNCTION__.'['.__LINE__."]: {$this->readonly['fullName']}\n";
		if($force || !@$this->readonly['dtUpdate'] || $this->hasUpdate()) {
			$this->readonly['dtUpdate']=$this->dateUpdateObj();
			if(!$this->error && $this->getStatus() && $this->rebuildObj() && $this->protect['saveFile']!==0) $this->protect['saveFile']=true;
		} 
	}
	protected function hasUpdate(){
		$dtUpdateFile=$this->dateUpdateFile($this->fullFileName);
		if( (time()-$dtUpdateFile) > $this->deltaUpdate ) {
			$dtObj=$this->dateUpdateObj();
			if($dtObj && $dtObj  > $dtUpdateFile) return true;
		}
		return false;
	}
	protected function renameDDL($ddl,$newFullName) { return $ddl; }
	protected function setError($error) { 
		$this->readonly['error']=$error;
		return false;
	}
	protected function log(){
		if(self::$doLog) $this->protect['log'][]=$this->called(1);
	}
	protected function decodeComment($str) { return strtr($str,array('\r'=>"\r",'\n'=>"\n",'\t'=>"\t","''"=>"'",)); }
	protected function getSourceSetEnum($type,&$length){
		$list=preg_replace(array('/^(set|enum)\(/i','/\)$/'),'',$type);
		$source=array();
		do {
			preg_match('/^(\'?)(.*?)(?<!\\\)\1(?:,(.*))?$/',$list,$ret);
			$list=stripslashes($ret[2]);
			$length=max($length,strlen($list));
			$source[]=$list;
			$list=@$ret[3];
		} while($list);
		return $source;
	}
	protected function rebuildTableName($table) {
		if(preg_match('/^(?:(`?)(?<db>[^`]+?)\1.)?(`?)(?<tbl>[^`]+?)\3$/',$table,$ret)) {
			if(!@$ret['db']) $ret['db']=$this->readonly['db'];
			return "`{$ret['db']}`.`{$ret['tbl']}`";
		}else return $table;
	}
	protected function splitFullName2($fullName,$field=false){
		if(preg_match('/^(?:`([^`]+)`|([^ \.]+))(?:\.(?:`([^`]+)`|([^ \.]+)))?(?:\.(?:`([^`]+)`|([^ \.]+)))?/',$fullName,$ret)){
			($p1=@$ret[1]) || ($p1=@$ret[2]);
			($p2=@$ret[3]) || ($p2=@$ret[4]);
			($p3=@$ret[5]) || ($p3=@$ret[6]);
			if($p3) return array('db'=>$p1,'table'=>$p3,'name'=>$p3);
			elseif($p2) {
				if($field) return array('db'=>$this->db,'table'=>$p1,'name'=>$p2);
				else return array('db'=>$p1,'table'=>$p2,'name'=>$p2);
			}
			else return array('db'=>$this->db,'table'=>$p1,'name'=>$p1);
		}
		else return false;
	}
	protected function splitFullName($fullName,$field=false){
		if($p=$this->buildSplitFullName($fullName)){
			($db=$this->db) || ($db=$this->conn->dsn['db']);
			if(@$p[2]) $out=array('db'=>$p[0],'table'=>$p[1],'name'=>$p[2]);
			elseif($field){
				if(@$p[1]) $out=array('db'=>$db,'table'=>$p[0],'name'=>$p[1]);
				else $out=array('db'=>$db,'table'=>'','name'=>$p[0]);
			}
			else {
				if(@$p[1]) $out=array('db'=>$p[0],'name'=>$p[1]);
				else $out=array('db'=>$db,'name'=>$p[0]);
			}
			($out['alias']=@$p['alias']) || ($out['alias']=$out['name']);
			return $out;
		}
		else return false;
	}
	protected function buildSplitFullName($fullName){
		if(preg_match('/^(?:`([^`]+)`|([a-z_][a-z0-9_]*))(?:\.(?:`([^`]+)`|([a-z_][a-z0-9_]*)))?(?:\.(?:`([^`]+)`|([a-z_][a-z0-9_]*)))?(?:\s*(?:\bas\s+)?(?:`([^`]+)`|\b([a-z_][a-z0-9_]*)))?$/i',$fullName,$ret)){
			$out=array();
			for($i=1;$i<7;$i+=2) if(($p=@$ret[$i]) || ($p=@$ret[$i+1])) $out[]=$p;
			if(($p=@$ret[7]) || ($p=@$ret[8])) $out['alias']=$p;
			return $out;
		}
		else return false;
	}
	protected function getErFieldLine(){
		$field='`(?<field>[^`]+)`\s*';
		$type='(?<type>\w+(?: BINARY)?(?:\(.*?\))?)';
		$charset='(?: CHARACTER SET (?<charset>\w+))?';
		$collate='(?: COLLATE (?<collate>\w+))?';
		$unsigned='(?: (?<unsigned>unsigned))?';
		$zerofill='(?: (?<zerofill>zerofill))?';
		$not_null='(?: (?<not_null>(?:NOT )?NULL))?';
		$auto_increment='(?: (?<auto_increment>AUTO_INCREMENT))?';
		$key='(?: (?<key>UNIQUE(?: KEY)?|(?:PRIMARY\b ?)?KEY))?';
		$default='(?: DEFAULT (?<default>\'.*?\'|\w+))?';
		$on_update_timestamp='(?: ON UPDATE (?<on_update_timestamp>\w+))?';
		$comment='(?: COMMENT \'(?<comment>.*)\')?';
		$column_format='(?: COLUMN_FORMAT (?<column_format>FIXED|DYNAMIC|DEFAULT))?';
		$storage='(?: STORAGE (?<storage>DISK|MEMORY|DEFAULT))?';
		return "/^\s*$field$type$charset$collate$unsigned$zerofill$not_null$auto_increment$key$default$on_update_timestamp$comment$column_format$storage{$this->getErReferenceLine()}$/i";
	}
	protected function getErReferenceLine(){
		return '(?:\bREFERENCES\s*(?<tableRefer>(?:`[^`]+`.)?`[^`]+`)\s*\(`(?<fieldsRefer>.+?)`\)\s*(?:\bMATCH\s+(?<match>FULL|PARTIAL|SIMPLE)\b\s*)?(?:\bON\s+DELETE\s+(?<on_delete>RESTRICT|CASCADE|SET\s+NULL|NO\s+ACTION)\b\s*)?(?:ON\s+UPDATE\s+(?<on_update>RESTRICT|CASCADE|SET\s+NULL|NO\s+ACTION)\b)?)?';
	}
	protected function getErConstraintLine(){
		return '(?:\bCONSTRAINT\s*(?<constraint>(?:`[^`]+`.)?`[^`]+`)\s*)?';
	}
	protected function getErIndexLine(){
		$key='\b(?<key>PRIMARY\s+KEY|FOREIGN\s+KEY|(?:UNIQUE|FULLTEXT|SPATIAL)(?:\s+(?:INDEX|KEY))?|INDEX|KEY)\b\s*';
		$index_name='(?:`(?<index_name>[^`]+)`\s*)?';
		$index_type='(?:\bUSING\s+(?<index_type>BTREE|HASH)\b\s*)?';
		$fields='\(`(?<fields>.+?)`\)\s*';
		return "/^\s*{$this->getErConstraintLine()}$key$index_name$index_type$fields(?<index_options>(?:.|\s)*?){$this->getErReferenceLine()}\s*$/i";
	}
	protected function getErCheckLine(){
		return "/^\s*{$this->getErConstraintLine()}CHECK\s*\((?<expr>.*)\)\s*$/i";
	}
}
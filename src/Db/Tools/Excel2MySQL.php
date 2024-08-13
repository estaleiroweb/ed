<?php
class Excel2MySQL {
	private $protected=array(
		'file'=>'',
		'db'=>'',
		'table'=>'',
		'tableFull'=>'',
		'droptTable'=>false,
		'linesInsert'=>300,
		'analize'=>array(),
	);
	private $head=array();
	private $content=array();
	private $conn;
	private $isAnalyzed=false;
	
	function __construct($file='',$conn=null,$db=''){
		$this->file=$file;
		$this->conn=$conn;
		$this->db=$db;
	}
	function __toString(){
		return print_r(array(
			'conn'=>$this->conn?@$this->conn->dsn:'',
			'protected'=>$this->protected,
			'head'=>$this->head,
			'content'=>$this->content,
		),true); 
	}
	private function __get($nm){
		$fnSet='get'.ucfirst($nm);
		if(method_exists ($this,$fnSet)) return $this->$fnSet();
		elseif(isset($this->protected[$nm])) return $this->protected[$nm];
	}
	private function __set($nm,$val){
		$fnSet='set'.ucfirst($nm);
		if(method_exists($this,$fnSet)) $this->$fnSet($val);
		elseif(isset($this->protected[$nm])) $this->protected[$nm]=$val;
	}
	function setFile($file){
		if(is_file($file)) {
			$this->isAnalyzed=false;
			$this->protected['file']=$file;
			$this->content=file($file);
			$this->head=$this->parserLine(array_shift($this->content),'addLimitField');
		}
	}
	function setDb($dbTable){
		if(preg_match('/(?:(?:(`)(.+?)\1|(\w+))\.)?(?:(`)(.+?)\4|(\w+))/',$dbTable,$ret)){
			$this->protected['tableFull']=$dbTable;
			$this->protected['db']=$ret[2]?$ret[2]:@$ret[3];
			$this->protected['table']=$ret[5]?$ret[5]:@$ret[6];
		}
	}
	function setLinesInsert($lines) {
		$lines=(int)$lines;
		if($lines>0 && $lines<=2000) $this->protected['linesInsert']=$lines;
	}
	function setDsn($dsn){ $this->conn=Conn::dsn($dsn); }
	function analyze(){
		if($this->isAnalyzed) return;
		$this->isAnalyzed=true;
		print "Analyzing table ";
		$numFields=count($this->head);
		foreach($this->content as $k=>&$line) {
			$line=$this->parserLine($line);
			if(count($line)<>$numFields) {
				$out=array();
				$i=0;
				while(isset($this->head[$i]) || isset($line[$i])) {
					$out[isset($this->head[$i])?$this->head[$i]:$i]=@$line[$i];
					$i++;
				}
				$this->error("File doesn't match head with its body\n Linha $k:".print_r($out,true));
			}
			$line='('.implode(', ',$line).')';
		}
		print "OK\n";
	}
	function error($msg){
		die("ERROR: $msg\nSintaxe: \n{GLOBALS['argv'][0]} <arquivo> <dsn_conn> <database.table>\n");
	}
	function export(){
		$tableFull=$this->tableFull;
		$conn=$this->conn;
		if(!$tableFull) $this->error("There isn't table\n");
		if(!$conn) $this->error("There isn't connection\n");
		$this->analyze();

		if(is_string($conn)) $this->conn=Conn::dsn($conn);
		elseif(is_array($conn)) $this->conn=Conn::singleton($conn);
		
		print "Creating table ";
		$db=$this->db;
		if($this->droptTable) {
			$sql="DROP TABLE IF EXISTS {$tableFull}";
			$this->conn->query($sql);
		} else {
			$sql='SHOW TABLES '.($db?"from `$db`":'')." like '{$this->table}'";
			$res=$this->conn->query($sql);
			$hasTable=$res->fetch_assoc();
			$res->close();
			if($hasTable) $this->error("Table {$tableFull} just there is.\n");
		}

		$noname="semnome01";
		$fields=array();
		$notAutoIncrement=false;
		foreach($this->head as $k=>$v) {
			if($v=='``') {
				$v="`$noname`";
				$noname++;
			}
			$fields[]="$v \t{$this->protected['analize'][$k]->result($notAutoIncrement)}";
			$notAutoIncrement|=$this->protected['analize'][$k]->isAutoIncrement;
		}
		$sql="CREATE TABLE IF NOT EXISTS {$tableFull} ( \n\t".implode(", \n\t",$fields)." \n)";
		
		$this->conn->ping();
		$this->conn->query($sql);
		$this->conn->commit();
		print "OK\n";

		print "Inserting table ";
		//Inserindo...
		$tam=count($this->content);
		$inc=$this->linesInsert;
		$cmd="INSERT IGNORE {$tableFull} VALUES \n";
		for($inicio=0;$inicio<$tam;$inicio+=$inc) {
			$sql=$cmd.implode(", \n",array_slice($this->content,$inicio,$inc));
			//print "$sql\n";
			$this->conn->query($sql);
		}
		$this->conn->commit();
		print "OK\n";
		/*
		$res=$this->conn->query("select * from $tableFull");
		while($line=$res->fetch_assoc()) print_r($line);
		$res->close();
		*/
	}
	private function addLimitField($index,$field){ 
		$this->protected['analize'][$index]=new DataFieldAnalyze();
		$field=trim($field);
		return "`$field`"; 
	}
	private function escape_string($index,$string){ 
		if(isset($this->protected['analize'][$index])) {
			$this->protected['analize'][$index]->add($string);
			return '"'.escapeString($string).'"'; 
		} else return 'NULL';
	}
	//private function escape_string($string){ return '"'.($string).'"'; }
	private function parserLine($line,$fn='escape_string'){
		$out=array();
		$line=str_replace('""',chr(1),trim($line).';');
		if(preg_match_all('/(?:"(?<aspas>(?:.|\s)*?)"|(?<comma>[^;]*));/',$line,$ret,PREG_SET_ORDER)) {
			foreach($ret as $k=>$value) {
				$value['comma']=trim(@$value['comma']);
				$value=str_replace(chr(1),'"',$value['aspas']?$value['aspas']:@$value['comma']);
				//print "$value : {$this->$fn($value)}\n";
				$out[]=$this->$fn($k,$value);
			}
		}
		return $out;
	}
}
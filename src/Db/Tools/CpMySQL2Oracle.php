<?php //spo11 => ngnisStaging
class CpMySQL2Oracle extends Parameters {
	public $connFrom,$connTo;
	public $dtUpdateDefault='DtUpdate'; //Pode ser DtUpdate,DtGer,DtOutro
	public $dbLink='';

	public $maxRows=500;
	private $tblFrom,$resFrom,$tblTo,$tmpTo,$mssRes,$hasTableTo,$dnsFrom;
	private $key='';
	private $set='';
	private $cl="\r                                                                                                           \r";
	private $idIndex=0;
	private $prelabel='MYSQYNC';
	private $countIndex=0;

	function __construct($dsnFrom,$dsnTo=''){
		parent::__construct();
		$this->countIndex=strlen($this->prelabel)+1;
		$this->openFromConnection($dsnFrom);
		if($dsnTo) $this->openToConnection($dsnTo);
	}
	function __destruct(){
		$this->close();
	}
	function getTime(){ return '--['.trim(`date "+%F %T"`).']'; }
	function openFromConnection($dsn){
		$this->dnsFrom=$dsn;
		$this->pr($this->getTime()."Conecting to Mysql $dsn\n");
		$this->connFrom=Conn::dsn($dsn);
		if(!preg_match('/mysqli?/',$this->connFrom->dsn['dbType'])) die("Parametro '$dsn' nao e MySQL\n");
	}
	function reopenFromConnection(){ $this->connFrom=Conn::dsn($this->dnsFrom); }
	function openToConnection($dsn){
		$this->pr($this->getTime()."Conecting to Oracle $dsn\n");
		$this->connTo=Conn::dsn($dsn);
		if(!preg_match('/oracle/',$this->connTo->dsn['dbType'])) die("Parametro '$dsn' nao e Oracle\n");
	}	
	function queryTo($sql){
		if(is_array($sql)) foreach($sql as $v) $this->queryTo($v);
		elseif($this->connTo) $this->connTo->query($sql);
		if($this->superDebug || !$this->connTo) print "$sql\n/\n";
	}
	function checkToTable($tbl){
		if (!$this->connTo) return 1;
		$line=$this->connTo->fastLine("SELECT COUNT(1) as QUANT FROM all_tables{$this->dbLink} where table_name='{$tbl}'");
		return @$line['QUANT']+0;
	}
	function captureDetails($res){//FIXME
		$wAdd=2;
		$finfo = $res->fetch_fields();
		foreach ($finfo as $position=>$f) {
			$width=$height=$type=$source=$scale=$length=$max_length=$min_length=$precision=null;

			$f->field=$f->name?$f->name:$f->orgname;
			$f->fullName=$f->table?"`{$f->table}`.`{$f->orgname}`":'';
			$f->position=$position;
			$f->typeNum=$f->type;
			$f->flagsNum=$f->flags;
			$f->type=null;
		}
		return $finfo;
	}
	function getIdIndex($id){
		if($this->connTo) while (true) {
			$line=$this->connTo->fastLine("SELECT COUNT(1) QUANT FROM ALL_INDEXES WHERE INDEX_NAME='{$this->prelabel}$id'");
		//print_r($line);
			if($line['QUANT']==0) return $this->idIndex=$id;
			$id++;
			$line=$this->connTo->fastLine($sql="
				SELECT * FROM (
					SELECT ROWNUM ID, T.* FROM (
						SELECT INDEX_NAME, SUBSTR(INDEX_NAME,{$this->countIndex}) NUM
						FROM ALL_INDEXES 
						WHERE INDEX_NAME LIKE '{$this->prelabel}%' 
						ORDER BY SUBSTR(INDEX_NAME,{$this->countIndex})+0
					) T
				) T
				WHERE ID>=$id AND NUM!=ID AND ROWNUM=1 
			");
		//print $sql;print_r($line);
			if(!@$line['ID']) $line=$this->connTo->fastLine($sql="
				SELECT MAX(SUBSTR(INDEX_NAME,{$this->countIndex})+1) ID 
				FROM ALL_INDEXES 
				WHERE INDEX_NAME LIKE '{$this->prelabel}%'
			");
		//print $sql;print_r($line);
			$id=$line['ID']+0;
		} 
		return $this->idIndex=$id;;
	}
	function mountToName($tblTo){
		$tblTo=strtoupper($tblTo);
		$tam=30;
		if(strlen($tblTo)<=$tam) return str_replace('.','_',$tblTo);
		$tblTo=str_replace(array('_','.'),array('','_'),$tblTo);
		if(strlen($tblTo)<=$tam) return $tblTo;
		$s=(array)explode('_',$tblTo);
		if(isset($s[1]) && count($s[0])<=3) $tblTo=$s[1];
		return substr($tblTo,0,$tam);
	}
	function buildTablesName($tblFrom,$tblTo){
		if(is_numeric($tblFrom)) $tblFrom=$tblTo;
		if(!$tblTo) $tblTo=$tblFrom;
		$this->tblFrom=$tblFrom;
		if($this->dbLink && $this->dbLink[0]!='@') $this->dbLink='@'.strtoupper($this->dbLink);
		$this->tblTo=$this->mountToName($tblTo);
		$this->tmpTo='CPMYSQL2ORACLE';
		if($this->checkToTable($this->tmpTo)) $this->queryTo("DROP TABLE {$this->tmpTo}{$this->dbLink}");
		$this->hasTableTo=$this->checkToTable($this->tblTo);
		$this->createTableTo();
	}
	function createTableTo(){
		$res=$this->connFrom->query("SHOW CREATE TABLE {$this->tblFrom}");
		$table=$res->fetch_row();
		$res->close();
		if(!$table || count($table)>2) return '';
		$out=array();
		$out[0]=strtoupper(preg_replace(
			array(
				'/.+$/i',
				'/\'{2}/i',
				'/( +(?<!`)\b(unsigned|AUTO_INCREMENT|NOT NULL|USING BTREE|CHARACTER SET \w+|ZEROFILL|DEFAULT (b?\'[^\']*?\'|NULL|CURRENT_TIMESTAMP)|ON UPDATE CURRENT_TIMESTAMP|COMMENT *\'[^\']*?\'))+/i',
				'/(?<=`)\b(LEVEL|FROM|TO|KEYS|GROUP|COMMENT|RAW|TABLE)\b/i',
				'/(?<!`)\b((MEDIUM|LONG)?TEXT)\b/i',
				'/(?<!`)\b(VARCHAR|VARBINARY)\b/i',
				'/(?<!`)\bTINYTEXT\b/i',
				'/(?<!`)\bTEXT\b/i',
				'/(?<!`)\b(DATE(TIME)?|TIME(STAMP)?)\b(\(\d+\))?/i',
				'/(?<!`)\b(ENUM|SET)\b\([^\)]*?\)/i',
				'/(?<!`)\b(BIGINT|TINYINT|SMALLINT|MEDIUMINT|INTEGER|INT|NUMERIC|YEAR|DECIMAL|DOUBLE|REAL|FLOAT)\b/i',
				'/(?<!`)\bBIT\b/i',
				'/(?<!`)\b((TINY|MEDIUM|LONG)?BLOB)\b/i',
				'/(?<!`)\bDEFAULT\b "/i',
				'/(?<=`)\b(\d+)/',
				'/`/',
			),
			array(
				')','"','',
				'R_\1',
				'CLOB','VARCHAR2','VARCHAR2(255)','VARCHAR2(65535)','DATE','VARCHAR2(255)','NUMBER','RAW','BLOB',
				'','N_\1','',
			),
			$table[1]
		));

		//FIXME: CONSTRAINT `fk_tb_ContatosGrp-tb_Contatos` FOREIGN KEY (`idContatos`) REFERENCES `tb_ContatosGrp` (`idContatos`) ON DELETE CASCADE ON UPDATE CASCADE
		$out[0]=preg_replace('/ *CONSTRAINT.*/i','',$out[0]);

		if(preg_match_all('/(?<!PRIMARY)(?:\s+UNIQUE|\s+FULLTEXT)*\s+\bKEY\b.*/i',$out[0],$ret,PREG_SET_ORDER)) {
			preg_match('/CREATE TABLE ([^\(]*)/i',$out[0],$retTable);
			$keysDone=array();
			foreach($ret as $item){
				$out[0]=str_replace($item[0],'',$out[0]);
				if(preg_match('/^\s*(?:(\sUNIQUE)|(\sFULLTEXT))*(\s+KEY)\s+([^ ]*)(.*?)\s*,?\s*$/i',$item[0],$retKey)) {
					if($retKey[2]) continue;
					if(!isset($keysDone[$retKey[5]])) {
						$out[]="CREATE{$retKey[1]} INDEX {$this->prelabel}{$this->getIdIndex(++$this->idIndex)} ON {$this->tmpTo} {$retKey[5]}";
						$keysDone[$retKey[5]]=$retKey[5];
					}
				}
			}
		}
		
		$out[0]=preg_replace(array('/,\s*\)\s*/','/CREATE TABLE ([^\(]*)/i'),array("\n)","CREATE TABLE {$this->tmpTo}"),$out[0]);
		//print "-- {$this->tblTo}\n";print_r($out);print "\n";
		$this->queryTo($out);
	}
	function trField($oField){
		$len=$oField->length;
		if(preg_match('/(big|tiny|small|mediunm)?int(eger)?|numeric|year/i',$oField->vartype)) return 'NUMBER('.($len?$len:19).',0)';
		if(preg_match('/DATE|TIME/i',$oField->vartype)) return 'DATE';
		if(preg_match('/VARCHAR|TINYTEXT/i',$oField->vartype)) return 'VARCHAR2('.($len?$len:255).')';
		if(preg_match('/TEXT/i',$oField->vartype)) return 'VARCHAR2('.($len?$len:65535).')';
		if(preg_match('/CHAR/i',$oField->vartype)) return 'CHAR('.($len?$len:32).')';
		if(preg_match('/ENUM|SET/i',$oField->vartype)) return 'VARCHAR2(255)';
		if(preg_match('/DECIMAL|DOUBLE|REAL|FLOAT/i',$oField->vartype)) return 'FLOAT('.($len?$len:38).','.($oField->decimals+0).')';
		if(preg_match('/BIT/i',$oField->vartype)) return 'RAW('.($len?$len:1).')';

		if(preg_match('/MEDIUMTEXT/i',$oField->vartype)) return 'CLOB('.($len?$len:16777215).')';
		if(preg_match('/LONGTEXT/i',$oField->vartype)) return 'CLOB('.($len?$len:4294967295).')';
		if(preg_match('/(TINY)?BLOB/i',$oField->vartype)) return 'BLOB('.($len?$len:255).')';
		if(preg_match('/MEDIUMBLOB/i',$oField->vartype)) return 'BLOB('.($len?$len:16777215).')';
		if(preg_match('/LONGBLOB/i',$oField->vartype)) return 'BLOB('.($len?$len:4294967295).')';
		return 'VARCHAR2('.($len?$len:65535).')';
	}
	function copyTableAll($tables){
		$tables=(array)$tables;
		foreach($tables as $tblFrom=>$tblTo) $this->copyTable($tblFrom,$tblTo);
	}
	function copyTable($tblFrom,$tblTo='',$fn=''){ 
		$this->reopenFromConnection();
		$this->buildTablesName($tblFrom,$tblTo);

		$this->resFrom=$this->connFrom->query("SELECT * FROM {$this->tblFrom}");
		$this->mssRes="\r{$this->getTime()}Copping table {$this->tblTo}";
		$cont=0;
		$recCount=$this->resFrom->num_rows();
		while($line=$this->resFrom->fetch_assoc()) $this->insertLine($line,++$cont,$recCount);
		$this->pr("\n");
		$this->resFrom->close();

		if($this->hasTableTo) $this->queryTo("DROP TABLE {$this->tblTo}{$this->dbLink}");
		$this->queryTo("ALTER TABLE {$this->tmpTo}{$this->dbLink} RENAME TO {$this->tblTo}{$this->dbLink}");
	}
	function escapeText($text){
		return preg_replace(array('/[^ -~]+/','/\'/','/^$/'),array('','"',' '),$text);
	}
	function insertLine($line,$cont,$recCount) {
		$this->pr($this->mssRes.($this->superDebug?'':" $cont/$recCount"));
		foreach($line as $k=>$v) if(is_null($v)) $line[$k]='NULL';
		elseif($v=='0000-00-00 00:00:00') $line[$k]="''";
		else $line[$k]='\''.$this->escapeText(substr($v,0,2000)).'\'';
		$this->queryTo("INSERT INTO {$this->tmpTo}{$this->dbLink} VALUES (".implode(',',$line).')');
	}	
	function close(){
		$this->pr($this->getTime()."Fim\n");
		@$this->connFrom->close();
		@$this->connTo->close();
	}
}

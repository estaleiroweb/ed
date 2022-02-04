<?php
class Conn_mysqli extends Conn_Main {
	public $maxInsert=200;
	
	public function __construct($splitConn=null){
		parent::__construct($splitConn);
		//print_r($this->readOnly); 
		verbose("MySQL Conn: {$this->readOnly['user']}@{$this->readOnly['host']}:{$this->readOnly['port']}/{$this->readOnly['db']}#socket={$this->readOnly['socket']}");
		$this->readOnly['conn']=new mysqli(
		$this->readOnly['host'],
		$this->readOnly['user'],
		$this->readOnly['pass'],
		$this->readOnly['db'],
		$this->readOnly['port'],
		$this->readOnly['socket']
		);
		$er='Withou Connection';
		if (!$this->readOnly['conn'] || ($er=$this->error())) $this->fatalError($er);
	}
	public function close(){
		if($this->readOnly['conn']) {
			@$this->readOnly['conn']->close();
			parent::close();
		}
	}
	public function select_db($db){
		//show($this->ping());
		parent::select_db($db);
		$this->readOnly['conn']->select_db($db);
	}
	public function escape_string($texto){ 
		$e=mb_detect_encoding($texto);
		if($e!='ASCII' && $GLOBALS['encode']!=$e) $texto=mb_convert_encoding($texto,$GLOBALS['encode'],$e);
		return escapeString($texto); 
	}
	public function error() { return @mysqli_connect_error().@$this->readOnly['conn']->error; }
	public function errno() { 
		($out=@mysqli_connect_errno()) || ($out=@$this->readOnly['conn']->errno);
		return $out;
	}
	
	public function get_database(){ 
		$out=$this->fastLine('SELECT DATABASE() db');
		return @$out['db']; 
	}
	
	public function affected_rows(){ return $this->readOnly['conn']->affected_rows; }
	public function insert_id(){ return $this->readOnly['conn']->insert_id; }
	public function get_client_info(){ return $this->readOnly['conn']->host_info; }
	//function procedure($pc){}	
	
	public function multi_query($sql){ return $this->readOnly['conn']->multi_query($sql); }
	
	public function get_server_info(){ return $this->readOnly['conn']->get_server_info(); }
	public function ping(){ return @$this->readOnly['conn']->ping(); }
	public function get_charset(){return @$this->readOnly['conn']->get_charset(); }
	public function set_charset($charset){return @$this->readOnly['conn']->set_charset($charset); }
	public function clearResults(){ while(@$this->readOnly['conn']->next_result()==0); }
	public function mountComparation($keys,$aliasSource=null,$aliasTarget=null,$div=' AND ') {
		($aliasSource) || ($aliasSource='s');
		($aliasTarget) || ($aliasTarget='t');
		if(!is_array($keys)) $keys=preg_split('/\s*[,;]\s*/',$keys);
		foreach($keys as $keySource=>&$keyTarget) {
			if(is_numeric($keySource)) $keySource=$keyTarget;
			$keyTarget="{$aliasTarget}.{$keyTarget}={$aliasSource}.{$keySource}";
		}
		return implode($div,$keys);
	}
	public function insertLine($tbl,$line=false) {
		static $sql=array();
		if($line) $sql[$tbl][]=$this->mountValueInsertLine($line);
		if(@$sql[$tbl] && (!$line || count(@$sql[$tbl])>=$this->maxLinesInsert)) {
			$this->query("INSERT IGNORE $tbl VALUES \n".implode(", \n",$sql[$tbl]));
			$sql[$tbl]=array();
			return true;
		}
		return false;
	}
	
	public function dbTbl(&$tbl){
		if(!preg_match('/(`[^`]+`|\w+)(?:\.(`[^`]+`|\w+))?/',$tbl,$ret)) die("Erro de formato: $tbl\n");
		array_shift($ret);
		$ret=preg_replace('/^`([^`]+)`$/','\1',$ret);
		$db=count($ret)>1?array_shift($ret):$this->db;
		if(!$db) die("Daba base não identificado: $tbl\n");
		$tb=array_shift($ret);
		if(!$tb) die("Tabela não identificada: $tbl\n");
		return $db;
	}
	public function getFields($tbl){
		$db=$this->splitDbTbl($tbl);
	}
	public function existsTable($tbl){
		$db=$this->splitDbTbl($tbl);
		return $this->fastValue("
			SELECT COUNT(1) q
			FROM information_schema.TABLES t
			WHERE t.TABLE_SCHEMA={$this->addQuote($db)} 
			AND t.TABLE_TYPE='BASE TABLE'
			AND t.TABLE_NAME={$this->addQuote($tbl)}
		");
	}
	public function showDatabases(){
		$out=$this->query_all('
		SELECT 
		s.SCHEMA_NAME `SCHEMA`, 
		s.CATALOG_NAME `DOMAIN`, 
		NULL `OWNER`, 
		s.DEFAULT_CHARACTER_SET_NAME,
		s.DEFAULT_COLLATION_NAME,
		NULL DEFAULT_CHARACTER_SET_CATALOG, 
		NULL DEFAULT_CHARACTER_SET_SCHEMA,
		s.SQL_PATH
		FROM information_schema.SCHEMATA s
		ORDER BY s.SCHEMA_NAME
		',false);
		if(is_null($out)) {
			$res=$this->query('SHOW DATABASES');
			$out=array();
			while($line=$res->fetch_row()) {$out[]=array(
				'SCHEMA'=>$line[0],
				'DOMAIN'=>'def',
				'OWNER'=>null,
				'DEFAULT_CHARACTER_SET_NAME'=>'',
				'DEFAULT_COLLATION_NAME'=>'',
				'DEFAULT_CHARACTER_SET_CATALOG'=>null,
				'DEFAULT_CHARACTER_SET_SCHEMA'=>null,
				'SQL_PATH'=>'',
			);}
			$res->close();
		}
		return $out;
	}
	public function showTables($db=null){
		if(!$db) $db=$this->db;
		if(!$db) return;
		
		$out=$this->query_all($sql="
		SELECT
			t.TABLE_NAME `TABLE`, 
			t.TABLE_CATALOG `DOMAIN`, 
			t.TABLE_SCHEMA `SCHEMA`, 
			t.TABLE_TYPE `TYPE`, 
			t.ENGINE, 
			t.VERSION, 
			t.ROW_FORMAT, 
			t.TABLE_ROWS `ROWS`, 
			t.AVG_ROW_LENGTH, 
			t.DATA_LENGTH, 
			t.MAX_DATA_LENGTH, 
			t.INDEX_LENGTH, 
			t.DATA_FREE, 
			t.AUTO_INCREMENT, 
			t.CREATE_TIME, 
			t.UPDATE_TIME, 
			t.CHECK_TIME, 
			t.TABLE_COLLATION COLLATION, 
			t.CHECKSUM, 
			t.CREATE_OPTIONS, 
			t.TABLE_COMMENT `COMMENT`
		FROM information_schema.TABLES t
		WHERE t.TABLE_SCHEMA={$this->addQuote($db)} AND t.TABLE_TYPE='BASE TABLE'
		ORDER BY t.TABLE_NAME
		",false);
		if(is_null($out)) {
			$res=$this->query('SHOW TABLES FROM `'.$db.'`');
			$out=array();
			while($line=$res->fetch_row()) {$out[]=array(
				'TABLE'=>$line[0],
				'DOMAIN'=>'def',
				'SCHEMA'=>$db,
				'TYPE'=>'BASE TABLE',
				'ENGINE'=>null,
				'VERSION'=>null,
				'ROW_FORMAT'=>null,
				'ROWS'=>null,
				'AVG_ROW_LENGTH'=>null,
				'DATA_LENGTH'=>null,
				'MAX_DATA_LENGTH'=>null,
				'INDEX_LENGTH'=>null,
				'DATA_FREE'=>null,
				'AUTO_INCREMENT'=>null,
				'CREATE_TIME'=>null,
				'UPDATE_TIME'=>null,
				'CHECK_TIME'=>null,
				'COLLATION'=>null,
				'CHECKSUM'=>null,
				'CREATE_OPTIONS'=>null,
				'COMMENT'=>null,
			);}
			$res->close();
		}
		return $out;
	}
	public function showViews($db=null){
		if(!($db=$db?$db:$this->db)) return;
		
		return $this->query_all("
		SELECT
		t.TABLE_NAME `TABLE`, 
		t.TABLE_CATALOG `DOMAIN`, 
		t.TABLE_SCHEMA `SCHEMA`, 
		t.TABLE_TYPE `TYPE`, 
		t.ENGINE, 
		t.VERSION, 
		t.ROW_FORMAT, 
		t.TABLE_ROWS `ROWS`, 
		t.AVG_ROW_LENGTH, 
		t.DATA_LENGTH, 
		t.MAX_DATA_LENGTH, 
		t.INDEX_LENGTH, 
		t.DATA_FREE, 
		t.AUTO_INCREMENT, 
		t.CREATE_TIME, 
		t.UPDATE_TIME, 
		t.CHECK_TIME, 
		t.TABLE_COLLATION COLLATION, 
		t.CHECKSUM, 
		t.CREATE_OPTIONS, 
		t.TABLE_COMMENT `COMMENT`
		FROM information_schema.TABLES t
		WHERE t.TABLE_SCHEMA={$this->addQuote($db)} AND t.TABLE_TYPE!='BASE TABLE'
		ORDER BY t.TABLE_NAME
		",false);
	}
	public function showFunctions($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showProcedures($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showEvents($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function showAllObjects($db=null){
		if(!($db=$db?$db:$this->db)) return;
	}
	public function buildSQL_Table(){
		$argv=func_get_args();
		if(@$argv[1]) {
			$tbl=$argv[1];
			($db=$argv[0]) || ($db=$this->db);
			} elseif(@$argv[0]){
			$tbl=$argv[0];
			$db=$this->db;
		} else return;
		
		{//Get Privileges
			$user=$this->addQuote(preg_replace('/(.+)@(.+)/','\'\1\'@\'\2\'',$this->fastValue('SELECT CURRENT_USER()')));
			$dbPriv=$this->addQuote($db);
			$tblPriv=$this->addQuote($tbl);
			$tablePrivilege=$this->fastValue("SELECT IFNULL(
			(SELECT 'GLOBAL' FROM information_schema.USER_PRIVILEGES pu WHERE pu.PRIVILEGE_TYPE='SELECT' AND pu.GRANTEE=$user),
			IFNULL(
			(SELECT 'SCHEMA' FROM information_schema.SCHEMA_PRIVILEGES WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND GRANTEE=$user),
			(SELECT 'TABLE'  FROM information_schema.TABLE_PRIVILEGES  WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND TABLE_NAME=$tblPriv AND GRANTEE=$user)
			)
		) p");}
		if($tablePrivilege) $fields='*';
		else {
			$res=$this->query("SELECT * FROM information_schema.COLUMN_PRIVILEGES WHERE PRIVILEGE_TYPE='SELECT' AND TABLE_SCHEMA=$dbPriv AND TABLE_NAME=$tblPriv AND GRANTEE=$user");
			$fields=array();
			while($line=$res->fetch_assoc()) $fields[]=$this->fieldDelimiter($line['COLUMN_NAME']);
			$res->close();
			if(!$fields) return;
			$fields=implode(', ',$fields);
		}
		
		return "SELECT $fields \nFROM {$this->buildTableName($db,$tbl)}";
	}
}
class Conn_mysqli_result extends Conn_Main_result {
	public $data_flag=array(
	1=>'NOT_NULL',        // can't be NULL
	2=>'PRIMARY_KEY',     // is part of a primary key
	4=>'UNIQUE_KEY',      // is part of a unique key
	8=>'MULTIPLE_KEY',    // is part of a key
	16=>'BLOB',            // is a blob
	32=>'UNSIGNED',        // is unsigned
	64=>'ZEROFILL',        // is zerofill
	128=>'BINARY',          // is binary  
	256=>'ENUM',            // is an enum
	512=>'AUTO_INCREMENT',  // is a autoincrement field
	1024=>'TIMESTAMP',       // is a timestamp
	);
	public function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		if(@$this->conn->multi_query($sql)) $this->res=@$dadObj->store_result();
		$this->verifyError($sql);
		/*mysqli_result Object(
			[current_field] => 0
			[field_count] => 5
			[lengths] => Array(
			[0] => 3
			[1] => 3
			[2] => 1
			[3] => 19
			[4] => 23
			)
			[num_rows] => 181
			[type] => 0
		)*/
	}
	public function fields(){
		$out=array();
		if($this->res) {
			$o=@$this->res->fetch_fields();
			if($o) foreach($o as $obj) $out[]=$obj->orgname?$obj->orgname:$obj->name;
		}
		return $out;
	}
	public function trFlag($flag){
		$out=array();
		foreach($this->data_flag as $k=>$v) if($flag & $k) $out[$k]=$v;
		return $out;
	}
	public function reccount(){ 
		$out=$this->oConn->fastLine('SELECT FOUND_ROWS() c');
		return @$out['c'];
	}
	public function free_result(){ return @$this->res->free(); }
	public function close(){
		if($this->res) {
			while(@$this->conn->more_results()) $this->conn->next_result();
			@$this->res->close();
			$this->res=null;
		}
	}
	
	public function num_fields(){ return @$this->res->field_count; }
	public function current_field(){ return @$this->res->current_field; }
	public function num_rows(){ return @$this->res->num_rows; }
	public function lengths(){ return @$this->res->lengths; }
	public function error() { return @$this->conn->error; }
	public function errno() { return @$this->conn->errno; }
	public function type(){ return @$this->res->type; }
}
class Conn_mysqli_result_field extends Conn_Main_result_field {
	/*
		name         O nome da coluna
		orgname      Nome original da coluna se foi especificado um alias
		table        O nome da tabela a qual este campo pertence (se não for calculada)
		orgtable     Nome da tabela original se foi especificado um alias
		def          O valor padrão para este campo, representando como uma string
		max_length   O tamanho máximo do campo no conjunto de resultados.
		flags        Um inteiro representando bit-flags para o campo.
		type         O tipo de dados usado para este campo
		decimals     O número de decimais usados (par campos integer)
	*/
	public $dataTypes=array(
	'bit'=>array(
	'bit'       =>array('min'=>1,'max'=>64,'descr'=>'A bit field'),
	),
	'int'=>array(
	'tinyint'   =>array('length'=>4,   'ulength'=>3, 'min'=>0,                   'max'=>255,                'descr'=>'A very small integer'),
	'smallint'  =>array('length'=>6,   'ulength'=>5, 'min'=>-32768,              'max'=>32767,              'descr'=>'A small integer'),
	'mediumint' =>array('length'=>9,   'ulength'=>8,          'descr'=>'A medium-sized integer'),
	'int'       =>array('length'=>11,  'ulength'=>10,'min'=>-2147483648,         'max'=>2147483647,         'descr'=>'A standard integer'),
	'bigint'    =>array('length'=>20,  'ulength'=>20,'min'=>-9223372036854775808,'max'=>9223372036854775807,'descr'=>'A large integer'),
	),
	'dec'=>array(
	'decimal'   =>array('descr'=>'A fixed-point number'),
	'numeric'   =>array('descr'=>'A fixed-point number'),
	),
	'float'=>array(
	'float'     =>array('length'=>23,                'descr'=>'A single-precision floating point number'),
	'real'      =>array('length'=>23,                'descr'=>'A single-precision floating point number'),
	'double'    =>array('length'=>53,                'descr'=>'A double-precision floating point number'),
	),
	'datetime'=>array(
	'date'      =>array('length'=>null,              'descr'=>'A date value in CCYY-MM-DD format'),
	'time'      =>array('length'=>null,              'descr'=>'A time value in hh:mm:ss format'),
	'datetime'  =>array('length'=>null,              'descr'=>'A date and time value inCCYY-MM-DD hh:mm:ssformat'),
	'timestamp' =>array('length'=>null,              'descr'=>'A timestamp value in CCYY-MM-DD hh:mm:ss format'),
	'year'      =>array('length'=>4,                 'descr'=>'A year value in CCYY or YY format'),
	),
	'char'=>array(
	'char'      =>array('length'=>4294967295,        'descr'=>'A fixed-length nonbinary (character) string'),
	),
	'string'=>array(
	'varchar'   =>array('length'=>4294967295,        'descr'=>'A variable-length non-binary string'),
	),
	'text'=>array(
	'tinytext'  =>array('length'=>8000,              'descr'=>'A very small non-binary string (255)'),
	'text'      =>array('length'=>4294967295,        'descr'=>'A small non-binary string (65535)'),
	'mediumtext'=>array('length'=>1099511627776,     'descr'=>'A medium-sized non-binary string (16777215)'),
	'longtext'  =>array('length'=>2199023255552,     'descr'=>'A large non-binary string (4294967295)'),
	),
	'binary'=>array(
	'binary'    =>array('length'=>4294967295,        'descr'=>'A fixed-length binary string'),
	'varbinary' =>array('length'=>4294967295,        'descr'=>'A variable-length binary string'),
	),
	'lob'=>array(
	'tinyblob'  =>array('length'=>8000,              'descr'=>'A very small BLOB (binary large object)'),
	'blob'      =>array('length'=>4294967295,        'descr'=>'A small BLOB'),
	'mediumblob'=>array('length'=>1099511627776,     'descr'=>'A medium-sized BLOB'),
	'longblob'  =>array('length'=>2199023255552,     'descr'=>'A large BLOB'),
	),
	'others'=>array(
	'enum'              =>array('length'=>null,      'descr'=>'An enumeration; each column value may be assigned one enumeration member'),
	'set'               =>array('length'=>null,      'descr'=>'A set; each column value may be assigned zero or more SET members'),
	'geometry'          =>array('length'=>null,      'descr'=>'A spatial value of any type'),
	'point'             =>array('length'=>null,      'descr'=>'A point (a pair of X-Y coordinates)'),
	'linestring'        =>array('length'=>null,      'descr'=>'A curve (one or more POINT values)'),
	'polygon'           =>array('length'=>null,      'descr'=>'A polygon'),
	'geometrycollection'=>array('length'=>null,      'descr'=>'A collection of GEOMETRYvalues'),
	'multilinestring'   =>array('length'=>null,      'descr'=>'A collection of LINESTRINGvalues'),
	'multipoint'        =>array('length'=>null,      'descr'=>'A collection of POINTvalues'),
	'multipolygon'      =>array('length'=>null,      'descr'=>'A collection of POLYGONvalues'),
	),
	);
	public function __construct($index=null,$res=null,$oConn=null){
		parent::__construct($index,$res,$oConn);
		if(!$res) return;
		$aFld=@$res->fetch_fields();
		if(!$aFld || !array_key_exists($index,$aFld)) return;
		$fld=$aFld[$index];
		
		$this->name=$fld->name;
		$this->orgname=@$fld->orgname==''?$fld->name:$fld->orgname;
		$this->table=$fld->table;
		$this->orgtable=@$fld->orgtable==''?$fld->table:$fld->orgtable;
		
		$this->def=$fld->def;
		$this->max_length=$fld->max_length;
		$this->flags=$fld->flags;
		$this->type=$fld->type;
		$this->decimals=$fld->decimals;
		
		$this->length=$this->max_length;
		$this->vartype=$this->trNumType();
		//$this->mysqlExtra=$fld;
	}
	public function trNumType(){
		$type=$this->type;
		if ($type==1) {
			if (!(($this->flags>>12)&1)) return ($this->length==1)?'BOOLEAN':'CHAR';
			} elseif ($type==252) {
			$b=($this->flags>>4)&1?"BLOB":"TEXT";
			if ($this->length==255) return "TINY$b";
			elseif ($this->length==65535) return $b;
			elseif ($this->length==16777215) return "MEDIUM$b";
			elseif ($this->length==-1) return "LONG$b";
			} elseif ($type==253) {
			if (($this->flags>>7)&1) return "VARBINARY";
			} elseif ($type==254) {
			if (($this->flags>>7)&1) return "BINARY";
			elseif (($this->flags>>8)&1) return "ENUM";
			elseif (($this->flags>>11)&1) return "SET";
		}
		return $this->data_type($type);
	}
	public function data_type($type=null){
		static $data_type=array(
		1=>'tinyint',
		2=>'smallint',
		3=>'int',
		4=>'float',
		5=>'double',
		7=>'timestamp',
		8=>'bigint',
		9=>'mediumint',
		10=>'date',
		11=>'time',
		12=>'datetime',
		13=>'year',
		16=>'bit',
		252=>'blob', //is currently mapped to all text and blob types (MySQL 5.0.51a)
		253=>'varchar',
		254=>'char',
		246=>'decimal'
		/*
			DECIMAL           0
			TINY              1
			SHORT             2
			LONG              3
			FLOAT             4
			DOUBLE            5
			NULL              6
			TIMESTAMP         7
			LONGLONG          8
			INT24             9
			DATE             10
			TIME             11
			DATETIME         12
			YEAR             13
			NEWDATE          14
			ENUM            247
			SET             248
			TINY_BLOB       249
			MEDIUM_BLOB     250
			LONG_BLOB       251
			BLOB            252
			VAR_STRING      253
			STRING          254
			GEOMETRY        255
		*/
		);
		if(is_null($type)) return $data_type;
		return array_key_exists($type,$data_type)?$data_type[$type]:"UNKNOWN";
	}
}
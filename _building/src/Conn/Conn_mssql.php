<?php
class Conn_mssql extends Conn_Main {
	public $startTableDelimiter='[';
	public $endTableDelimiter=']';
	public $startFieldDelimiter='[';
	public $endFieldDelimiter=']';
	public $strDelimiter='"';
	
	public function __construct($splitConn=null){
		parent::__construct($splitConn);
		#int mssql_connect ([ string $nomedoservidor [, string $username [, string $password ]]] )
		$this->readOnly['conn']=mssql_connect($this->getHostPort(), @$this->readOnly['user'], @$this->readOnly['pass']);
		//print_r($this->readOnly); 
		$this->checkConnetctionSelectDb();
	}
	public function close(){
		if(!$this->readOnly['conn']) return false;
		@mssql_close($this->readOnly['conn']);
		parent::close();
	}
	public function select_db($db){
		if(!$this->readOnly['conn']) return false;
		parent::select_db($db);
		mssql_select_db($db,$this->conn);
	}
	public function error() {
		if (isset($this->readOnly['res'])) return $this->readOnly['res']?'':mssql_get_last_message();
		return $this->readOnly['conn']?'':mssql_get_last_message();
	}
	public function commit(){
		return mssql_query("COMMIT",$this->readOnly['conn']);
	}
	public function autocommit($bool){
		//return mssql_query("COMMIT",$this->readOnly['conn']);
	}
	public function change_user($user='root',$passwd='',$db=''){
	}
	public function affected_rows(){
		return mssql_rows_affected($this->readOnly['conn']);
	}
	public function insert_id(){
		$res=mssql_query("SELECT @@IDENTITY as last_insert_id", $this->readOnly['conn']);
		return $line=@mssql_fetch_row($res)?$line[0]:0;
	}
	public function get_client_info(){
	}
	public function listSchema(){
		return $this->query("
			SELECT 
				tab.name AS table_name, 
				col.name AS column_name, 
				col.colid AS column_id, 
				typ.name AS data_type,
				col.length AS length, 
				col.prec AS prec,
				col.scale AS scale, 
				com.text AS default_value, 
				obj.name AS default_cons_name
			FROM systypes typ 
			INNER JOIN syscolumns col ON typ.xusertype = col.xusertype 
			INNER JOIN sysobjects tab ON col.id = tab.id 
			LEFT OUTER JOIN syscomments com ON col.cdefault = com.id AND com.colid = 1
			INNER JOIN sysobjects obj ON com.id = obj.id 
			WHERE (tab.xtype = 'U')
			ORDER BY tab.name, col.colid
		");
	}
	public function showDatabases(){
		return $this->query_all('
			SELECT
				s.CATALOG_NAME [SCHEMA],
				s.SCHEMA_NAME [DOMAIN],
				s.SCHEMA_OWNER [OWNER], 
				s.DEFAULT_CHARACTER_SET_NAME,
				NULL DEFAULT_COLLATION_NAME,
				s.DEFAULT_CHARACTER_SET_CATALOG, 
				s.DEFAULT_CHARACTER_SET_SCHEMA,
				NULL SQL_PATH
			FROM INFORMATION_SCHEMA.SCHEMATA s
			ORDER BY s.CATALOG_NAME
		');
	}
	public function showTables($db=null){
		if(!($db=$db?$db:$this->db)) return;
		return $this->query_all('
			SELECT 
				t.TABLE_NAME [TABLE], 
				t.TABLE_CATALOG [DOMAIN], 
				t.TABLE_SCHEMA [SCHEMA], 
				t.TABLE_TYPE [TYPE],

				NULL ENGINE, 
				NULL VERSION, 
				NULL ROW_FORMAT, 
				NULL TABLE_ROWS [ROWS], 
				NULL AVG_ROW_LENGTH, 
				NULL DATA_LENGTH, 
				NULL MAX_DATA_LENGTH, 
				NULL INDEX_LENGTH, 
				NULL DATA_FREE, 
				NULL AUTO_INCREMENT, 
				NULL CREATE_TIME, 
				NULL UPDATE_TIME, 
				NULL CHECK_TIME, 
				NULL TABLE_COLLATION COLLATION, 
				NULL CHECKSUM, 
				NULL CREATE_OPTIONS, 
				NULL TABLE_COMMENT [COMMENT]
			FROM INFORMATION_SCHEMA.TABLES 
			WHERE TABLE_TYPE="BASE TABLE"
			ORDER BY t.TABLE_NAME
		');
	}
	public function showViews($db=null){
		if(!($db=$db?$db:$this->db)) return;
		return $this->query_all('
			SELECT 
				t.TABLE_NAME [TABLE], 
				t.TABLE_CATALOG [DOMAIN], 
				t.TABLE_SCHEMA [SCHEMA], 
				t.TABLE_TYPE [TYPE],

				NULL ENGINE, 
				NULL VERSION, 
				NULL ROW_FORMAT, 
				NULL TABLE_ROWS [ROWS], 
				NULL AVG_ROW_LENGTH, 
				NULL DATA_LENGTH, 
				NULL MAX_DATA_LENGTH, 
				NULL INDEX_LENGTH, 
				NULL DATA_FREE, 
				NULL AUTO_INCREMENT, 
				NULL CREATE_TIME, 
				NULL UPDATE_TIME, 
				NULL CHECK_TIME, 
				NULL TABLE_COLLATION COLLATION, 
				NULL CHECKSUM, 
				NULL CREATE_OPTIONS, 
				NULL TABLE_COMMENT [COMMENT]
			FROM INFORMATION_SCHEMA.TABLES 
			WHERE TABLE_TYPE="BASE TABLE"
			ORDER BY t.TABLE_NAME
		');
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
}
class Conn_mssql_result extends Conn_Main_result {
	public function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		$this->res=@mssql_query($sql, $this->conn);
		$this->verifyError($sql);
	}
	public function close(){
		if($this->res) return @mssql_free_result($this->res);
	}
	public function data_seek($offset){
		return mssql_data_seek($this->res,$offset);
	}
	public function num_fields(){//retorna numero de campos
		return mssql_num_fields($this->res);
	}
	public function fetch_field_direct($fieldnr){
		return $this->fetch_field($fieldnr);
	}
	public function field_seek($fieldnr){
		return mssql_field_seek($this->res,$fieldnr);
	}
	public function num_rows(){
		return mssql_num_rows($this->res);
	}
	public function fetch_array($resulttype=0){
		return mssql_fetch_array($this->res,$resulttype);
	}
	public function fetch_assoc(){
		return mssql_fetch_assoc($this->res);
	}
	public function fetch_object(){
		return mssql_fetch_object($this->res);
	}
	public function fetch_row(){
		return mssql_fetch_row($this->res);
	}
	public function field_count(){
		return $this->num_fields();
	}
	public function current_field(){
	}
	public function lengths(){
		$tam=$this->num_fields();
		$out=array();
		for ($i=0;$i<$tam;$i++) $out[$i]=$this->mssql_field_length($this->res,$i);
		return $out;
	}
	public function error() {
		return $this->res?'':mssql_get_last_message();
	}
	/*
function querys($sQuery, $hDb_conn, $sError, $bDebug) {
   if(!$rQuery = @mssql_query($sQuery, $hDb_conn)) {
       $sMssql_get_last_message = mssql_get_last_message();
       $sQuery_added  = "BEGIN TRY\n";
       $sQuery_added .= "\t".$sQuery."\n";
       $sQuery_added .= "END TRY\n";
       $sQuery_added .= "BEGIN CATCH\n";
       $sQuery_added .= "\tSELECT 'Error: '  + ERROR_MESSAGE()\n";
       $sQuery_added .= "END CATCH";
       $rRun2= @mssql_query($sQuery_added, $hDb_conn);
       $aReturn = @mssql_fetch_assoc($rRun2);
       if(empty($aReturn)){
           echo $sError.'. MSSQL returned: '.$sMssql_get_last_message.'.<br>Executed query: '.nl2br($sQuery);
       }elseif(isset($aReturn['computed'])){
           echo $sError.'. MSSQL returned: '.$aReturn['computed'].'.<br>Executed query: '.nl2br($sQuery);
       }
       return FALSE;
   }else return $rQuery;
}
*/
}
class Conn_mssql_result_field extends Conn_Main_result_field{
	public $dataTypes=array(
		'bit'=>array(
			'bit'       =>array('min'=>1,'max'=>64,'descr'=>'A bit field'),
		),
		'int'=>array(
			'tinyint'  =>array('length'=>4,   'ulength'=>3,   'min'=>0,                   'max'=>255,                ),
			'smallint' =>array('length'=>6,   'ulength'=>5,   'min'=>-32768,              'max'=>32767,              ),
			'int'      =>array('length'=>11,  'ulength'=>10,  'min'=>-2147483648,         'max'=>2147483647,         ),
			'bigint'   =>array('length'=>20,  'ulength'=>20,  'min'=>-9223372036854775808,'max'=>9223372036854775807,),
		),
		'dec'=>array(
			'decimal'  =>array('min'=>-1E38,    'max'=>1E38,    ),
			'numeric'  =>array('min'=>-1E38,    'max'=>1E38,    ),
		),
		'float'=>array(
			'float'    =>array('length'=>23,        'min'=>-1.79E308,'max'=>1.79E308,),
			'real'     =>array('length'=>53,        'min'=>-3.40E38, 'max'=>3.40E38, ),
		),
		'datetime'=>array(
			'date'     =>array('length'=>null,           'descr'=>'Stores date in the format YYYY-MM-DD'),
			'time'     =>array('length'=>null,           'descr'=>'Stores time in the format HH:MI:SS'),
			'datetime' =>array('length'=>null,           'descr'=>'Stores date and time information in the format YYYY-MM-DD HH:MI:SS'),
			'timestamp'=>array('length'=>null,           'descr'=>'Stores number of seconds passed since the Unix epoch (‘1970-01-01 00:00:00’ UTC)'),
			'year'     =>array('length'=>4,              'descr'=>'Stores year in 2 digit or 4 digit format. Range 1901 to 2155 in 4-digit format. Range 70 to 69, representing 1970 to 2069.'),
		),
		'char'=>array(
			'nchar'    =>array('length'=>4000,           'descr'=>'Fixed length with maximum length of 4000 characters'),
			'char'     =>array('length'=>8000,           'descr'=>'Fixed length with maximum length of 8000 characters'),
		),
		'string'=>array(
			'nvarchar' =>array('length'=>4000,           'descr'=>'Variable length storage with maximum length of 4000 characters'),
			'varchar'  =>array('length'=>8000,           'descr'=>'Variable length storage with maximum length of 8000 characters'),
		),
		'text'=>array(
			'ntext'    =>array('length'=>1099511627776,  'descr'=>'Variable length storage with maximum size of 1GB data'),
			'text'     =>array('length'=>2199023255552,  'descr'=>'Variable length storage with maximum size of 2GB data'),
		),
		'binary'=>array(
			'binary'   =>array('length'=>8000,           'descr'=>'Fixed length with maximum length of 8000 bytes'),
			'varbinary'=>array('length'=>8000,           'descr'=>'Variable length storage with maximum length of 8000 bytes'),
			'image'    =>array('length'=>2199023255552,  'descr'=>'Variable length storage with maximum size of 2GB binary data'),
		),
		'lob'=>array(
			'clob'     =>array('length'=>2199023255552,  'descr'=>'Character large objets that can hold up to 2GB'),
			'blob'     =>array('length'=>2199023255552,  'descr'=>'For binary large objects'),
		),
		'others'=>array(
			'xml'      =>array('length'=>2199023255552,  'descr'=>'for storing xml data'),
			'json'     =>array('length'=>2199023255552,  'descr'=>'for storing JSON data'),
		),
	);
	public $startFieldDelimiter='[';
	public $endFieldDelimiter=']';
	public $strDelimiter='"';
	
	public function __construct($index=null,$res=null,$oConn=null){
		parent::__construct($index,$res,$oConn);
		if(!$res || !($fld=mssql_fetch_field($res,$index))) return;

		$this->name=$this->orgname=$fld->name;
		$this->table=$this->orgtable=$fld->column_source;
		$this->max_length=$fld->max_length;
		$this->length=mssql_field_length($res,$index);
		//$this->flags=$oConn->trFlag($f=mysql_field_flags($res,$index));
		//$this->type=$oConn->trType($fld->type,$this->length,$f,$fld->max_length);
		$this->type=$this->realType=$fld->type;
		$this->vartype=$this->type;
		//$this->mysqlExtra=$fld;
	}
}

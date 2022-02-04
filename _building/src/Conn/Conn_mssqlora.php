<?php
class Conn_mssqlora extends Conn_Main {
	public $startTableDelimiter='\'';
	public $endTableDelimiter='\'';
	public $startFieldDelimiter='\'';
	public $endFieldDelimiter='\'';
	
	public function connect($host='localhost',$user='root',$passwd='',$db=''){
		parent::connect($host,$user,$passwd,$db);
		$this->readOnly['conn']=mssql_connect($host,$user,$passwd);
		if (!$this->readOnly['conn'] && $this->error()) $this->fatalError();
		if ($db) $this->select_db($db);
		return $this;
	}
	public function close(){
		@mssql_close($this->readOnly['conn']);
		parent::close();
	}
	public function select_db($db){
		parent::select_db($db);
		//mssql_select_db($db,$this->conn);
	}
	public function escape_string($texto){ ///////////////
		return $texto;
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
	public function query($sql,$verifyError=true){
		return $this->readOnly['res']=new Conn_mssqlora_result($this->conn,$sql,$verifyError,$this->db,$this->dsn);
	}
}
class Conn_mssqlora_result extends Conn_Main_result {
	public function __construct($conn,$sql,$verifyError=true,$db,$dsn=''){
		parent::__construct($conn,$sql,$verifyError,$dsn);
		$this->db=$db;
		$this->res=mssql_query("SELECT * FROM OPENQUERY($db,'".str_replace("'","''",$sql)."')", $this->conn);
		$this->verifyError();
	}
	public function close(){
		return mssql_free_result($this->res);
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
class Conn_mssqlora_result_field extends Conn_mssql_result_field {
	public $startFieldDelimiter='\'';
	public $endFieldDelimiter='\'';
}

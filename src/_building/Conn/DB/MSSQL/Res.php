<?php
namespace DB\MSSQL;

class Res extends \DB\Res {
	function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		$this->res=@mssql_query($sql, $this->conn);
		$this->verifyError($sql);
	}
	function close(){
		if($this->res) return @mssql_free_result($this->res);
	}
	function data_seek($offset){
		return mssql_data_seek($this->res,$offset);
	}
	function fetch_field($fieldnr){
		return new Field($this->res,$fieldnr,$this->conn);
	}
	function fetch_fields(){
		$tam=$this->num_fields();
		$out=array();
		for ($i=0;$i<$tam;$i++) $out[$i]=$this->fetch_field($i);
		return $out;
	}
	function num_fields(){//retorna numero de campos
		return mssql_num_fields($this->res);
	}
	function fetch_field_direct($fieldnr){
		return $this->fetch_field($fieldnr);
	}
	function field_seek($fieldnr){
		return mssql_field_seek($this->res,$fieldnr);
	}
	function num_rows(){
		return mssql_num_rows($this->res);
	}
	function fetch_array($resulttype=0){
		return mssql_fetch_array($this->res,$resulttype);
	}
	function fetch_assoc(){
		return mssql_fetch_assoc($this->res);
	}
	function fetch_object(){
		return mssql_fetch_object($this->res);
	}
	function fetch_row(){
		return mssql_fetch_row($this->res);
	}
	function field_count(){
		return $this->num_fields();
	}
	function current_field(){
	}
	function lengths(){
		$tam=$this->num_fields();
		$out=array();
		for ($i=0;$i<$tam;$i++) $out[$i]=$this->mssql_field_length($this->res,$i);
		return $out;
	}
	function error() {
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

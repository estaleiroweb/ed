<?php
class Conn_details extends OverLoadElements {
	protected $protect=array(
		'version'=>null,
		'sql'=>null,
		'sqlFull'=>'',
		'tables'=>array(),
		'fields'=>array(),
		'result'=>array(),
	);
	protected $readonly=array(
		'conn'=>null,
		'is_updatable'=>true,
		'filter'=>true,
	);
	protected $obj=array();
	protected $__allDatabases=array();
	protected $__allTables=array();
	protected $__allAlias=array();
	protected $__allFields=array();
	protected $tmpDir='/var/tmp/conn_details'; 
	/*Estrutura temporária:
		$tmpDir\
			$dsn\
				databases\
					$db\
						tables\
							$tbl\
								<cfg>
						views\
							$vw\
								<cfg>
				querys\
					$hash\
						<cfg>
		
	*/
	
	function __construct($conn=null,$sql=null){
		$this->readonly['conn']=$conn;
		$this->sql=$sql;
	}
}

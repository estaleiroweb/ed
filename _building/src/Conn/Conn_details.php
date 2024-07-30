<?php
class Conn_details extends OverLoadElements {
	protected $obj=[];
	protected $protect=array(
		'version'=>null,
		'sql'=>null,
		'sqlFull'=>'',
		'tables'=>[],
		'fields'=>[],
		'result'=>[],
	);
	protected $all=[
		'databases'=>[],
		'tables'=>[],
		'alias'=>[],
		'fields'=>[],
		'fieldsKey'=>[],
	];

	protected $reservedWords='';
	protected $contraBarra,$aspasSimples,$aspasDupla,$pontoVirgula;
	protected $tmpDir='/var/tmp/conn_details'; 
	protected $readonly=array(
		'is_updatable'=>true,
		'filter'=>true,
	);
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
	
	public function __construct($conn=null,$sql=null){
		/*
		$this->contraBarra=chr(251).chr(251);
		$this->aspasSimples=chr(252).chr(252);
		$this->aspasDupla=chr(253).chr(253);
		$this->pontoVirgula=chr(254);
		*/
		$this->contraBarra='\x5C';
		$this->aspasSimples='\x27';
		$this->aspasDupla='\x22';
		$this->pontoVirgula='\x3B';

		$this->conn($conn);
		//$this->readonly['conn']=$conn;
		$this->sql=$sql;
	}
	protected function conn($val=null){
		static $conn=null;
		if(is_null($val)) return $conn;
		$conn=$val;
		return $conn;
	}
}

<?php
namespace DB;

abstract class Field {
	/*
	* Nome da coluna
	* 
	* @var string
	*/
	public $name='';
	/*
	* Nome da coluna Alias
	* 
	* @var string
	*/
	public $orgname='';
	/*
	* Nome da tabela onde esta o campo
	* 
	* @var string
	*/
	public $table='';
	/*
	* Nome da tabela onde esta o campo Alias
	* 
	* @var string
	*/
	public $orgtable='';
	/*
	* Defini��es
	* 
	* @var string
	*/
	public $def='';
	/*
	* O limite de tamanho para a coluna 
	* 
	* @var integer
	*/
	public $max_length=0;
	/*
	* Tamanho do campo
	* 
	* @var integer
	*/
	public $length=0;
	/*
	* Charset do campo
	* 
	* @var integer
	*/
	public $charsetnr=0;
	/*
	* Flags do campo (not_null,primary_key,unique_key,multiple_key,numeric,blob,unsigned,zerofill)
	* 
	* @var integer
	*/
	public $flags=0;
	/*
	* C�digo do tipo da coluna
	* 
	* @var integer
	*/
	public $type=0;
	/*
	* Tipo em string Real
	* 
	* @var string
	*/
	public $realType='';
	/*
	* N�mero de decimais
	* 
	* @var integer
	*/
	public $decimals=0;
	/*
	* Tipo em string
	* 
	* @var string
	*/
	public $vartype='';
	/*
		not_null - 1 se a coluna n�o pode ser NULL 
		primary_key - 1 se a coluna � a chave prim�ria 
		unique_key - 1 se a coluna � a chave �nica 
		multiple_key - 1 se a coluna � uma chave n�o �nica 
		numeric - 1 se a coluna � num�rica 
		blob - 1 se a coluna � BLOB 
		unsigned - 1 se a coluna � unsigned(sem sinal) 
		zerofill - 1 se a coluna � preenchida com zero 
	*/
	
	abstract public function __construct($res,$i,$oConn);
	//public function __get($name){ return @$this->$name; }
}

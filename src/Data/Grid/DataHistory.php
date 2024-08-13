<?php
# Autor: Helbert Fernandes
# Descrição: Imprime organizadamente todo o conteudo na tela
#
# Histórico:
# Data: 23/06/2005 08:00 - Helbert Fernandes

class DataHistory {
	static private $instance;
	private $track=array();
	private $loaded=array();

	static public function singleton($obj=false)   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		if ($obj!==false) self::$instance->load($obj);
		return self::$instance;
	}
	function load($obj){
		if (!($id=@$obj->id)) die("Objeto sem Id");
		if (isset($this->loaded[$id])) die("Já exixte um Objeto com o nome '$id'");
		$this->loaded[$id]=$obj;
	}
	function endTrack($obj){
		if ($id=@$obj->idHistory) unset($this->track[$id]);
	}
	function addTrack($obj){
		if ($id=@$obj->id) {
			end($this->track);
			$k=key($this->track)+1;
			$obj->idHistory=$k;
			$this->track[$k]=$id;
		}
	}
	function getPreviousTrack($idHistory) {
		$keys=array_keys($this->track);
		$pos=array_search($idHistory,$keys);
		if (!$pos) return false;
		$id=$this->track[$keys[$pos-1]];
		return $this->loaded[$id];
	}
}

<?php

namespace EstaleiroWeb\ED\Tools;

class Feriados {
	const DIA = 86400;
	public $nacionais = array(
		'01-01' => 'Confraternização Universal',
		'sexPaixao' => 'Sexta feira da Paixão',
		'04-04' => 'Tiradentes',
		'05-01' => 'Dia Mundial do Trabalho',
		'corpusChristi' => 'Corpus Christi',
		'09-07' => 'Independência do Brasil',
		'10-12' => 'Nossa Senhora Aparecida',
		'11-02' => 'Finados',
		'11-15' => 'Proclamação da República',
		'12-25' => 'Natal',
	);
	public $regionais = array();
	public $order = true;
	private $ano, $dtRef, $data;
	private $mascara = '%F';

	function __construct($dtRef = false) {
		$this->setDate($dtRef);
	}
	function setDate($dtRef = false) {
		if (preg_match('/^\d{4}$/', $dtRef)) $dtRef .= '-01-01';
		$this->dtRef = $dtRef === false ? time() : (is_string($dtRef) ? strtotime($dtRef) : $dtRef);
		$this->setMascara($this->mascara);
	}
	function setMascara($m = '%F') {
		$this->mascara = $m;
		$this->data = strftime($this->mascara, $this->dtRef);
		$this->ano = strftime('%Y', $this->dtRef);
	}
	private function getKey($k) {
		return strftime($this->mascara, preg_match('/(\d{1,2}){2}/', $k) ? strtotime("{$this->ano}-$k") : $this->$k());
	}
	function nacionais() {
		$out = array();
		foreach ($this->nacionais as $k => $v) $out[$this->getKey($k)] = $v;
		if ($this->order) ksort($out);
		return $out;
	}
	function regionais() {
		$out = $this->nacionais();
		foreach ($this->regionais as $k => $v) $out[$this->getKey($k)] = $v;
		if ($this->order) ksort($out);
		return $out;
	}
	function isFeriadoNacional() {
		foreach ($this->nacionais as $k => $v) if ($this->data == $this->getKey($k)) return $v;
		return false;
	}
	function isFeriado() {
		if (($out = $this->isFeriadoNacional())) return $out;
		foreach ($this->regionais as $k => $v) if ($this->data == $this->getKey($k)) return $v;
		return false;
	}
	/**
	 * Domingo de Páscoa (Easter); celebração da Ressurreição de Jesus Cristo;
	 **/
	public function pascoa() {
		$a = $this->ano % 19;
		$b = floor($this->ano / 100);
		$c = $this->ano % 100;
		$d = floor($b / 4);
		$e = $b % 4;
		$f = ($b + 8) / 25;
		$g = floor(($b - $f + 1) / 3);
		$h = (19 * $a + $b - $d - $g + 15) % 30;
		$i = floor($c / 4);
		$k = $c % 4;
		$l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
		$m = floor(($a + 11 * $h + 22 * $l) / 451);
		$n = $h + $l - 7 * $m + 114;
		$mes = floor($n / 31);
		$dia = ($n % 31) + 1;
		return strtotime("{$this->ano}-{$mes}-{$dia}");
	}
	public function carnaval() { //Terça-feira de Carnaval ocorre 47 dias antes da Páscoa; celebração pagã
		return self::pascoa() - (47 * self::DIA);
	}
	public function cinzas() { //Quarta-feira de Cinzas (Ash) ocorre 46 dias antes da Páscoa; início do período da Quaresma (Lent) [1], [2];
		return self::pascoa() - (46 * self::DIA);
	}
	public function ramos() { //Domingo de Ramos ocorre sete dias antes da Páscoa, marcando a abertura da Semana Santa; festa litúrgica que celebra a entrada de Jesus Cristo na cidade de Jerusalém;
		return self::pascoa() - (7 * self::DIA);
	}
	public function quiSanta() { //Quinta-feira Santa ocorre três dias antes da Páscoa; marca o fim da Quaresma e início do Tríduo Pascal na Semana Santa; celebração da ultima ceia de Jesus Cristo com os doze apóstolos;
		return self::pascoa() - (3 * self::DIA);
	}
	public function sexPaixao() { //Sexta-feira da Paixão ocorre dois dias antes da Páscoa, na Semana Santa; dia em que os cristãos contemplam a paixão e morte de Jesus Cristo;
		return self::pascoa() - (2 * self::DIA);
	}
	public function domPentecostes() { //Domingo de Pentecostes ocorre no 50º dia desde a Páscoa;
		return self::pascoa() + (50 * self::DIA);
	}
	public function domStimaTrindade() { //Domingo da Santíssima Trindade é o domingo seguinte ao de Pentecostes;
		return self::domPentecostes() + (7 * self::DIA);
	}
	public function corpusChristi() { //Quinta-feira de Corpus Christi (Corpo de Deus) ocorre no 60 dias após a Páscoa; celebração da presença do corpo de Cristo na Eucaristia
		return self::pascoa() + (60 * self::DIA);
	}
}

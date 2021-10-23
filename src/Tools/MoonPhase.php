<?php

namespace EstaleiroWeb\ED\Tools;

/**
 * moon-phase.cls.php
 * June 2003 (updated Jan 2009 by Dan Sheeler to fix major bug where strtotime was called incorrectly)
 * authors:
 * 	dan sheeler <dsheeler@cs.uchicago.edu>
 * 	kumar mcmillan <kumar@chicagomodular.com>
 *
 * description:
 * 	do moon phase calculation stuff
 *   You will see a slight drift in the cycle if you compare the results to other phase calculations...
 *   this is probably because of different degrees of precision among phase periods used, as well as 
 *   float precision from computer to computer.  It is more or less accurate and seems to always report the
 *   correct phase name.  Please let us know if you have any improvements, suggestions
 *   or questions.  Do not make modifications to this class in case the source changes (see moon-phase.php 
 *   for an example of usage). 
 */
class MoonPhase {
	static public $allMoonPhases = array(
		'Lua Nova',
		'Quarto crescente',
		'Quarto Minguante',
		'Lua crescente',
		'Lua Cheia',
		'Lua minguante',
		'Terceiro Quarto crescente',
		'Quarto minguante',
	);
	public $idPhase;
	public $namePhase;
	private $dateAsTimeStamp;
	private $day_in_seconds = 86400; //60 * 60 * 24
	private $periodInDays = 29.53058867; // == complete moon cycle
	private $periodInSeconds = 2551442.861088; // gets set when you ask for it = $this->periodInDays * $this->day_in_seconds;
	private $someFullMoonDate = 1070915820; // set base date, that we know was a full moon: // strtotime("December 8 2003 20:37 UTC"); | December 12 2008 16:37 UTC | August 22 2002 22:29 UTC
	/*
		* CONSTRUCTOR
		* $timestamp (int) date of which to calculate a moon phase and relative phases for
	*/
	function __construct($timeStamp = null) {
		$this->setDate($timeStamp);
	}
	function __toString() {
		return $this->getPhaseName();
	}
	function __invoke() {
		return $this->idPhase;
	}
	/*
		* PRIVATE
		* sets the moon phase ID and moon phase name internally
	*/
	private function calcMoonPhase2() {
		$position = $this->getPositionInCycle();
		if ($position >= 0.474 && $position <= 0.53)        $this->idPhase = 0;
		elseif ($position >= 0.53 && $position <= 0.724)   $this->idPhase = 1;
		elseif ($position >= 0.724 && $position <= 0.776)  $this->idPhase = 2;
		elseif ($position >= 0.776 && $position <= 0.974)  $this->idPhase = 3;
		elseif ($position >= 0.974 || $position <= 0.026)  $this->idPhase = 4;
		elseif ($position >= 0.026 && $position <= 0.234)  $this->idPhase = 5;
		elseif ($position >= 0.234 && $position <= 0.295)  $this->idPhase = 6;
		elseif ($position >= 0.295 && $position <= 0.4739) $this->idPhase = 7;
		else die('ERROR: ' . __FUNCTION__);
		$this->namePhase = self::$allMoonPhases[$this->idPhase];
	}
	private function calcMoonPhase() {
		$position = $this->getPositionInCycle();
		if ($position <= 0.026)      $this->idPhase = 4;
		elseif ($position <= 0.234)  $this->idPhase = 5;
		elseif ($position <= 0.295)  $this->idPhase = 6;
		elseif ($position <= 0.4739) $this->idPhase = 7;
		elseif ($position <= 0.53)   $this->idPhase = 0;
		elseif ($position <= 0.724)  $this->idPhase = 1;
		elseif ($position <= 0.776)  $this->idPhase = 2;
		elseif ($position <= 0.974)  $this->idPhase = 3;
		else return 4;
		$this->namePhase = self::$allMoonPhases[$this->idPhase];
	}
	/*
		* PUBLIC
		* return (float) number between 0 and 1.  0 or 1 is the beginning of a cycle (full moon) 
		*		and 0.5 is the middle of a cycle (new moon).
	*/
	private function getPositionInCycle() {
		$diff = $this->dateAsTimeStamp - $this->someFullMoonDate;
		$position = ($diff % $this->periodInSeconds) / $this->periodInSeconds;
		return $position < 0 ? $position + 1 : $position;
	}
	/*
		* PUBLIC
		* sets the internal date for calculation and calulates the moon phase for that date.
		* called from the constructor.
		* $timeStamp (int) date to set as unix timestamp
	*/
	public function setDate($timeStamp = null) {
		if ($timeStamp + 0 <= 0) $timeStamp = time();
		$this->dateAsTimeStamp = $timeStamp;
		print "===> {$this->dateAsTimeStamp}\n";
		$this->calcMoonPhase();
		return $this->idPhase;
	}
	/*
		* PUBLIC
	*/
	public function getDaysUntilNextFullMoon() {
		$position = $this->getPositionInCycle();
		return round((1 - $position) * $this->periodInDays, 2);
	}
	/*
		* PUBLIC
	*/
	public function getDaysUntilNextLastQuarterMoon() {
		$days = 0;
		$position = $this->getPositionInCycle();
		if ($position < 0.25) $days = (0.25 - $position) * $this->periodInDays;
		elseif ($position >= 0.25) $days = (1.25 - $position) * $this->periodInDays;
		return round($days, 1);
	}
	/*
		* PUBLIC
	*/
	public function getDaysUntilNextFirstQuarterMoon() {
		$days = 0;
		$position = $this->getPositionInCycle();
		if ($position < 0.75) $days = (0.75 - $position) * $this->periodInDays;
		elseif ($position >= 0.75) $days = (1.75 - $position) * $this->periodInDays;
		return round($days, 1);
	}
	/*
		* PUBLIC
	*/
	public function getDaysUntilNextNewMoon() {
		$days = 0;
		$position = $this->getPositionInCycle();
		if ($position < 0.5) $days = (0.5 - $position) * $this->periodInDays;
		elseif ($position >= 0.5) $days = (1.5 - $position) * $this->periodInDays;
		return round($days, 1);
	}
	/*
		* PUBLIC
		* returns the percentage of how much lunar face is visible
	*/
	public function getPercentOfIllumination() {
		// from http://www.lunaroutreach.org/cgi-src/qpom/qpom.c
		// C version: // return (1.0 - cos((2.0 * M_PI * phase) / (LPERIOD/ 86400.0))) / 2.0;
		$percentage = (1.0 + cos(2.0 * M_PI * $this->getPositionInCycle())) / 2.0;
		$percentage *= 100;
		$percentage = round($percentage, 1) . '%';
		return $percentage;
	}
	/*
		* PUBLIC
		* $ID (int) ID of phase, default is to get the phase for the current date passed in constructor
	*/
	public function getPhaseName($id = -1) {
		if ($id < 0) return $this->namePhase; // get name for this current date
		return self::$allMoonPhases[$id]; // or.. get name for a specific ID
	}
	/*
		* PUBLIC
		* $newStartingDateAsTimeStamp (int) set a new date to start the week at, or use the current date
		* return (array[6]) weekday timestamp => phase for weekday
	*/
	public function getUpcomingWeekArray($newStartingDateAsTimeStamp = -1) {
		$newStartingDateAsTimeStamp = ($newStartingDateAsTimeStamp > -1) ? $newStartingDateAsTimeStamp : $this->dateAsTimeStamp;
		$moonPhaseObj = get_class($this);
		$weeklyPhase = new $moonPhaseObj($newStartingDateAsTimeStamp);
		$upcomingWeekArray = array();
		for ($day = 0, $thisTimeStamp = $weeklyPhase->dateAsTimeStamp; $day < 7; $day++, $thisTimeStamp += $this->day_in_seconds) {
			$weeklyPhase->setDate($thisTimeStamp);
			$upcomingWeekArray[$thisTimeStamp] = $weeklyPhase->idPhase;
		}
		unset($weeklyPhase);
		return $upcomingWeekArray;
	}
}

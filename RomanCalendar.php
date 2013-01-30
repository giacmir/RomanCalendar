<?php
/**
 * A PHP class to get informations about the Liturgical Roman Calendar
 * @author Giacomo Mirabassi <giacomo@mirabassi.it>
 * @license GNU/GPL version 3 or later
 *
 */
class RomanCalendar {

	/* class constants */
	const TIME_ORDINARY = 1;
	const TIME_ADVENT = 2;
	const TIME_CHRISTMAS = 3;
	const TIME_LENT = 4;
	const TIME_EASTER = 5;

	const EASTER_MIN = 322;
	const EASTER_MAX = 425;

	const EASTER_DURATION = 40;
	const LENT_DURATION = 45;

	const SOLEMNITY = 200;

	/**
	 * Get the liturgical time for a given date
	 * @param mixed $date a date to check, it can be a DateTime object or any format accepted from DateTime::__construct(), if omitted current date is assumed
	 * @throws Exception
	 * @return integer Returns one of the TIME_* constants
	 */
	public static function getYearTime($date=null){

		try {
			$date = static::_getDateTimeObject($date);
			$year = (int) $date->format('Y');


			$easter = static::getEasterDate((int) $date->format('Y'));
			$easterInterval = $easter->diff($date);

			if($easterInterval->days == 0 || ($easterInterval->days<=static::EASTER_DURATION && $easterInterval->invert == 0)){
				return static::TIME_EASTER;
			}
			else if($easterInterval->days <= static::LENT_DURATION && $easterInterval->invert === 1) {
				return static::TIME_LENT;
			}

			if($date->format('m')<3){
				$oldYear = $year-1;

				$christmas = static::_getDateTimeObject("$oldYear-12-25");
				$christmasLength = static::getChristmasTimeLength($year);
				$christmasInterval = $christmas->diff($date);

				if($christmasInterval->days <= $christmasLength && $christmasInterval->invert == 0){
					return static::TIME_CHRISTMAS;
				}
			}
			else if($date->format('m')>9){
				$advent = static::getAdventLength($year);
				$christmas = static::_getDateTimeObject("$year-12-25");
				$adventInterval = $christmas->diff($date);

				if($adventInterval->days === 0){
					return static::TIME_CHRISTMAS;
				}

				if($adventInterval->days <= $advent && $adventInterval->invert = 1){
					return static::TIME_ADVENT;
				}
			}



			return static::TIME_ORDINARY;


		} catch (Exception $e) {
			throw $e;
		}


	}

	/**
	 * Returns a DateTime object for the easter day of the given year;
	 * @param integer $year the year to consider, if omitted current year assumed
	 * @throws Exception
	 * @return DateTime
	 */
	public static function getEasterDate($year=null){
		try {
			$year = static::_getYear($year);
			$easter = static::_getDateTimeObject("@".easter_date($year));

			return $easter;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * check the year params and returns it if valid or the current year if null
	 * @param integer $year
	 * @throws Exception
	 * @return number
	 */
	protected static function _getYear($year=null){
		if(!$year){
			$cur = static::_getDateTimeObject();
			$year = (int) $cur->format('Y');
		}
		else {
			if(is_numeric($year)){
				$year = (int) $year;
			}
			else {
				throw new Exception("Invalid year notation");
			}
		}

		return $year;
	}

	/**
	 * checks the given $date parameters and returns the relative DateTime object
	 * @param mixed $date can be a DateTime object or any format accepted by DateTime::__construct()
	 * @throws Exception
	 * @return DateTime
	 */
	protected static function _getDateTimeObject($date=null){
		$timezone = new DateTimeZone('UTC');
		if(!$date){
			return new DateTime("now",$timezone);
		}
		else if(is_a($date, "DateTime")){
			$date->setTimezone($timezone);
			return $date;
		}
		else {
			try {
				$obj = new DateTime($date,$timezone);
				return $obj;
			} catch (Exception $e) {
				throw $e;
			}
		}
	}

	/**
	 * Returns the day of Ash Wednesday for the requeste year
	 * @param integer $year the year to consider, if omitted current year assumed
	 * @throws Exception
	 * @return DateTime
	 */
	public static function getAshWednesday($year=null){

		try {
			$year = static::_getYear($year);
			$easter = static::getEasterDate($year);

			$interval = new DateInterval("P".static::LENT_DURATION."D");

			$ash = $easter->sub($interval);

			return $ash;


		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * Returns an array of the fixed holidays (currently only solemnities are considered)
	 * the first key is monthday (es 1225 for christmas)
	 * each key is associated to an array that contains a string for the name of the holiday and an integer for the type
	 * @return array
	 */
	public static function getFixedHolidays(){
		$fixed = array(
				101 => array('Mary, Mother of God',static::SOLEMNITY),
				106 => array('Epiphany',static::SOLEMNITY),
				319 => array('Saint Joseph',static::SOLEMNITY),
				325 => array('Annunciation of the Lord',static::SOLEMNITY),
				624 => array('Birth of Saint John the Baptist',static::SOLEMNITY),
				629 => array('Saints Peter and Paul',static::SOLEMNITY),
				815 => array('Assumption of the Blessed Virgin Mary',static::SOLEMNITY),
				1001 => array('All Saints',static::SOLEMNITY),
				1208 => array('Immaculate Conception of the Blessed Virgin Mary',static::SOLEMNITY),
				1225 => array('Nativity of the Lord',static::SOLEMNITY)
		);

		return $fixed;
	}

	/**
	 * Returns an array of DateTime objects, one for each advent sunday
	 * @param integer $year the year to consider, if omitted current year assumed
	 * @throws Exception
	 * @return array:
	 */
	public static function getAdventSundays($year=null){

		try {
			$year = static::_getYear($year);
			$christmasEve = static::_getDateTimeObject("$year-12-24");
			$sundays = array();

			if($christmasEve->format('w') == 0){
				$sundays[0] = $christmasEve;
			}
			else {
				$interval = new DateInterval("P".$christmasEve->format('w')."D");
				$sundays[0] = $christmasEve->sub($interval);
			}

			for($i=1;$i<4;$i++){
				$interval = new DateInterval("P7D");
				$sundays[$i] = $sundays[$i-1]->sub($interval);
			}

			return array_reverse($sundays);



		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * returns the day of the first advent sunday (the first day of advent and the first day of the liturgical year)
	 * @param integer $year the year to consider, if omitted current year assumed
	 * @return DateTime
	 */
	public static function getAdventStart($year=null){
		$sundays = static::getAdventSundays($year);

		return $sundays[0];
	}

	/**
	 * alias for getAdventStart()
	 * @param integer $year
	 * @return DateTime
	 */
	public static function getYearStart($year=null){
		return static::getAdventStart($year);
	}

	/**
	 * Returns how much days is the advent long
	 * @param integer $year
	 * @throws Exception
	 */
	public static function getAdventLength($year=null){
		try {
			$year = static::_getYear($year);
			$christmas = static::_getDateTimeObject("$year-12-15");
			$firstSunday = static::getAdventStart($year);

			$interval = $firstSunday->diff($christmas);

			return $interval->days;
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	 * returns the last day of Christmas time
	 * @param integer $year
	 * @throws Exception
	 * @return DateTime
	 */
	public static function getChristmasTimeEnd($year=null){
		try {
			$year = static::_getYear($year);

			$afterEpiphany = static::_getDateTimeObject("$year-01-07");

			if($afterEpiphany->format('w') == 0){
				return $afterEpiphany;
			}
			else {
				$toSunday = 7-$afterEpiphany->format('w');
				$interval = new DateInterval("P".$toSunday."D");
				$afterEpiphany->add($interval);

				return $afterEpiphany;
			}

		} catch (Exception $e) {
			throw $e;
		}

	}

	/**
	 * Number of days the christmas time is long
	 * @param integer $year
	 * @throws Exception
	 * @return integer
	 */
	public static function getChristmasTimeLength($year=null){
		try {
			$year = static::_getYear($year);

			$startYear = $year-1;
			$start = static::_getDateTimeObject($startYear."-12-25");
			$end = static::getChristmasTimeEnd($year);
			$interval = $start->diff($end);

			return $interval->days;

		} catch (Exception $e) {
			throw $e;
		}
	}

}
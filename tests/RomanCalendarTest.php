<?php
require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../RomanCalendar.php");

class RomanCalendarTest extends \PHPUnit\Framework\TestCase {

	protected $_timezone;

	public function setUp() : void
    {
		$this->_timezone = new DateTimeZone('UTC');
	}

	public function testGetYearTime(){
		$date = '2012-12-20';
		$this->assertEquals(RomanCalendar::TIME_ADVENT, RomanCalendar::getYearTime($date),'failed advent test');

		$date = '2013-01-03';
		$this->assertEquals(RomanCalendar::TIME_CHRISTMAS, RomanCalendar::getYearTime($date),'failed christmas test');

		$date = '2012-12-25';
		$this->assertEquals(RomanCalendar::TIME_CHRISTMAS, RomanCalendar::getYearTime($date),'failed christmas day old year test');

		$date = '2012-12-30';
		$this->assertEquals(RomanCalendar::TIME_CHRISTMAS, RomanCalendar::getYearTime($date),'failed christmas day new year test');


		$date = '2013-01-13';
		$this->assertEquals(RomanCalendar::TIME_CHRISTMAS, RomanCalendar::getYearTime($date),'failed last day of christmas test');

		$date = '2013-03-1';
		$this->assertEquals(RomanCalendar::TIME_LENT, RomanCalendar::getYearTime($date),'failed lent test');

		$date = '2013-03-31';
		$this->assertEquals(RomanCalendar::TIME_EASTER, RomanCalendar::getYearTime($date),'failed easter day test');

		$date = '2013-04-1';
		$this->assertEquals(RomanCalendar::TIME_EASTER, RomanCalendar::getYearTime($date),'failed easter test');

		$date = '2013-07-19';
		$this->assertEquals(RomanCalendar::TIME_ORDINARY, RomanCalendar::getYearTime($date),'failed ordinary test');

		$date = '2013-01-14';
		$this->assertEquals(RomanCalendar::TIME_ORDINARY, RomanCalendar::getYearTime($date),'failed first ordinary after christmas  test');

		$date = '2013-02-13';
		$this->assertEquals(RomanCalendar::TIME_LENT, RomanCalendar::getYearTime($date),'failed ashes day test');
	}

	public function testGetAshWednesday(){
		$this->assertEquals(3, RomanCalendar::getAshWednesday()->format('w'),'is not a wednesday');

		$date = new DateTime('2013-02-13',$this->_timezone);
		$wed = RomanCalendar::getAshWednesday(2013);


		$interval = $date->diff($wed);
		$this->assertEquals(0, $interval->days,'wrong day');

	}

	public function testGetAdventSundays(){
		$sundays = array(
			new DateTime('2012-12-02',$this->_timezone),
			new DateTime('2012-12-09',$this->_timezone),
			new DateTime('2012-12-16',$this->_timezone),
			new DateTime('2012-12-23',$this->_timezone)
		);

		$tested = RomanCalendar::getAdventSundays(2012);

		$this->assertTrue(count(RomanCalendar::getAdventSundays()) == 4);

		foreach($sundays as $key=>$sun){
			$this->assertEquals($sun->getTimestamp(), $tested[$key]->getTimestamp());
		}


	}

	public function testGetAdventStart(){
		$start = new DateTime('2012-12-02',$this->_timezone);

		$this->assertEquals($start->getTimestamp(), RomanCalendar::getAdventStart(2012)->getTimestamp(),'wrong day');

		$this->assertEquals(0, RomanCalendar::getAdventStart()->format('w'),'not a sunday');
	}

	public function testGetChristmasTimeEnd(){
		$this->assertEquals(0, RomanCalendar::getChristmasTimeEnd()->format('w'),'not a sunday');
	}

	public function testgetAscention(){
		$this->assertEquals(0, RomanCalendar::getAscention()->format('w'),'not a sunday');

		$ascention = new DateTime('2013-05-12',$this->_timezone);

		$this->assertEquals($ascention->format('d-m-y'), RomanCalendar::getAscention(2013)->format('d-m-y'),'wrong day');
	}

	public function testGetPentecost(){
		$this->assertEquals(0, RomanCalendar::getPentecost()->format('w'),'not a sunday');

		$pentecost = new DateTime('2013-05-19',$this->_timezone);

		$this->assertEquals($pentecost->format('d-m-y'), RomanCalendar::getPentecost(2013)->format('d-m-y'),'wrong day');
	}

	public function testGetOrdinaryWeek(){
		$date = new DateTime('2013-01-30',$this->_timezone);

		$this->assertTrue(is_int(RomanCalendar::getOrdinaryWeek($date)),'not an integer');
		$this->assertEquals(3, RomanCalendar::getOrdinaryWeek($date),'wrong week count');

		$date = new DateTime('2013-02-20',$this->_timezone);
		$this->assertFalse(RomanCalendar::getOrdinaryWeek($date));

		$date = new DateTime('2013-12-25',$this->_timezone);
		$this->assertFalse(RomanCalendar::getOrdinaryWeek($date));

		$date = new DateTime('2013-02-11',$this->_timezone);
		$this->assertEquals(5, RomanCalendar::getOrdinaryWeek($date),'wrong week count');

		$date = new DateTime('2013-05-21',$this->_timezone);
		$this->assertEquals(7, RomanCalendar::getOrdinaryWeek($date),'wrong week count');
	}
}

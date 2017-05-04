<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzTimeTest extends SzTestAbstract
{
    /**
     * @see SzParam::getMicroTime
     * @see SzParam::getTime
     * @see SzParam::getTimestamp
     * @see SzParam::setTime
     * @see SzParam::getDate
     * @see SzParam::getNextDayTimestamp
     * @see SzParam::getYmDate
     * @see SzParam::getYmdDate
     * @see SzParam::getHour
     * @see SzParam::getMinutes
     * @see SzParam::getSeconds
     */
    public function test_Time()
    {
        $timestamp  = 1388505600;
        $timeString = '2014-01-01 00:00:00';
        $this->assertEquals(time(), SzTime::getTime());
        $this->assertEquals($timestamp, SzTime::getTimestamp($timeString));
        $this->assertEquals($timeString, SzTime::setTime($timestamp));
        $this->assertEquals('2014-01-01', SzTime::getDate($timestamp));
        $this->assertEquals('1388592000', SzTime::getNextDayTimestamp($timestamp));
        $this->assertEquals('201401', SzTime::getYmDate($timestamp));
        $this->assertEquals('20140101', SzTime::getYmdDate($timestamp));
        $this->assertEquals('00', SzTime::getHour($timestamp));
        $this->assertEquals('00', SzTime::getMinutes($timestamp));
        $this->assertEquals('00', SzTime::getSeconds($timestamp));
        $this->assertEquals(SzTime::EMPTY_TIME, SzTime::convertTime(null));
    }
}
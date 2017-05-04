<?php
class SzTime
{

    const EMPTY_TIME = '0000-00-00 00:00:00'; // default value in DB
    const TIMESTAMP_INIT_TIME = '1970-01-01 00:00:00';

    // time constants, all in seconds
    const MINUTE  = 60;
    const HOURS4  = 14400;
    const HOURS6  = 21600;
    const HOURS8  = 28800;
    const HOURS12 = 43200;
    const HOURS24 = 86400;
    const DAY2    = 172800;
    const DAY3    = 259200;
    const DAY7    = 604800;

    /**
     * Get system timestamp, seconds.microseconds
     *
     * @return float $time
     */
    public static function getMicroTime()
    {
        return microtime(true) * 1000;
    }

    /**
     * Is Summer Time
     *
     * @return int
     */
    public static function isDst()
    {
        return date('I');
    }

    /**
     * Get linux system timestamp.
     *
     * @return int $time
     */
    public static function getTime()
    {
        return time();
    }

    /**
     * Get linux system timestamp by $timeString.
     *
     * @param string $timeString
     * @return int
     */
    public static function getTimestamp($timeString)
    {
        if ($timeString == null || $timeString == self::EMPTY_TIME) {
            return false;
        } else {
            return strtotime($timeString);
        }
    }

    /**
     * Convert timestamp to string 'YYYY-MM-DD HH:MM:SS'.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function setTime($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Convert timestamp to string 'YYYY-MM-DD'.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function getDate($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('Y-m-d', $timestamp);
    }

    /**
     * Get the timestamp of '00:00:00' of the next natural day.
     *
     * @param int $timestamp default null, means now
     * @return int
     */
    public static function getNextDayTimestamp($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = SzTime::getTime();
        }

        return SzTime::getTimestamp(SzTime::getDate($timestamp + 60 * 60 * 24));
    }

    /**
     * Convert timestamp to string 'YYYYMM'.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function getYmDate($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('Ym', $timestamp);
    }

    /**
     * Convert timestamp to string 'YYYYMMDD'.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function getYmdDate($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('Ymd', $timestamp);
    }

    /**
     * Convert timestamp to string hour.
     *
     * <pre>
     * Two format: 0-24 | 00-24
     * </pre>
     *
     * @param int $timestamp default null, means now
     * @param boolean $withInitZero default false, means int (0-23) returned, otherwise (00-23) returned
     * @return string $timeString
     */
    public static function getHour($timestamp = null, $withInitZero = false)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        $timeString = $withInitZero ? date('H', $timestamp) : date('G', $timestamp);
        return $timeString;
    }

    /**
     * Convert timestamp to string minutes with initialize 0.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function getMinutes($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('i', $timestamp);
    }

    /**
     * Convert timestamp to string seconds with initialize 0.
     *
     * @param int $timestamp default null, means now
     * @return string $timeString
     */
    public static function getSeconds($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getTime();
        }
        return date('s', $timestamp);
    }

    /**
     * Convert time string to NULL value('0000-00-00 00:00:00' => NULL), <br/>
     * or convert NULL value to time string(NULL => '0000-00-00 00:00:00').
     *
     * @param string $input
     * @return string $return
     */
    public static function convertTime($input)
    {
        $return = $input;
        if ($input == self::EMPTY_TIME) {
            $return = null;
        } else {
            if ($input === null) {
                $return = self::EMPTY_TIME;
            }
        }

        return $return;
    }

}
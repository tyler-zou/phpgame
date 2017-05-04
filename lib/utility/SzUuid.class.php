<?php
class SzUuid
{

    /**
     * @brief Generates a Universally Unique IDentifier, version 4.
     *
     * This function generates a truly random UUID. The built in CakePHP String::uuid() function
     * is not cryptographically secure. You should uses this function instead.
     *
     * @see http://tools.ietf.org/html/rfc4122#section-4.4
     * @see http://en.wikipedia.org/wiki/UUID
     *
     * @return string A UUID, made up of 32 hex digits and 4 hyphens.
     */
    public static function genUuid()
    {
        $prBits = null;

        $prBits = "";
        for ($cnt = 0; $cnt < 16; $cnt++) {
            $prBits .= chr(mt_rand(0, 255));
        }

        $timeLow = bin2hex(substr($prBits, 0, 4));
        $timeMid = bin2hex(substr($prBits, 4, 2));
        $timeHiAndVersion = bin2hex(substr($prBits, 6, 2));
        $clockSeqHiAndReserved = bin2hex(substr($prBits, 8, 2));
        $node = bin2hex(substr($prBits, 10, 6));

        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * timeHiAndVersion field to the 4-bit version number from
         * Section 4.1.3.
         *
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $timeHiAndVersion = hexdec($timeHiAndVersion);
        $timeHiAndVersion = $timeHiAndVersion >> 4;
        $timeHiAndVersion = $timeHiAndVersion | 0x4000;

        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clockSeqHiAndReserved to zero and one, respectively.
         */
        $clockSeqHiAndReserved = hexdec($clockSeqHiAndReserved);
        $clockSeqHiAndReserved = $clockSeqHiAndReserved >> 2;
        $clockSeqHiAndReserved = $clockSeqHiAndReserved | 0x8000;

        return sprintf(
            '%08s-%04s-%04x-%04x-%012s',
            $timeLow,
            $timeMid,
            $timeHiAndVersion,
            $clockSeqHiAndReserved,
            $node
        );
    }

    /**
     * Validate input is uuid or not.
     *
     * @param string $uuid
     * @return boolean
     */
    public static function isUuid($uuid)
    {
        //$regex = '/^[\x21\x23-\x26\x2a-\x3a\x3d\x3f-\x5a\x5c\x5e\x5f\x61-\x7a\x7c\x7e]+@(([iqxz]+\.)|([a-zA-Z0-9][-a-zA-Z0-9]*[a-zA-Z0-9]\.)|([a-zA-Z0-9]\.[a-zA-Z0-9][-a-zA-Z0-9]*[a-zA-Z0-9]\.))+[a-zA-Z]{2,6}$/';
        $regex = '/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/';
        if (preg_match($regex, $uuid)) {
            return true;
        } else {
            return false;
        }
    }
}
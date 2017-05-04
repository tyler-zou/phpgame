<?php
class SzUtility
{

    /**
     * Get client browser type.
     *
     * @return string $browser
     */
    public static function getClientBrowser()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = '';
        $browserVer = '';

        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'OmniWeb';
            $browserVer = $regs[2];
        } else if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Netscape';
            $browserVer = $regs[2];
        } else if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Safari';
            $browserVer = $regs[1];
        } else if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = 'Internet Explorer';
            $browserVer = $regs[1];
        } else if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser = 'Opera';
            $browserVer = $regs[1];
        } else if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser = '(Internet Explorer ' . $browserVer . ') NetCaptor';
            $browserVer = $regs[1];
        } else if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser = '(Internet Explorer ' . $browserVer . ') Maxthon';
            $browserVer = '';
        } else if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'FireFox';
            $browserVer = $regs[1];
        } else if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser = 'Lynx';
            $browserVer = $regs[1];
        }

        if ($browser != '') {
            return "{$browser} {$browserVer}";
        } else {
            return 'Unknown browser';
        }
    }

    /**
     * Get server host address.
     *
     * @return string $address
     */
    public static function getServerHost()
    {
        return empty($_SERVER["SERVER_ADDR"]) ? 'No Server IP' : $_SERVER["SERVER_ADDR"];
    }

    /**
     * Get client ip address.
     *
     * @return string address
     */
    public static function getClientHost()
    {
        $ip = null;

        if (self::checkArrayKey('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $forwardedIps = self::explodeWithTrim(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (is_array($forwardedIps) && $forwardedIps) {
                $ip = array_shift($forwardedIps);
            }
        }
        if (is_null($ip) && self::checkArrayKey('REMOTE_ADDR', $_SERVER) && $_SERVER['REMOTE_ADDR']) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        return $ip;
    }

    /**
     * Check whether array key exist or not.
     *
     * <pre>
     * $restrictMode "WILL" check exactly whether the key exists in array or not.
     * IF "NOT" $strictMode, function will return false when key exists but value is "NULL".
     * </pre>
     *
     * @param array|mixed $keys array param given, means all the elements in the array will be checked
     * @param array $array
     * @param boolean $strictMode default false
     * @return boolean
     */
    public static function checkArrayKey($keys, $array, $strictMode = false)
    {
        $result = true;

        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (!self::checkArrayKey($key, $array, $strictMode)) {
                    $result = false;
                    break;
                }
            }
        } else {
            if ($strictMode) {
                $result = array_key_exists($keys, $array);
            } else {
                $result = isset($array[$keys]);
            }
        }

        return $result;
    }

    /**
     * Calculate the hit NO. of the consistent hash result of the given $shardKey and $totalCount. <br/>
     *
     * <pre>
     * e.g
     *     MYSQL servers: array( 0 => 'configOfHost1', 1 => 'configOfHost2')
     *     USER ID: 101
     *     HIT NO: crc32(101) % 2
     * </pre>
     *
     * @param string $shardKey
     * @param int $totalCount
     * @return int
     */
    public static function consistentHash($shardKey, $totalCount)
    {
        file_put_contents('/tmp/aa.txt', $shardKey.PHP_EOL, FILE_APPEND);

        return sprintf("%u", crc32($shardKey)) % $totalCount;
    }

    /**
     * Explode string to array with string firstly trimmed.
     *
     * @param string $delimiter
     * @param string $string
     * @return array
     */
    public static function explodeWithTrim($delimiter, $string)
    {
        $result = explode($delimiter, trim($string, $delimiter));
        if (is_null($result[0]) || '' == $result[0]) {
            // for case $string is NULL or '', return will be an array(''), not make sense
            // so unset the value at position 0, make this array empty => array()
            unset($result[0]);
        }
        return $result;
    }

    /**
     * Filter special character & blank in input value.
     *
     * @param string $input
     * @return string $output
     */
    public static function escape($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = self::escape($value);
            }
        } else {
            $input = trim(strip_tags($input));
        }
        return $input;
    }

    /**
     * Convert string 'true' to TRUE, string 'false' to FALSE.
     *
     * @param string $input
     * @return boolean | mixed
     */
    public static function convertStringToBoolean($input)
    {
        if (strtolower($input) == 'true') {
            $input = true;
        } else if (strtolower($input) == 'false') {
            $input = false;
        }
        return $input;
    }

    /**
     * Build http request query string.
     *
     * @param array $params
     * @param boolean $needEncode default true
     * @return string $queryString
     */
    public static function buildQueryString($params, $needEncode = true)
    {
        $queryString = '';

        if ($params && is_array($params)) {
            foreach ($params as $key => $val) {
                $params[] = $key . '=' . ($needEncode ? rawurlencode($val) : $val);
                unset($params[$key]);
            }
            $queryString = implode('&', $params);
        }

        return $queryString;
    }

    /**
     * Parse url into parts.
     *
     * @see parse_url
     *
     * @param string $url
     * @return array $parsed 'scheme, host, port, path, query'
     */
    public static function parseUrl($url)
    {
        $parsed = parse_url($url);
        if (!isset($parsed['port'])) {
            $parsed['port'] = '80';
        }

        return $parsed;
    }

    /**
     * Post a http request.
     *
     * @param string $url
     * @param array $params 'key => value', if string given means this query data already formatted
     * @param boolean $needEncode default true
     * @param int $timeout default 10 => 10s
     * @throws SzException 10200
     * @return string $result
     */
    public static function postHttpRequest($url, $params, $needEncode = true, $timeout = 10)
    {
        $result = null;

        $queryString = is_array($params) ? self::buildQueryString($params, $needEncode) : $params;

        // post the query and get result
        if (function_exists('curl_init')) {
            // Use CURL if installed...
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // default connect timeout 2s
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            if (false === $result) {
                curl_close($ch);
                throw new SzException(10200, array(curl_errno($ch), curl_error($ch)));
            }
            curl_close($ch);
        } else {
            // Non-CURL based version...
            $context = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded' . "\r\n" .
                    'User-Agent: PHP5 Framework ' . "\r\n" .
                    'Content-length: ' . strlen($queryString),
                    'content' => $queryString
                ),
            );
            $contextId = stream_context_create($context);
            $sock = fopen($url, 'r', false, $contextId);
            if ($sock) {
                $result = '';
                while (!feof($sock)) {
                    $result .= fgets($sock, 4096);
                }
                fclose($sock);
            }
        }

        return $result;
    }

    /**
     * Post an async http request.
     *
     * @param string $url
     * @param array $params 'key => value', if string given means this query data already formatted
     * @param boolean $needEncode default true
     * @param int $timeout default 10 => 10s
     * @throws SzException 10201
     * @return void
     */
    public static function postAsyncHttpRequest($url, $params, $needEncode = true, $timeout = 10)
    {
        $queryString = is_array($params) ? self::buildQueryString($params, $needEncode) : $params;
        $parsed = self::parseUrl($url);

        $sock = fsockopen($parsed['host'], $parsed['port'], $errno, $errstr, $timeout);
        if (!$sock) {
            throw new SzException(10201, array($errno, $errstr));
        }

        $output = "POST {$parsed['path']} HTTP/1.1\r\n";
        $output .= "Host: {$parsed['host']}\r\n";
        $output .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $output .= "Content-Length: " . strlen($queryString) . "\r\n";
        $output .= "Connection: Close\r\n\r\n";
        if ($queryString) {
            $output .= $queryString;
        }

        fwrite($sock, $output);
        fclose($sock);
    }

    /**
     * Get a http request.
     *
     * @param string $url
     * @param int $timeout default 10 => 10s
     * @throws SzException 10200
     * @return string $result
     */
    public static function getHttpRequest($url, $timeout = 10)
    {
        $result = null;

        if (function_exists('curl_init')) {
            // Use CURL if installed...
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // default connect timeout 2s
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            if (false === $result) {
                curl_close($ch);
                throw new SzException(10200, array(curl_errno($ch), curl_error($ch)));
            }
            curl_close($ch);
        } else {
            // Non-CURL based version...
            $contextId = stream_context_create(array(
                'http' => array(
                    'timeout' => $timeout
                )
            ));
            $result = file_get_contents($url, 0, $contextId);
        }

        return $result;
    }

    /**
     * Get Random Element From Array. And if probability is 0, then return this bonus id directly without random logic. <br/>
     * Probability logic: The percentage probability means is determined by the total sum value.
     *
     * <pre>
     * e.g the total sum value is 10000
     * bonusId => probability
     * 10      => 500 5%
     * 11      => 500 5%
     * 12      => 500 5%
     * 13      => 400 4%
     * 14      => 50 0.5%
     * 15      => 50 0.5%
     * 16      => 1500 15%
     * 17      => 1500 15%
     * 18      => 5000 50%
     * </pre>
     *
     * @param array $probabilityList
     * <pre>
     * array(
     *      bonusId => probability
     * );
     * </pre>
     * @return int $bonusId
     */
    public static function getRandomElementByProbability($probabilityList)
    {
        // get bonus id when the probability is 0
        $result = array_search(0, $probabilityList);
        if ($result === false) {
            // get bonus id by probability
            $all = array_sum($probabilityList);
            $seed = mt_rand(1, $all);
            $sum = 0;
            foreach ($probabilityList as $id => $probability) {
                $sum += $probability;
                if ($seed <= $sum) {
                    $result = $id;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Get Random Int From String Range.
     *
     * <pre>
     * e.g '9,16' means 9 to 16.
     * </pre>
     *
     * @param string $randomRange
     * @return int
     * @throws SzException 10202, 10203
     */
    public static function getRandomIntFromRange($randomRange)
    {
        if (!preg_match("/\d,\d/", $randomRange)) { // invalid config
            throw new SzException(10202, $randomRange);
        }

        $rangeArray = explode(',', $randomRange);
        if ($rangeArray[0] >= $rangeArray[1]) { // min is bigger than max
            throw new SzException(10203, $randomRange);
        }

        return mt_rand($rangeArray[0], $rangeArray[1]);
    }

    /**
     * Calulate whether given percentage rate hit or not.
     *
     * @param int $rate
     * <pre>
     * shall be 1-100
     * if float, it should be 0.xx and will be multiplied by 100 (0.xx * 100 => xx%)
     * </pre>
     * @return boolean $hit
     */
    public static function calcPercentageRate($rate)
    {
        $rate += 0; // convert $rate to number
        if (is_float($rate)) {
            $rate = $rate * 100; // convert 0.3 => 30%
        }

        $hit = false;
        if ($rate <= 0) {
            // do nothing, $hit already FALSE
        } else {
            if ($rate >= 100) {
                $hit = true;
            } else {
                $sysRate = self::getRandomIntFromRange('1,100');
                if ($sysRate <= $rate) {
                    $hit = true;
                }
            }
        }

        return $hit;
    }

    /**
     * Format the given "underscored_word_group" as a "Human Readable Word Group".
     *
     * @param string $word
     * @return string $word
     */
    public static function wordHumanize($word)
    {
        $result = SzSystemCache::cache(SzSystemCache::UTIL_WORD_HUMANIZE, $word);
        if (!$result) {
            $result = ucwords(str_replace('_', ' ', $word));
            SzSystemCache::cache(SzSystemCache::UTIL_WORD_HUMANIZE, $word, $result);
        }
        return $result;
    }

    /**
     * Format the given "lower_case_and_underscored_word" as a "CamelCased" word.
     *
     * @param $word
     * @param bool $needUcFirst default false
     * @return string
     */
    public static function wordCamelize($word, $needUcFirst = false)
    {
        $result = SzSystemCache::cache(SzSystemCache::UTIL_WORD_CAMELIZE, $word);
        if (!$result) {
            $result = lcfirst(str_replace(' ', '', self::wordHumanize($word)));
            SzSystemCache::cache(SzSystemCache::UTIL_WORD_CAMELIZE, $word, $result); // cached values are always "LC FIRST"
        }
        if ($needUcFirst) {
            $result = ucfirst($result);
        }
        return $result;
    }

    /**
     * Format the given "camelCasedWord" as an "underscored_word".
     *
     * @param $word
     * @return string
     */
    public static function wordUnderscore($word)
    {
        $result = SzSystemCache::cache(SzSystemCache::UTIL_WORD_UNDERSCORE, $word);
        if (!$result) {
            $result = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
            SzSystemCache::cache(SzSystemCache::UTIL_WORD_UNDERSCORE, $word, $result);
        }
        return $result;
    }

    /**
     * Generate md5 hash string of the source string.
     *
     * @param string $source
     * @return string $hash
     */
    public static function genMd5($source)
    {
        return md5($source);
    }

    /**
     * Get string character size, using UTF-8 encoding.
     * <pre>
     * e.g.
     *     "hello世界" => 7
     *     "hello world" => 11
     *     "世界" => 2
     * </pre>
     *
     * @param string $str
     * @return int $len
     */
    public static function strUtf8CharLen($str)
    {
        return mb_strlen($str, 'UTF8');
    }

    /**
     * Get string length using system default function "strlen". <br/>
     * One chinese character occupies three byte size.
     *
     * @param string $str
     * @return int $len
     */
    public static function strLen($str)
    {
        return strlen($str);
    }

    /**
     * Calculate exp & level update.
     *
     * There are two mode of exp gaining:
     *     1. non-continuous mode: when level up, the exp value would be cleared to 0, and gain till next level again
     *     2. continuous mode: when level up, the exp value would not be cleared to 0
     *
     * <pre>
     * $expLadder = array(
     *     level => array(
     *         'level' => levelValue,
     *         'exp'   => expRequired
     *     ),
     *     ...
     * )
     * Actually the format of the $expLadder we need has only one rule, there have to be the key "exp".
     * The key "level" here is only the result of CCS output, we won't use it.
     *
     * $expLadder describe how much exp required to reach this config level:
     * e.g
     * $expLadder = array(
     *     2 => 100, // level up to 2 require exp 100
     *     3 => 250, // level up to 3 require exp 250
     * );
     * $level = 1; // current level is 1
     * $exp = 0; // current exp is 0
     *
     * when $exp was raised to 100, $level could be promoted to 2,
     * and $exp was reduced to 0 again.
     * </pre>
     *
     * @param int $level passed by reference
     * @param int $exp passed by reference
     * @param array $expLadder
     * @param boolean $continuousMode default false
     * @param boolean $allowLevelUpMultiTimes default false, is level up multi times at one exp calculation, default is false, means only one level can be gained at a time
     * @return void
     */
    public static function calcExpUpdate(&$level, &$exp, $expLadder, $continuousMode = false, $allowLevelUpMultiTimes = false)
    {
        $levelUp = false;
        $ladderExpValue = (SzUtility::checkArrayKey($level + 1, $expLadder)) ? $expLadder[$level + 1]['exp'] : false;
        if ($ladderExpValue // next level required exp exists, means did not reach the max level yet
            && $exp >= $ladderExpValue) { // level up
            ++$level;
            if (!$continuousMode) { // non-continous mode, we need to reduce the remaining exp value
                $exp -= $ladderExpValue;
            }
            $levelUp = true;
        }
        if ($levelUp && $allowLevelUpMultiTimes) {
            // check can level up again or not
            $nextLadderExp = (SzUtility::checkArrayKey($level + 1, $expLadder)) ? $expLadder[$level + 1]['exp'] : false;
            if ($nextLadderExp && $exp >= $nextLadderExp) { // user not reach the max level, and still can levelup
                self::calcExpUpdate(
                    $level,
                    $exp,
                    $expLadder,
                    $continuousMode
                );
            }
        }
    }

    /**
     * Handle level up bonus items.
     * It only supports item bonus.
     *
     * <pre>
     * $bonusConfigs = array(
     *     level => array(
     *         'level'  => levelValue,
     *         'reward' => array(
     *             itemId => count,
     *             ...
     *         )
     *     ),
     *     ...
     * )
     * </pre>
     *
     * @param int $originLevel level value before exp change
     * @param int $finalLevel level value after exp change
     * @param array $bonusConfigs
     * @return array $bonus
     * <pre>
     * array(itemId => count)
     * </pre>
     */
    public static function handleLevelUpBonus($originLevel, $finalLevel, $bonusConfigs)
    {
        $bonus = array();

        if ($originLevel >= $finalLevel) {
            // wrong level provided, do nothing
            return $bonus;
        }

        for ($targetLevel = $originLevel + 1; $targetLevel <= $finalLevel; $targetLevel++) {
            $levelUpBonus = $bonusConfigs[$targetLevel]['reward'];
            if ($levelUpBonus && is_array($levelUpBonus)) {
                foreach ($levelUpBonus as $itemId => $count) {
                    if (self::checkArrayKey($itemId, $bonus)) {
                        $bonus[$itemId] += $count;
                    } else {
                        $bonus[$itemId] = $count;
                    }
                }
            }
        }

        return $bonus;
    }

    /**
     * Returns the offset from the origin timezone to the remote timezone, in seconds.
     *
     * @param string $originTz
     * @param string $remoteTz default 'GMT'
     * @return int
     */
    public static function getTimeZoneOffset($originTz, $remoteTz = 'GMT')
    {
        $originTz = new DateTime('now', new DateTimeZone($originTz));
        $originOffset = $originTz->getOffset();

        if ($remoteTz != 'GMT' && $remoteTz != 'UTC') {
            $remoteTz = new DateTime('now', new DateTimeZone($remoteTz));
            $remoteOffset = $remoteTz->getOffset();
        } else {
            $remoteOffset = 0;
        }

        return $originOffset - $remoteOffset;
    }


    /**
     * Refresh regularly generated resource according to now time & last time resource charged time.
     *
     * @param int $resource
     * @param int $lastChargedTime
     * @param int $limit
     * @param int $recoveryInterval
     * @param int $recoveryAmount
     * @param int $reqTime
     * @throws SzException 10202
     * @return int $resource
     */
    public static function refreshRegularlyGeneratedResourceCount($resource, $lastChargedTime, $limit, $recoveryInterval, $recoveryAmount, $reqTime)
    {
        // prepare params
        $recoveryTimes = floor(($reqTime - $lastChargedTime) / $recoveryInterval);
        if ($recoveryTimes) { // resource recovery time reached, go through following logics
            if ($resource < $limit) { // if resource is smaller than limit, means resource can recover
                $resource += $recoveryTimes * $recoveryAmount;
                if ($resource > $limit) { // resource recovery exceeded the limit, change it to limit
                    $resource = $limit;
                }
            }
            // if resource recovered, time need to be set to now
            $lastChargedTime += $recoveryTimes * $recoveryInterval;
        }

        return array($resource, $lastChargedTime);
    }

    /**
     * Return 'http:' | 'https:' | ''.
     *
     * <pre>
     * Empty string also useful when display the web page, like:
     * <script src="{$protocol}//www.host.com/link.js"></script>
     * </pre>
     *
     * @return string $protocol
     */
    public static function getProtocol()
    {
        $protocol = '';

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') {
            $protocol = 'https:';
        } else if (isset($_SERVER['HTTPS']) && (!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == 'off')) {
            $protocol = 'http:';
        }

        return $protocol;
    }

    /**
     * Get current cycle cooldown count
     *
     * @param int $time default null, given time
     * @param int $startTime
     * @param int $coolDown
     * @return int
     */
    public static function getCurrentCycleCoolDownCount($time = null, $startTime, $coolDown)
    {
        $curTime = is_null($time) ? SzTime::getTime() : $time;
        return floor(($curTime - $startTime) / $coolDown);
    }

    /**
     * Get current cycle cooldown time via give time
     *
     * @param int $time default null, given time
     * @param int $startTime
     * @param int $coolDown
     * @return int
     */
    public static function getCurrentCycleCoolDownTime($time = null, $startTime, $coolDown)
    {
        return $startTime + (SzUtility::getCurrentCycleCoolDownCount($time, $startTime, $coolDown)) * $coolDown;
    }

    /**
     * Get next cycle cooldown time via give time
     *
     * @param int $clientTime default null
     * @param int $startTime
     * @param int $coolDown
     * @return int
     */
    public static function getNextCycleCoolDownTimeViaGivenTime($clientTime = null, $startTime, $coolDown)
    {
        return $startTime + (SzUtility::getCurrentCycleCoolDownCount($clientTime, $startTime, $coolDown) + 1) * $coolDown;
    }


    /**
     * Validate remote ip address is valid or not.
     *
     * @throws SzException 10801
     * @return void
     */
    public static function validateIpAddress()
    {
        $appConfig = SzConfig::get()->loadAppConfig('app');

        $valid = true;
        $clientIp = null;

        // ip address validation
        if (is_array($appConfig['IP_WHITE_LIST']) && $appConfig['IP_WHITE_LIST']) {
            $clientIp = self::getClientHost();
            if ($clientIp) {
                try {
                    SzValidator::validateIpAddress($clientIp);
                    if (!in_array($clientIp, $appConfig['IP_WHITE_LIST'])) {
                        $valid = false; // ip not in the white list
                    }
                } catch (Exception $e) {
                    $valid = false; // ip address string format invalid
                }
            } else {
                $valid = false; // ip address string empty
            }
        }

        if (!$valid) {
            throw new SzException(10801, $clientIp);
        }
    }

    /**
     * Use gzcompress to compress string data, encoding ZLIB_ENCODING_DEFLATE.
     *
     * @see gzcompress
     *
     * @param string $data
     * @param boolean $needTrim default true
     * @return string
     */
    public static function compress($data, $needTrim = true)
    {
        if ($needTrim) {
            $data = trim($data);
        }
        return gzcompress($data);
    }

    /**
     * Use gzuncompress to decompress string data, encoding ZLIB_ENCODING_DEFLATE.
     *
     * @param string $data
     * @param boolean $needTrim default true
     * @return string
     */
    public static function decompress($data, $needTrim = true)
    {
        if ($needTrim) {
            $data = trim($data);
        }
        return gzuncompress($data);
    }

    /**
     * Encode with base64_encode.
     *
     * @param string $data
     * @param boolean $needTrim default true
     * @return string
     */
    public static function base64Encode($data, $needTrim = true)
    {
        if ($needTrim) {
            $data = trim($data);
        }
        return base64_encode($data);
    }

    /**
     * Decode with base64_decode.
     *
     * @param string $data
     * @param boolean $needTrim default true
     * @return string
     */
    public static function base64Decode($data, $needTrim = true)
    {
        if ($needTrim) {
            $data = trim($data);
        }
        return base64_decode($data);
    }

    /**
     * DES encrypt specified string data, and base64_encode it.
     *
     * @param string $data
     * @param string $secretKey
     * @param string $ivKey
     * @return string
     */
    public static function encrypt($data, $secretKey = 'KEY=SZ00', $ivKey = 'KEY=SZ00')
    {
        $data = trim($data);
        $desc = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');

        mcrypt_generic_init($desc, $secretKey, $ivKey);

        $blockSize = mcrypt_get_block_size(MCRYPT_3DES, 'cbc');
        $paddingChar = $blockSize - (strlen($data) % $blockSize);
        $data .= str_repeat(chr($paddingChar), $paddingChar);

        $cipher = mcrypt_generic($desc, $data);
        $cipher = base64_encode($cipher);

        mcrypt_generic_deinit($desc);
        mcrypt_module_close($desc);

        return trim($cipher);
    }

    /**
     * Decrypt DES encrypted base64_encode string data.
     *
     * @param string $data
     * @param string $secretKey
     * @param string $ivKey
     * @return string
     */
    public static function decrypt($data, $secretKey = 'KEY=SZ00', $ivKey = 'KEY=SZ00')
    {
        $data = trim($data);
        $desc = mcrypt_module_open(MCRYPT_3DES, '', 'cbc', '');

        mcrypt_generic_init($desc, $secretKey, $ivKey);

        $data = base64_decode($data);
        $decryptData = mdecrypt_generic($desc, $data);

        mcrypt_generic_deinit($desc);
        mcrypt_module_close($desc);

        $tailStr = substr($decryptData, -1);
        if (ord($tailStr) >= 0 && ord($tailStr) <= 8) {
            $decryptData = str_replace($tailStr, '', $decryptData);
        }

        return trim($decryptData);
    }

    /**
     * build api sign with params.
     *
     * @param $params
     * @param $secret
     * @return string
     */
    public static function makeSign($params, $secret)
    {
        $source = self::buildQueryString($params);
        $sign = base64_encode(hash_hmac("sha1", $source, $secret, true));

        return $sign;
    }
}
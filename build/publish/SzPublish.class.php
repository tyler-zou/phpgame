<?php
require 'lib/SzAbstractPublishPlatform.class.php';

class SzPublish
{

    const MAGIC_TOKEN = '52d0ee3c031dd';

    /**
     * @var SzPublish
     */
    private static $instance;

    private static $PLATFORMS = array(
        'facebook',
        'sina',
        'tencent',
        'armorgame',
        'vk',
        'ogz',
    );

    /**
     * implementation of SzAbstractPublishPlatform
     *
     * @var SzAbstractPublishPlatform
     */
    private $parser;

    /**
     * platform id of the player
     *
     * @var string
     */
    private $platformId;

    /**
     * configs from './publish.config.php'
     *
     * @var array
     */
    private $configs = array();

    /**
     * Load configs & parse platform id.
     *
     * @param boolean $needLogin default true, does it necessary to direct page to platform login page, if user token not found
     * @throws Exception
     * @return SzPublish
     */
    public function __construct($needLogin = true)
    {
        $this->configs = require 'publish.config.php';
        $platform = $this->configs['PLATFORM'];

        // load platform name
        if (is_null($platform)) {
            $platform = 'none'; // null platform, means test environment, "none" => SzPublishPlatformNone
        } else if (!in_array($platform, self::$PLATFORMS)) {
            throw new Exception('Publish platform name defined is invalid!');
        }

        // load platform parser
        $platformClass = 'SzPublishPlatform' . ucfirst($platform);
        require "lib/implements/{$platformClass}.class.php";
        $this->parser = new $platformClass($this->configs);

        // parse platform id
        $this->platformId = $this->parser->parsePlatformId($needLogin);
        if (!$this->platformId) {
            echo "Cannot parse the platform id, platform: {$platform}."; exit(1);
        }

        // validate ip address
        $this->validateIpAddress();
    }

    /**
     * Get the instance of SzPublish.
     *
     * @param boolean $needLogin default true, does it necessary to direct page to platform login page, if user token not found
     * @return SzPublish $instance
     */
    public static function get($needLogin = true)
    {
        if (!self::$instance) {
            self::$instance = new SzPublish($needLogin);
        }
        return self::$instance;
    }

    /**
     * Get the developer dir name.
     *
     * <pre>
     * This result is related to environment param "ENV", defined in "publish.config.php".
     * ENV:
     *     DEV: $_REQUEST['dev'] will be read, if not set, 'main' returned
     *     LIVE | STAGING: always 'main' returned
     * </pre>
     *
     * @return string
     */
    public function getDeveloper()
    {
        $developer = 'master';
        if ($this->configs['ENV'] == 'DEV') {
            $developer = isset($_REQUEST['dev']) ? $_REQUEST['dev'] : $developer;
        }

        return $developer;
    }

    /**
     * Get app version. "FALSE" returned, when user is blocked by throttle.
     *
     * @return string
     */
    public function getAppVersion()
    {
        $version = false;

        $onlineVer = $this->configs['ONLINE_VER'];
        $previewVer = $this->configs['PREVIEW_VER'];

        if ($onlineVer == $previewVer) {
            // not in publishing process
            $throttleRate = $this->configs['THROTTLE_PERCENT'];
            if ($throttleRate == 0 // no throttle
                || $this->checkThrottle($throttleRate) // passed the throttle
            ) {
                $version = $onlineVer;
            }
        } else {
            // in publishing process
            if ($this->checkThrottle($this->configs['PREVIEW_PERCENT'])) {
                $version = $previewVer; // pass the throttle means preview player
            } else {
                $version = $onlineVer;
            }
        }

        return $version;
    }

    /**
     * Check platform id can pass the throttle or not.
     * This function can be used both "preview" & "throttle".
     * Result will always be "true", if platform id is in "DEV_LIST" | "WHITE_LIST".
     *
     * @param int $rate 0-99
     * @return boolean
     */
    private function checkThrottle($rate)
    {
        $result = false;

        if (in_array($this->platformId, $this->configs['DEV_LIST'])
            || in_array($this->platformId, $this->configs['WHITE_LIST'])
        ) {
            // in white list
            $result = true;
        } else {
            // while rate is 0, we didn't want any one pass the throttle
            if ($rate == 0) {
                return $result;
            }

            // not in white list
            if ($rate >= 100) {
                $rate = 99; // since we use 1-99 to do the comparison
            }

            $tailingNum = $this->platformId;
            if (!is_numeric($tailingNum)) {
                $tailingNum = $this->pickNumbersInStr($tailingNum); // pick up all the numbers in the string
            }
            // convert short string to at least 3 char long int string
            // e.g 1 => 001, 21 => 021
            $tailingNum = sprintf('%1$03d', $tailingNum);
            // get the last two char of number, and convert from string to int
            // range: 00 - 99
            $tailingNum = (int)substr($tailingNum, -2);

            $result = ($tailingNum <= $rate);
        }

        return $result;
    }

    /**
     * Collect all the numbers in one string and combine them into string.
     * <pre>
     * e.g
     *     9C6F7446F21FD39E3AAD138985BF8F86 => 96744621393138985886
     * </pre>
     *
     * @param string $str
     * @return string
     */
    private function pickNumbersInStr($str)
    {
        preg_match_all("/\d+/", $str, $match);
        /**
         * e.g $str = '9C6F7446F21FD39E3AAD138985BF8F86'
         * $match = array(
         *     0 =>
         *         array(
         *             0 => string '9'
         *             1 => string '6'
         *             2 => string '7446'
         *             3 => string '21'
         *             4 => string '39'
         *             5 => string '3'
         *             6 => string '138985'
         *             7 => string '8'
         *             8 => string '86'
         *         ),
         *     ),
         * );
         */

        return implode('', $match[0]);
    }

    /**
     * Get the user platform id.
     *
     * @return string $platformId
     */
    public function getPlatformId()
    {
        return $this->platformId;
    }

    /**
     * Validate remote ip address is valid or not.
     *
     * <pre>
     * If config item "IP_WHITE_LIST" in publish.config.php is not empty,
     * this validation would be enabled, otherwise it would be skipped.
     * </pre>
     *
     * @return void
     */
    private function validateIpAddress()
    {
        $valid = true;
        $clientIp = null;

        // ip address validation
        if (is_array($this->configs['IP_WHITE_LIST']) && $this->configs['IP_WHITE_LIST']) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
            if ($clientIp) {
                try {
                    SzValidator::validateIpAddress($clientIp);
                    if (!in_array($clientIp, $this->configs['IP_WHITE_LIST'])) {
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
            echo "Invalid ip address: {$clientIp}."; exit(1);
        }
    }

}
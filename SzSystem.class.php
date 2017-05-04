<?php
require 'lib/utility/SzUtility.class.php';
require 'lib/cache/SzSystemCache.class.php';
require 'lib/config/SzConfig.class.php';
require 'lib/autoload/SzAutoload.class.php';

class SzSystem
{

    public static $FRAME_ROOT  = ''; // with version
    public static $APP_ROOT    = ''; // with version
    public static $MODULE_ROOT = ''; // without version

    public static $WWW_RELATIVE_PATH = '';

    /**
     * moduleName => moduleVersion
     *
     * @var array
     */
    public static $MODULE_VERS = array();

    /**
     * System user identify: possible to be game player id || platform user id ...
     *
     * @var string
     */
    private static $IDENTIFY = null;

    /**
     * @var int $reqTime
     */
    private static $reqTime = null;

    /**
     * @var int $sysTime
     */
    private static $sysTime = null;

    /**
     * Initialize the framework system.
     *
     * @param string $appRoot
     * @param string $wwwRelativePath
     * <pre>
     * the relative between host | webRoot and the resources
     * e.g
     *     web:
     *         http://$host . SzSystem::$WWW_RELATIVE_PATH . '/js/...'
     *     file system:
     *         $appRoot = $nginxWebRoot . '/' . $wwwRelativePath;
     *                    '/home/php/demo/web' . '/' . 'app/devA/v0.1.1/www'
     * </pre>
     * @param string $moduleRoot default null, means no modules required
     * @param array $moduleVers
     * <pre>
     * default null, means no modules required <br/>
     *
     * array(moduleName => version)
     * e.g
     *     array("module_item" => "v0.1.1")
     * </pre>
     * @return void
     */
    public static function init($appRoot, $wwwRelativePath, $moduleRoot = null, $moduleVers = null)
    {
        // set roots
        self::$FRAME_ROOT = __DIR__;
        self::$APP_ROOT = $appRoot;
        self::$WWW_RELATIVE_PATH = $wwwRelativePath;
        if (!is_null($moduleRoot) && !is_null($moduleVers)) {
            self::$MODULE_ROOT = $moduleRoot;
            self::$MODULE_VERS = $moduleVers;
        }

        // init config
        SzConfig::init();
        // init autoload
        SzAutoload::init();
        // init logger
        SzLogger::init();
        // init error handler
        SzErrorHandler::init();
        // init persistence cache
        SzPersister::init();
        // init context
        SzContextFactory::init();
        // init controller
        SzController::init();
        // init timezone
        date_default_timezone_set(SzConfig::get()->loadAppConfig('app', 'TIMEZONE'));
        // start the profiler if necessary
        SzProfHandler::run();
        // register log closing event at the end of the init process
        // it cannot be done in SzLogger::init(), otherwise logs after event "SzAbstractLogger::closeLog" won't be logged
        self::registerShutdownHandler(SzLogger::get(), 'closeLog');
    }

    /**
     * Register one shutdown handler function.
     *
     * @param object $instance instance of a class
     * @param string $functionName handler function name
     * @return void
     */
    public static function registerShutdownHandler($instance, $functionName)
    {
        register_shutdown_function(array($instance, $functionName));
    }

    /**
     * Register one error handler function.
     *
     * @param object $instance instance of a class
     * @param string $functionName handler function name
     * @return void
     */
    public static function registerErrorHandler($instance, $functionName)
    {
        set_error_handler(array($instance, $functionName));
    }

    /**
     * Register one exception handler function.
     *
     * @param object $instance instance of a class
     * @param string $functionName handler function name
     * @return void
     */
    public static function registerExceptionHandler($instance, $functionName)
    {
        set_exception_handler(array($instance, $functionName));
    }

    /**
     * Handle framework level user identify info.
     *
     * <pre>
     * Possible:
     *     1. Save identify, if given $identify is not null, and can be overwrote
     *     2. Already saved identify, return it directly
     *     3. Save & return the "userId" transferred via HTTP POST or GET
     *     4. Param "userId" of the first request in "*" requests
     * </pre>
     *
     * @see SzSystem::$IDENTIFY
     *
     * @param array $inputs default null, params parsed from $_REQUEST['*'], possible to be provided from outside
     * @param string $identify default null
     * <pre>
     * default null, means retrieve identify info
     * not null, means save identify info
     * </pre>
     * @return string $identify
     */
    public static function handleIdentify($inputs = null, $identify = null)
    {
        // 1. save identify
        if (!is_null($identify)) {
            self::$IDENTIFY = $identify;
            return self::$IDENTIFY;
        }

        // 2. already saved identify
        if (!is_null(self::$IDENTIFY)) {
            return self::$IDENTIFY;
        }

        // 3. "userId" transferred via HTTP
        $httpUserId = SzParam::getReqParam('userId');
        if ($httpUserId) {
            self::$IDENTIFY = $httpUserId;
            return self::$IDENTIFY;
        }

        // 4. "userId" of the first request in "*" requests
        if (is_null($inputs)) {
            $inputs = SzParam::parseRawInputs('*');
        }

        if (!is_array($inputs)) {
            return null; // wrong format
        }

        /**
         * Retrieve the first request：
         * array(
         *     $reqName,
         *     array(
         *         $reqParam1,
         *         $reqParam2,
         *         ...
         *     )
         * )
         */
        $input = array_shift($inputs);
        if (!is_array($input)) {
            return null; // wrong format
        }

        if ($input && is_array($input)) {
            $params = array_pop($input);
            if (is_array($params)) {
                self::$IDENTIFY = array_shift($params);
            }
        }

        return self::$IDENTIFY;
    }

    /**
     * 保存/获取 接口调用时间
     *
     * @param int $timestamp default null
     * @return int $this->reqTime
     */
    public static function getReqTime($timestamp = null)
    {
        if (!is_null($timestamp)) {
            self::$reqTime = $timestamp;
        }
        if (is_null(self::$reqTime)) {
            self::$reqTime = self::getSysTime();
        }
        return self::$reqTime;
    }

    /**
     * 获得服务器时间
     *
     * @return int $this->sysTime
     */
    public static function getSysTime()
    {
        if (is_null(self::$sysTime)) {
            self::$sysTime = SzTime::getTime();
            if (SzConfig::get()->loadAppConfig('app', 'ENV') == 'DEV') {
                $timeOffset = intval(SzParam::getReqParam('timeOffset'));
                self::$sysTime += ($timeOffset == 0) ? SzConfig::get()->loadAppConfig('app', 'TIME_OFFSET') : $timeOffset;
            }
        }
        return self::$sysTime;
    }
}
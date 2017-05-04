<?php
class SzErrorHandler
{

    /**
     * @var SzErrorHandler
     */
    private static $instance;

    /**
     * Initialize SzErrorHandler.
     *
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzErrorHandler();
        SzSystem::registerShutdownHandler(self::$instance, 'handleFatal');
        SzSystem::registerErrorHandler(self::$instance, 'handleError');
        SzSystem::registerExceptionHandler(self::$instance, 'handleException');
    }

    /**
     * Get instance of SzErrorHandler.
     *
     * @return SzErrorHandler
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * PHP error numbers which can be caught by function registered in "set_error_handler". <br/>
     * array(int => string)
     *
     * @link http://www.php.net/manual/en/errorfunc.constants.php
     *
     * @var array
     */
    private $errnos = array(
        E_ERROR        => 'E_ERROR',
        E_WARNING      => 'E_WARNING',
        E_NOTICE       => 'E_NOTICE',
        E_USER_ERROR   => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE  => 'E_USER_NOTICE',
        E_DEPRECATED   => 'E_DEPRECATED',
    );

    /**
     * SzErrorHandler initialized by SzSystem, and start with handleFatal(shutdown handler) enabled default.
     * And sometime sub implementation system need to handle shutdown event themselves, and maybe need to disable the shutdown
     * handler implemented in this class.
     *
     * This flag is designed by this purpose.
     *
     * @var bool default true
     */
    private $needHandleFatal = true;

    /**
     * Singleton insurance.
     *
     * @return SzErrorHandler
     */
    private function __construct()
    {
    }

    /**
     * Handle error. <br/>
     *
     * The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
     * @link http://php.net/manual/en/function.set-error-handler.php
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        $message = $this->formatError($errno, $errstr, $errfile, $errline);
        if ($message) {
            SzLogger::get()->error('SzErrorHandler: Error caught', $message);
            SzController::get()->logExceptionExitReqStatus();
        }
    }

    /**
     * Format error info into a array for logging.
     *
     * The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
     * @link http://php.net/manual/en/function.set-error-handler.php
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return string $error
     * @return boolean <b>true</b> means no need to execute PHP internal error handler,
     *                 <b>string</b> is normal error message
     */
    public function formatError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return array();
        }

        if (SzUtility::checkArrayKey($errno, $this->errnos)) {
            $errtype = $this->errnos[$errno];
        } else {
            $errtype = "E_UNKNOWN[{$errno}]";
        }

        $message = array(
            'type'    => $errtype,
            'no'      => $errno,
            'content' => $errstr,
            'file'    => $errfile,
            'line'    => $errline
        );
        if (SzConfig::get()->loadAppConfig('app', 'LOG_ERROR_TRACE')) {
            $message['trace'] = var_export(debug_backtrace(), true);
        }

        return $message;
    }

    /**
     * PHP fatal error cannot be caught by SzSystem::registerErrorHandler,
     * we have to use error_get_last to get last encountered issue and check whether it's an error or not.
     *
     * @return void
     */
    public function handleFatal()
    {
        if (!$this->needHandleFatal) {
            return;
        }

        $error = error_get_last();
        if ($error['type'] == E_ERROR) {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Mark handleFatal event to disabled status.
     *
     * return void
     */
    public function unregisterHandleFatal()
    {
        $this->needHandleFatal = false;
    }

    /**
     * Handle exception.
     *
     * @param Exception $e
     * @throws Exception
     * @return void
     */
    public function handleException($e)
    {
        // log exception
        $logger = SzLogger::get();
        if ($logger) { // may logger not been initialized yet
            $message = array(
                'msg'   => $e->getMessage(),
                'code'  => $e->getCode()
            );
            if (SzConfig::get()->loadAppConfig('app', 'LOG_EXCEPTION_TRACE')) {
                $message['trace'] = $e->getTraceAsString();
            }
            $logger->error('SzErrorHandler: Exception caught', $message);
            SzController::get()->logExceptionExitReqStatus();
        }

        if ($e instanceof SzException) {
            // system exception, format & response to client
            $resManager = new SzResponseManager();
            $resManager->setCode($e->getCode());
            $resManager->setBody($e->getMessage());
            $resManager->send(); // exit(1);
            // FIXME cannot exit here for framework unit testing purpose, will this cause any issue?
        } else {
            // non system exception, also let PHP handle it
            throw $e;
        }
    }

}
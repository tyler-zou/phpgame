<?php
class SzLogger
{
    const LOG_TYPE_SYSLOG   = 'SYSLOG';
    const LOG_TYPE_FILELOG  = 'FILELOG';

    /**
     * @var SzAbstractLogger
     */
    private static $instance;

    /**
     * Initialize SzLogger.
     *
     * @throws SzException
     * @return void
     */
    public static function init()
    {
        $configs = SzConfig::get()->loadAppConfig('logger');

        $logType = $configs['LOG_TYPE'];
        switch ($logType) {
            case self::LOG_TYPE_SYSLOG:
                self::$instance = new SzSysLogger($configs['LOG_LEVEL']);
                break;
            case self::LOG_TYPE_FILELOG:
                self::$instance = new SzFileLogger(
                    $configs['LOG_LEVEL'],
                    $configs['LOG_FILE']
                );
                break;
            default:
                throw new SzException(10300, $logType);
                break;
        }
    }

    /**
     * Singleton insurance.
     *
     * @return SzLogger
     */
    private function __construct()
    {
    }

    /**
     * Get Logger.
     *
     * @return SzAbstractLogger
     */
    public static function get()
    {
        return self::$instance;
    }

}
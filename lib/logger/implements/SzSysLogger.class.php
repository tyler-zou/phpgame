<?php
class SzSysLogger extends SzAbstractLogger
{
    /**
     * @see SzAbstractLogger::__construct
     */
    public function __construct($level = null)
    {
        parent::__construct($level);
        openlog(
            SzConfig::get()->loadAppConfig('logger', 'LOG_IDENTITY'),
            LOG_PID,
            SzConfig::get()->loadAppConfig('logger', 'LOG_FACILITY')
        );
    }

    /**
     * @see SzAbstractLogger::doLog
     */
    protected function doLog($level, $message, $params = null)
    {
        if (is_object($message) || is_array($message)) {
            $message = var_export($message, true);
        }

        syslog($level, $this->formatMessage($level, $message, $params));

        return true;
    }

    /**
     * @see SzAbstractLogger::closeLog
     */
    public function closeLog()
    {
        closelog();
    }
}
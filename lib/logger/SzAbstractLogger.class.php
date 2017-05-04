<?php
abstract class SzAbstractLogger
{
    const LOG_CHANNEL_PROGRAM    = 'program';
    const LOG_CHANNEL_BUSINESS   = 'business';

    const LOG_TAG_SESSION = 'session';
    const LOG_TAG_CHANNEL = 'channel';

    const CHANNEL_LOG_FILTER_FIELDS = "['act']";

    const DEBUG   = LOG_DEBUG;   // 7. debug level
    const INFO    = LOG_INFO;    // 6. info level
    const NOTICE  = LOG_NOTICE;  // 5. notice level
    const WARNING = LOG_WARNING; // 4. warning level
    const ERROR   = LOG_ERR;     // 3. error level

    const KEY_PREFIX = '_';

    /**
     * @var array $loggerSetting
     */
    private $loggerSetting = array(
        'LOG_LEVEL' => LOG_WARNING,
        'LOG_TYPE'  => 'SYSLOG',
        'LOG_FILE'  => '',
        'LOG_IDENTITY' => 'PHP-CGI',
        'LOG_FACILITY' => LOG_USER,
        'LOG_MAX_SIZE' => 1024,
        'LOG_RETAIN_FIELD' => array('act'),
        'LOG_FILTER'=> 'UNLABELED'
    );

    /**
     * @var array $needPrefixKeys
     */
    private $needPrefixKeys = array(
        'filter', 'time', 'channel', 'level'
    );

    protected $logLevel = LOG_DEBUG; // default log level

    /**
     * Construct, set default log level.
     *
     * @param int $level default null, means use DEBUG as default log level
     * @return SzAbstractLogger
     */
    public function __construct($level = null)
    {
        if (!is_null($level)) {
            $this->setLogLevel($level);
        }

        // default setting
        $loggerSetting = SzConfig::get()->loadAppConfig('logger');
        foreach ($loggerSetting as $key => $val) {
            $this->loggerSetting[$key] = $val;
        }
    }

    /**
     * User APIs:
     * debug | info | notice | warn | error
     * Param $message is required, thus, users can add various numbers of params, they are additional params.
     * =>
     * log
     * Check log level to determine whether log shall be logged
     * =>
     * doLog
     * Real log logic implemented by child classes
     * -------------------------------
     * The content of additional params can be found in comments of function: log
     */
    /**
     * Log debug message.
     *
     * @see SzAbstractLogger::log
     *
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    public function debug($message, $params = null)
    {
        return $this->log(self::DEBUG, $message, $params);
    }

    /**
     * Log info message.
     *
     * @see SzAbstractLogger::log
     *
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    public function info($message, $params = null)
    {
        return $this->log(self::INFO, $message, $params);
    }

    /**
     * Log notice message.
     *
     * @see SzAbstractLogger::log
     *
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    public function notice($message, $params = null)
    {
        return $this->log(self::NOTICE, $message, $params);
    }

    /**
     * Log warning message.
     *
     * @see SzAbstractLogger::log
     *
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    public function warn($message, $params = null)
    {
        return $this->log(self::WARNING, $message, $params);
    }

    /**
     * Log error message.
     *
     * @see SzAbstractLogger::log
     *
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    public function error($message, $params = null)
    {
        return $this->log(self::ERROR, $message, $params);
    }

    /**
     * Log message.
     *
     * @param int $level
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    protected function log($level, $message, $params = null)
    {
        if ($level <= $this->logLevel) {
            $this->doLog($level, $message, $params);
        } else {
            return false;
        }
        return true;
    }

    /**
     * Log functionality to be implemented.
     *
     * @param int $level
     * @param string $message
     * @param array $params default null
     * @return boolean
     */
    abstract protected function doLog($level, $message, $params = null);

    /**
     * Close log resource when no longer necessary.
     *
     * @return void
     */
    abstract public function closeLog();

    /**
     * Set current log level.
     *
     * @param int $level
     * @return void
     */
    public function setLogLevel($level)
    {
        if (in_array($level, array(self::DEBUG, self::INFO, self::NOTICE, self::WARNING, self::ERROR))) {
            $this->logLevel = $level;
        }
    }

    /**
     * Format message into the format we want it to be.
     *
     * @param int $level
     * @param string $message
     * @param array $params default null
     * @return string
     */
    protected function formatMessage($level, $message, $params = null)
    {
        if (SzUtility::checkArrayKey(self::LOG_TAG_SESSION, $params)) {
            $session = $params[self::LOG_TAG_SESSION];
            unset($params[self::LOG_TAG_SESSION]);
        } else {
            $session = null;
        }

        $channel = self::LOG_CHANNEL_PROGRAM;
        if (SzUtility::checkArrayKey(self::LOG_TAG_CHANNEL, $params)) {
            if ($params[self::LOG_TAG_CHANNEL] == self::LOG_CHANNEL_PROGRAM
                || $params[self::LOG_TAG_CHANNEL] == self::LOG_CHANNEL_BUSINESS) {
                $channel = $params[self::LOG_TAG_CHANNEL];
                unset($params[self::LOG_TAG_CHANNEL]);
            }
        }

        /**
         * When channel is business, we did not need format message
         */
        if ($channel == self::LOG_CHANNEL_BUSINESS) {
            $formatMessage = $message;
        } else {
            $formatMessage = array(
                "identify"  => SzSystem::handleIdentify(),
                "info"      => $message,
                "session"   => $session,
                "params"    => $this->formatParams($params),
            );
        }

        $result = array(
            "filter"    => $this->loggerSetting['LOG_FILTER'],
            "time"      => SzTime::getTime(),
            "channel"   => $channel,
            "level"     => $level,
            "message"   => $formatMessage
        );

        return $this->handleLoggerSize($result);
    }

    /**
     * Handle logger size, if logger size exceed max size, message should be substr
     * And send a warning logger
     *
     * @param array $logger
     * <pre>
     * array(
     *  "time"      => SzTime::getTime(),
     *  "channel"   => $channel,
     *  "level"     => $level,
     *  "message"   => $formatMessage
     * )
     * </pre>
     * @return string
     */
    protected function handleLoggerSize($logger)
    {
        $strLogger = json_encode($logger);
        $exceedSize = SzUtility::strLen($strLogger) - $this->loggerSetting['LOG_MAX_SIZE'];

        if ($exceedSize > 0
            && SzUtility::checkArrayKey('message', $logger)) {
            if ($logger['channel'] == self::LOG_CHANNEL_BUSINESS) {
                $message = json_decode($logger['message'], true);
                // handle business logger retain field
                foreach ($message as $field => $val) {
                    if (!in_array($field, $this->loggerSetting['LOG_RETAIN_FIELD'])) {
                        $message[$field] = null;
                    }
                }
                $this->warn('SzLogger: logger size is out of limit, Max Logger Size:' .  $this->loggerSetting['LOG_MAX_SIZE'] . '!', $message);
            } else {
                $message = null;
            }

            $logger['message'] = json_encode($message);
        }

        return json_encode($logger);
    }

    /**
     * English date format will be format into a Unix timestamp
     * Some array key will be add a prefix, setting in $this->needPrefixKeys
     *
     * @param array $params default null
     * @return array
     */
    protected function formatParams($params = null)
    {
        if (!is_array($params) || empty($params)) {
            return $params;
        }

        foreach ($params as $key => $val) {
            if (in_array($key, $this->needPrefixKeys)) {
                unset($params[$key]);
                $key = self::KEY_PREFIX . $key;
            }

            if (is_array($val)) {
                $val = $this->formatParams($val);
            } elseif (is_string($val)) {
                if ($unixTime = strtotime($val)) {
                    $val = $unixTime;
                }
            }

            $params[$key] = $val;
        }

        return $params;
    }
}


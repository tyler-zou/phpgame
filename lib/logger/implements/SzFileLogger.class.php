<?php
/**
 * Class SzFileLogger
 * <pre>
 * <b>NOTE:</b>
 * This class is not thread safe, since it's using append mode to write file.
 * Set your <b>UNIQUE</b> log file before you use this Logger to log info.
 * </pre>
 */
class SzFileLogger extends SzAbstractLogger
{

    private $logFilePath = '/tmp';
    private $logFileName = 'szdebug.log';

    /**
     * file handling instance
     *
     * @var SzFile
     */
    private $file;

    /**
     * @see SzAbstractLogger::__construct
     */
    public function __construct($level = null, $logFilePath = null)
    {
        parent::__construct($level);

        if ($logFilePath) {
            $this->setLogFile($logFilePath);
        }

        $this->file = new SzFile();
        $this->file->openFile($this->logFilePath, $this->logFileName, SzFile::MODE_APPEND);
    }

    /**
     * @see SzAbstractLogger::doLog
     */
    protected function doLog($level, $message, $params = null)
    {
        if ($this->file) {
            if (is_object($message) || is_array($message)) {
                $message = var_export($message, true);
            }
            if (!is_null($params)) {
                $message .= ', params: ' . json_encode($params);
            }
            $this->file->writeFile($message . PHP_EOL);
        }

        return true;
    }

    /**
     * @see SzAbstractLogger::closeLog
     */
    public function closeLog()
    {
        $this->file->closeFile();
    }

    /**
     * Set log file path.
     *
     * @param string $filePath
     * @return void
     */
    public function setLogFile($filePath)
    {
        /**
         * "/tmp/debug.log"
         * =>
         * array(
         *     'tmp'
         *     'debug.log'
         * )
         */
        $info = SzUtility::explodeWithTrim('/', $filePath);

        $this->logFileName = $info[count($info) - 1]; // debug.log
        unset($info[count($info) - 1]); // array('', 'tmp')
        $this->logFilePath = '/' . implode('/', $info); // "/tmp"
    }

}
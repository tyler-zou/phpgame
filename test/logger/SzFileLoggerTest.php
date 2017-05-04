<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzFileLoggerTest extends SzTestAbstract
{

    private $logFileName;
    private $logFilePath;

    /**
     * @var SzFileLogger
     */
    protected static $instance;

    public function setUp()
    {
        $time = time();
        $this->logFileName = "utest-{$time}.log";
        $this->logFilePath = "/tmp/{$this->logFileName}";
        self::$instance = new SzFileLogger(SzAbstractLogger::DEBUG, $this->logFilePath);
    }

    /**
     * @see SzFileLogger::doLog
     */
    public function test_DoLog()
    {
        $reflector = $this->setMethodPublic('SzFileLogger', 'doLog');
        $reflector->invoke(self::$instance, SzAbstractLogger::DEBUG, 'LOG_MESSAGE');

        $fp = file($this->logFilePath);
        $this->assertEquals('LOG_MESSAGE', trim($fp[count($fp)-1]));
    }

    /**
     * @see SzFileLogger::setLogFile
     */
    public function test_SetLogFile()
    {
        self::$instance->setLogFile($this->logFilePath);

        $this->assertEquals($this->logFileName, $this->getPropertyValue('SzFileLogger', self::$instance, 'logFileName'));
        $this->assertEquals('/tmp', $this->getPropertyValue('SzFileLogger', self::$instance, 'logFilePath'));
    }

    /**
     * @see SzSysLogger::setLogLevel
     */
    public function test_SetLogLevel()
    {
        self::$instance->setLogLevel(SzAbstractLogger::ERROR);
        $this->assertEquals(SzAbstractLogger::ERROR, $this->getPropertyValue('SzFileLogger', self::$instance, 'logLevel'));
    }
}
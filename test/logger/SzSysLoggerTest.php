<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzSysLoggerTest extends SzTestAbstract
{

    /**
     * @var SzSysLogger
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzSysLogger();
    }

    /**
     * @see SzSysLogger::doLog
     */
    public function test_DoLog()
    {
        $reflector = $this->setMethodPublic('SzSysLogger', 'doLog');
        $reflector->invoke(self::$instance, LOG_DEBUG, "[" . time() . "]" . 'LOG_MESSAGE');
        $this->assertTrue(true);

        if (PHP_OS != 'WINNT') {
            $time = time();
            $this->assertEquals(
                "[{$time}]LOG_MESSAGE",
                shell_exec("tail -n 1 /var/log/syslog | grep [{$time}]")
            );
        }
    }

    /**
     * @see SzSysLogger::setLogLevel
     */
    public function test_SetLogLevel()
    {
        self::$instance->setLogLevel(LOG_ERR);
        $this->assertEquals(LOG_ERR, $this->getPropertyValue('SzSysLogger', self::$instance, 'logLevel'));
    }
}
<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzExceptionTest extends SzTestAbstract
{
    /**
     * @var SzException
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzException(10001);
    }

    /**
     * Test SzErrorHandler::getExMsg
     */
    public function test_GetExMsg()
    {
        $message = SzConfig::get()->loadFrameConfig('exception', 10001);
        $message = vsprintf($message, 'SzException');

        $this->assertEquals($message, self::$instance->getExMsg('SzException', null));
    }
}
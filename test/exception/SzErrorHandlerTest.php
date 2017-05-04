<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzErrorHandlerTest extends SzTestAbstract
{
    /**
     * @var SzErrorHandler
     */
    protected static $instance;

    public function setUp()
    {
        $class = new ReflectionClass('SzErrorHandler');
        self::$instance = $class->newInstanceWithoutConstructor();
    }

    /**
     * Test SzErrorHandler::handleError
     */
    public function test_HandleError()
    {
        $this->assertFalse(self::$instance->handleError(E_NOTICE, 'NOTICE', 'FILE', '999'));
    }

    /**
     * Test SzErrorHandler::handleException
     */
    public function test_HandleException()
    {
        $exception = new SzException(10001, 'SzErrorHandler');
        self::$instance->handleException($exception);

        $this->expectOutputString(json_encode(array('code' => 10001, 'msg' => $exception->getExMsg('SzErrorHandler', null))));
    }
}
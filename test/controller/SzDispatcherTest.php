<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/controller/mock/DispatcherMockAction.class.php';
require_once dirname(dirname(__DIR__)) . '/test/controller/mock/DispatcherErrorMockAction.class.php';

class SzDispatcherManageTest extends SzTestAbstract
{

    /**
     * @var SzDispatcher
     */
    protected static $instance;
    /**
     * @var SzRequestManager
     */
    protected static $reqManager;

    public function setUp()
    {
        self::$instance = new SzDispatcher();
        self::$reqManager = new SzRequestManager();
    }

    /**
     * @see SzDispatcher::dispatch
     */
    public function test_Dispatch()
    {
        self::$reqManager->registerRequest('Dispatcher', 'Mock', array(1, 1));
        $request = self::$reqManager->shiftRequest();

        $this->assertEquals(2, self::$instance->dispatch($request));
    }

    /**
     * @see SzDispatcher::dispatch
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10401
     */
    public function test_Dispatch_Error_10401()
    {
        self::$reqManager->registerRequest('Dispatcher', 'ErrorMock', array(1, 1));
        $request = self::$reqManager->shiftRequest();

        self::$instance->dispatch($request);
    }

    /**
     * @see SzDispatcher::getActionClassName
     */
    public function test_GetActionClassName()
    {
        self::$reqManager->registerRequest('Dispatcher', 'ErrorMock', array(1, 1));
        $request = self::$reqManager->shiftRequest();

        $reflector = $this->setMethodPublic('SzDispatcher', 'getActionClassName');
        $this->assertEquals('DispatcherErrorMockAction', $reflector->invoke(self::$instance, $request));
    }

}
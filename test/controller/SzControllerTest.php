<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzControllerTest extends SzTestAbstract
{
    /**
     * @var SzController
     */
    protected static $instance;
    /**
     * @var SzRouter
     */
    protected static $router;
    /**
     * @var SzDispatcher
     */
    protected static $dispatcher;
    /**
     * @var SzRequestManager
     */
    protected static $reqManager;
    /**
     * @var SzResponseManager
     */
    protected static $resManager;

    public function setUp()
    {
        $class = new ReflectionClass('SzController');
        self::$instance = $class->newInstanceWithoutConstructor();

        // router
        self::$router = new SzRouter();
        self::setPropertyValue('SzRouter', self::$router, 'isCompressed', false);
        // resManager
        self::$resManager = new SzResponseManager();
        self::setPropertyValue('SzResponseManager', self::$resManager, 'compress', false);
        // dispatcher
        self::$dispatcher = new SzDispatcher();
        // reqManager
        self::$reqManager = new SzRequestManager();

        self::setPropertyValue('SzController', self::$instance, 'router', self::$router);
        self::setPropertyValue('SzController', self::$instance, 'dispatcher', new SzDispatcher());
        self::setPropertyValue('SzController', self::$instance, 'reqManager', self::$reqManager);
        self::setPropertyValue('SzController', self::$instance, 'resManager', self::$resManager);
    }

    /**
     * @see SzController::process
     */
    public function test_Process()
    {
        self::$instance->process();
        $this->expectOutputString(self::$resManager->getBody()); // string in the output stream shall be the same as 'self::$resManager->getBody()'
    }

    /**
     * @see SzController::process
     */
    public function test_GetTotalRequestsCount()
    {
        self::$reqManager->registerRequest('Page', 'Index', array(1));
        $this->setPropertyValue('SzController', self::$instance, 'requestsCount', self::$reqManager->getTotalRequestsCount());
        $this->assertEquals(1, self::$instance->getTotalRequestsCount());
    }
}
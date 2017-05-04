<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/controller/mock/DispatcherMockAction.class.php';

class SzRouterTest extends SzTestAbstract
{

    /**
     * @var SzRouter
     */
    protected static $instance;

    public function setUp()
    {
        $class = new ReflectionClass('SzRouter');
        self::$instance = $class->newInstanceWithoutConstructor();
        $this->setPropertyValue('SzRouter', self::$instance, 'isCompressed', false);
    }

    /**
     * @see SzRouter::parseRawInputs
     */
    public function test_ParseRawInputs()
    {
        $str = '[["Page.Index", [1]]]';
        $_REQUEST["*"] = $str;
        $this->assertEquals(json_decode($str, true), self::$instance->parseRawInputs());
    }

    /**
     * @see SzRouter::formatRequests
     */
    public function test_FormatRequests()
    {
        $str = '[["Page.Index", [1]]]';
        $_REQUEST["*"] = $str;
        $inputs = self::$instance->parseRawInputs();

        $reqManager = new SzRequestManager();
        self::$instance->formatRequests($inputs, $reqManager);

        $this->assertEquals(new SzRequest('Page', 'Index', array(1)), $reqManager->shiftRequest());
    }
}
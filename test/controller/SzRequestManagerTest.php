<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzRequestManageTest extends SzTestAbstract
{
    /**
     * @var SzRequestManager
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzRequestManager();
    }

    /**
     * @see SzRequestManage::registerRequest
     * @see SzRequestManage::getTotalRequestsCount
     * @see SzRequestManage::shiftRequest
     */
    public function test_RegisterRequest()
    {
        self::$instance->registerRequest('Page', 'Index', array(1));
        $request = new SzRequest('Page', 'Index', array(1));

        $this->assertEquals(1, self::$instance->getTotalRequestsCount());
        $this->assertEquals($request, self::$instance->shiftRequest());
        $this->assertEquals(0, self::$instance->getTotalRequestsCount());
    }

    /**
     * @see SzRequestManage::registerRawRequest
     */
    public function test_RegisterRawRequest()
    {
        self::$instance->registerRawRequest(array('Page.Index', array(1)));
        $request = new SzRequest('Page', 'Index', array(1));

        $this->assertEquals($request, self::$instance->shiftRequest());
    }

    /**
     * @see SzRequestManage::registerRawRequest
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10400
     */
    public function test_RegisterRawRequest_Error_10400()
    {
        self::$instance->registerRawRequest(array());
    }

    /**
     * @see SzRequestManage::addRequest
     */
    public function test_AddRequest()
    {
        $request = new SzRequest('Page', 'Index', array(1));
        self::$instance->addRequest($request);

        $this->assertEquals($request, self::$instance->shiftRequest());
    }
}
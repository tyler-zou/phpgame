<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzRequestTest extends SzTestAbstract
{

    /**
     * @var SzRequest
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzRequest('Page', 'Index', array(1));
    }

    /**
     * @see SzRequest::getFunction
     */
    public function test_GetFunction()
    {
        $this->assertEquals('Page', self::$instance->getFunction());
    }

    /**
     * @see SzRequest::getAction
     */
    public function test_GetAction()
    {
        $this->assertEquals('Index', self::$instance->getAction());
    }

    /**
     * @see SzRequest::getParams
     */
    public function test_GetParams()
    {
        $this->assertEquals(array(1), self::$instance->getParams());
    }
}
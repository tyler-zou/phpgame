<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/controller/mock/SzAbstractActionMock.class.php';

class SzAbstractActionTest extends SzTestAbstract
{
    /**
     * @var SzAbstractAction
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzAbstractActionMock();
    }

    /**
     * @see SzAbstractAction::validateParams
     */
    public function test_ValidateParams()
    {
        $reqParams= array(1, 1);
        self::$instance->validateParams($reqParams);
        $this->assertTrue(true);
    }

    /**
     * @see SzAbstractAction::validateParams
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10404
     */
    public function test_ValidateParams_Error_10404()
    {
        $reqParams = array(1);
        self::$instance->validateParams($reqParams);
    }

    /**
     * @see SzAbstractAction::validateParams
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10218
     */
    public function test_ValidateParams_Error_10218()
    {
        $reqParams= array('A', 1);
        self::$instance->validateParams($reqParams);
    }
}
<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzAutoloadTest extends SzTestAbstract
{
    /**
     * @var SzAutoload
     */
    protected static $instance;

    public function setUp()
    {
        $class = new ReflectionClass('SzAutoload');
        self::$instance = $class->newInstanceWithoutConstructor();
    }

    /**
     * @see SzAutoload::autoload
     */
    public function test_Autoload()
    {
        $frameAutoloads = array(
            'SzAutoloadMock' => 'test/autoload/mock/SzAutoloadMock.class.php'
        );

        self::setPropertyValue('SzAutoload', self::$instance, 'frameAutoloads', $frameAutoloads);
        self::$instance->autoload('SzAutoloadMock');

        $this->assertTrue(class_exists('SzAutoloadMock', true));
    }

    /**
     * @see SzAutoload::autoload
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10002
     */
    public function test_Autoload_Path_Error_10002()
    {
        $frameAutoloads_Error = array(
            'SzAutoloadMock' => 'test/autoload/mock/SzAutoloadMock_Error.class.php'
        );

        self::setPropertyValue('SzAutoload', self::$instance, 'frameAutoloads', $frameAutoloads_Error);
        self::$instance->autoload('SzAutoloadMock');
    }
}
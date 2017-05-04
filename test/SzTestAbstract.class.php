<?php
abstract class SzTestAbstract extends PHPUnit_Framework_TestCase
{
    protected static $instance;

    /**
     * Initialize the framework before all the tests.
     *
     * @see PHPUnit_Framework_TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass()
    {
        $sysFile = dirname(__DIR__) . '/SzSystem.class.php';
        if (!in_array($sysFile, get_included_files())) {
            require_once $sysFile;
            SzSystem::init(dirname(__DIR__) . '/build/create', '');
        }
    }

    protected static function setPropertyValue($className, $instance, $propertyName, $value)
    {
        $reflector = new ReflectionProperty($className, $propertyName);
        $reflector->setAccessible(true);
        $reflector->setValue($instance, $value);
    }

    protected static function getPropertyValue($className, $instance, $propertyName)
    {
        $reflector = new ReflectionProperty($className, $propertyName);
        $reflector->setAccessible(true);
        return $reflector->getValue($instance);
    }

    protected static function setMethodPublic($className, $methodName)
    {
        $reflector = new ReflectionMethod($className, $methodName);
        $reflector->setAccessible(true);
        return $reflector;
    }

    /**
     * Redis Cache Mock
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function mockRedisCache()
    {
        $stub = $this->getMock('SzRedisCache', array('connent'), array(true));
        $stub->expects($this->any())
            ->method('connent')
            ->with($this->equalTo(true))
            ->will($this->returnValue(true));

        return $stub;
    }

}
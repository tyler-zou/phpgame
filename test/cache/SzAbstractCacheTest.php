<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/cache/mock/SzAbstractCacheMock.class.php';

class SzAbstractCacheTest extends SzTestAbstract
{
    /**
     * @var SzAbstractCacheMock
     */
    protected static $instance;

    public function setUp()
    {
        $class = new ReflectionClass('SzAbstractCacheMock');
        self::$instance = $class->newInstanceWithoutConstructor();
    }

    /**
     * @see SzAbstractCache::genExpire
     */
    public function test_GenExpire()
    {
        $this->assertTrue(is_int(self::$instance->genExpire()));
    }
}
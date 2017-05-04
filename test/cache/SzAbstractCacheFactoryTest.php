<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/cache/mock/SzAbstractCacheFactoryMock.class.php';

class SzAbstractCacheFactoryTest extends SzTestAbstract
{
    /**
     * @var SzAbstractCacheFactory
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzAbstractCacheFactoryMock();

        self::setPropertyValue('SzAbstractCacheFactoryMock', self::$instance, 'secureCacheEnabled', false);
        self::$instance->AppRedisServers = array(array('127.0.0.1', '6370'), array('127.0.0.1', '6371'));
        self::$instance->AppRedisServerCount = 2;
        self::$instance->StaticRedisServers = array(array('127.0.0.1', '6370'), array('127.0.0.1', '6371'));
        self::$instance->StaticRedisServerCount = 2;
    }

    /**
     * @see SzAbstractCacheFactory::getAppCache
     */
    public function test_GetAppCache()
    {
        $this->assertTrue(self::$instance->getAppCache() instanceof SzRedisCache);
    }

    /**
     * @see SzAbstractCacheFactory::getStaticCache
     */
    public function test_GetStaticCache()
    {
        $this->assertTrue(self::$instance->getStaticCache() instanceof SzRedisCache);
    }

    /**
     * @see SzAbstractCacheFactory::groupShardKeys
     */
    public function test_GroupShardKeys()
    {
        $group = self::$instance->groupShardKeys(array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
        $server1Keys = array(6, 7);
        $server2Keys = array(1, 2, 3, 4, 5, 8, 9, 10);
        $this->assertEquals($group[0], $server1Keys);
        $this->assertEquals($group[1], $server2Keys);
        $this->assertEquals(self::$instance->AppRedisServerCount, count($group));
    }

    /**
     * @see SzAbstractCacheFactory::getCacheInstance
     */
    public function test_GetCacheInstance()
    {
        $shardKey = 60;
        $reflector = $this->setMethodPublic('SzAbstractCacheFactoryMock', 'getCacheInstance');
        /* SzRedisCacheMock */
        $result = $reflector->invoke(self::$instance, SzAbstractCacheFactory::SERVER_TYPE_APP, SzAbstractDb::DB_TYPE_REDIS, $shardKey);

        $this->assertTrue($result instanceof SzRedisCache);
        // function connect of class SzRedisCacheMock has been overwrote, return the $config directly, so the $handle shall be the config array
        $this->assertEquals(array('127.0.0.1', '6371'), $this->getPropertyValue('SzRedisCache', $result, 'handle'));
    }

    /**
     * @see SzAbstractCacheFactory::buildServerCountKey
     */
    public function test_BuildServerCountKey()
    {
        $reflector = $this->setMethodPublic('SzAbstractCacheFactoryMock', 'buildServerCountKey');
        $this->assertEquals('AppRedisServerCount', $reflector->invoke(self::$instance));
    }

    /**
     * @see SzAbstractCacheFactory::buildServersConfigKey
     */
    public function test_BuildServersConfigKey()
    {
        $reflector = $this->setMethodPublic('SzAbstractCacheFactoryMock', 'buildServersConfigKey');
        $this->assertEquals('AppRedisServers', $reflector->invoke(self::$instance));
    }
}
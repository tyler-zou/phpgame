<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/cache/SzAbstractCacheFactory.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/test/cache/mock/SzRedisCacheMock.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/test/cache/mock/SzMemcachedCacheMock.class.php';

class SzAbstractCacheFactoryMock extends SzAbstractCacheFactory
{
    protected function getRedisCache($config)
    {
        return new SzRedisCacheMock($config);
    }

    protected function getMemcachedCache($config)
    {
        return new SzMemcachedCacheMock($config);
    }
}
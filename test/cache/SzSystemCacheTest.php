<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzSystemCacheTest extends SzTestAbstract
{
    /**
     * @see SzSystemCache::cache
     */
    public function test_Cache()
    {
        $cacheType     = 'CACHE_TYPE';
        $cacheKey      = 'CACHE_KEY';
        $cacheValue    = 'CACHE_VALUE';

        SzSystemCache::cache($cacheType, $cacheKey, $cacheValue);
        $this->assertEquals($cacheValue, SzSystemCache::cache($cacheType, $cacheKey));

        $cacheKeyError = 'CACHE_KEY_ERROR';
        $this->assertFalse(SzSystemCache::cache($cacheType, $cacheKeyError));
    }

}
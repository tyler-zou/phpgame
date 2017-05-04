<?php
abstract class SzAbstractCacheFactory
{

    const SERVER_COUNT_KEY  = '%s%sServerCount'; // e.g "AppMemcached"ServerCount
    const SERVER_CONFIG_KEY = '%s%sServers';     // e.g "StaticRedis"Servers

    const SERVER_TYPE_APP = 'App';
    const SERVER_TYPE_STATIC = 'Static';

    public static $VALID_SERVER_TYPES = array(
        self::SERVER_TYPE_APP, self::SERVER_TYPE_STATIC
    );

    /**
     * @var SzCacheFactory
     */
    protected static $instance;

    /**
     * is secure cache enabled
     *
     * @var boolean
     */
    protected $secureCacheEnabled;

    /**
     * cache type
     *
     * @var boolean
     */
    protected $cacheType;

    /**
     * Initialize SzAbstractCacheFactory.
     *
     * <pre>
     * Configs of file "app/cache.config.php" would be cached in this class:
     * For those keys in SzAbstractCache::$VALID_CACHE_TYPES, there are four keys generated & cached:
     *     "{App|Static}{CACHE_TYPE}"ServerCount: total count of servers for cache of "{App|Static}{CACHE_TYPE}"
     *     "{App|Static}{CACHE_TYPE}"Servers: server configs for cache of "{App|Static}{CACHE_TYPE}"
     * </pre>
     *
     * @return SzAbstractCacheFactory
     */
    public function __construct()
    {
        $configLoader = SzConfig::get();

        foreach (SzAbstractCache::$VALID_CACHE_TYPES as $cacheType) {
            foreach (self::$VALID_SERVER_TYPES as $serverType) {
                $configKey = $serverType . '_' . $cacheType; // App_Redis, Static_Memcached, ...
                $servers = $configLoader->loadAppConfig('cache', $configKey);

                $serverCountKey = $this->buildServerCountKey($serverType, $cacheType);
                $serversConfigKey = $this->buildServersConfigKey($serverType, $cacheType);

                $this->$serverCountKey = count($servers);
                $this->$serversConfigKey = $servers;
            }
        }
        $this->cacheType = $configLoader->loadAppConfig('cache', 'CACHE_TYPE');
        $this->secureCacheEnabled = $configLoader->loadAppConfig('cache', 'SECURE_CACHE_ENABLED');

        unset($configLoader);
    }

    /**
     * Get app cache, data stored may be lost for "LRU".
     *
     * @param string $shardKey default null, means use the first cache shard
     * @param string $type default null, means "Redis"
     * @return SzAbstractCache
     */
    public function getAppCache($shardKey = null, $type = null)
    {
        return $this->getCacheInstance(self::SERVER_TYPE_APP, $type, $shardKey);
    }

    /**
     * Get static cache, data stored shall never be lost.
     *
     * @param string $shardKey default null, means use the first cache shard
     * @param string $type default null, means "Redis"
     * @return SzAbstractCache
     */
    public function getStaticCache($shardKey = null, $type = null)
    {
        $instance = null;

        if (!$this->secureCacheEnabled) {
            $instance = $this->getAppCache($shardKey, $type);
        } else {
            $instance = $this->getCacheInstance(self::SERVER_TYPE_STATIC, $type, $shardKey);
        }

        return $instance;
    }

    /**
     * Group given shard keys into shard groups.
     *
     * <pre>
     * array(shardKey, shardKey, ...)
     * =>
     * array(
     *     shardId => array(shardKey, shardKey, ...),
     *     ...
     * )
     * </pre>
     *
     * @param array $shardKeys
     * @param string $cacheType default null, means "Redis"
     * @return array
     */
    public function groupShardKeys($shardKeys, $cacheType = null)
    {
        $serverCountKey = $this->buildServerCountKey(self::SERVER_TYPE_APP, $cacheType);
        $serverCount = $this->$serverCountKey;

        $shardId = 0;
        $groupKeys = array();
        foreach ($shardKeys as $shardKey) {
            if (!is_null($shardKey) && $serverCount != 1
                && $cacheType != SzAbstractCache::CACHE_TYPE_MEMCACHED // memcached has it's own shard logic, no need to do it here
            ) {
                $shardId = SzUtility::consistentHash($shardKey, $serverCount);
            }

            if (!SzUtility::checkArrayKey($shardId, $groupKeys)) {
                $groupKeys[$shardId] = array();
            }

            $groupKeys[$shardId][] = $shardKey;
        }

        return $groupKeys;
    }

    /**
     * Get the cache class instance.
     *
     * @param string $serverType refer to SzAbstractCacheFactory::SERVER_TYPE_*
     * @param string $cacheType refer to SzAbstractCache::CACHE_TYPE_*
     * @param string $shardKey null given, means use the first cache shard
     * @throws SzException 10701, 10703
     * @return SzAbstractCache
     */
    protected function getCacheInstance($serverType, $cacheType, $shardKey)
    {
        if (is_null($cacheType)) {
            $cacheType = $this->cacheType;
        }
        if (!in_array($cacheType, SzAbstractCache::$VALID_CACHE_TYPES)) {
            throw new SzException(10701, $cacheType);
        }
        if (!in_array($serverType, self::$VALID_SERVER_TYPES)) {
            throw new SzException(10703, $serverType);
        }

        $shardId = 0;

        $serverCountKey = $this->buildServerCountKey($serverType, $cacheType);
        $serverConfigKey = $this->buildServersConfigKey($serverType, $cacheType);

        $serverCount = $this->$serverCountKey;
        $serverConfig = $this->$serverConfigKey;

        if (!is_null($shardKey) && $serverCount != 1
            && $cacheType != SzAbstractCache::CACHE_TYPE_MEMCACHED // memcached has it's own shard logic, no need to do it here
        ) {
            $shardId = SzUtility::consistentHash($shardKey, $serverCount);
        }

        $instanceKey = sprintf(SzSystemCache::CACHE_CLASS_INSTANCE, $serverType, $cacheType);
        $instance = SzSystemCache::cache($instanceKey, $shardId);
        if (!$instance) {
            switch ($cacheType) {
                case SzAbstractCache::CACHE_TYPE_REDIS:
                    $instance = $this->getRedisCache($serverConfig[$shardId]);
                    break;
                case SzAbstractCache::CACHE_TYPE_MEMCACHED:
                    $instance = $this->getMemcachedCache($serverConfig);
                    break;
                default:
                    throw new SzException(10701, $cacheType);
                    break;
            }
            SzSystemCache::cache($instanceKey, $shardId, $instance);
        }

        return $instance;
    }

    /**
     * Initialize Redis Cache
     *
     * @param $config
     * @return SzRedisCache
     */
    protected function getRedisCache($config)
    {
        return new SzRedisCache($config);
    }

    /**
     * Initialize Memcached Cache
     *
     * @param $config
     * @return SzMemcachedCache
     */
    protected function getMemcachedCache($config)
    {
        return new SzMemcachedCache($config);
    }

    /**
     * Build the cache server count key.
     * <pre>
     * e.g
     *     $serverType = 'App'
     *     $cacheType = 'Redis'
     *         => "AppRedisServerCount"
     *     $serverType = null, menas default type, is "App"
     *     $cacheType = null, means default type, is "Redis"
     * </pre>
     *
     * @param string $serverType default null, refer to SzAbstractCacheFactory::SERVER_TYPE_*
     * @param string $cacheType default null, refer to SzAbstractCache::CACHE_TYPE_*
     * @return string
     */
    protected function buildServerCountKey($serverType = null, $cacheType = null)
    {
        if (is_null($serverType)) {
            $serverType = self::SERVER_TYPE_APP;
        }
        if (is_null($cacheType)) {
            $cacheType = SzAbstractCache::CACHE_TYPE_REDIS;
        }
        return sprintf(self::SERVER_COUNT_KEY, $serverType, $cacheType);
    }

    /**
     * Build the cache server config key.
     * <pre>
     * e.g
     *     $serverType = 'App'
     *     $cacheType = 'Redis'
     *         => "AppRedisServers"
     *     $serverType = null, menas default type, is "App"
     *     $cacheType = null, means default type, is "Redis"
     * </pre>
     *
     * @param string $serverType default null, refer to SzAbstractCacheFactory::SERVER_TYPE_*
     * @param string $cacheType default null, refer to SzAbstractCache::CACHE_TYPE_*
     * @return string
     */
    protected function buildServersConfigKey($serverType = null, $cacheType = null)
    {
        if (is_null($serverType)) {
            $serverType = self::SERVER_TYPE_APP;
        }
        if (is_null($cacheType)) {
            $cacheType = $this->cacheType;
        }
        return sprintf(self::SERVER_CONFIG_KEY, $serverType, $cacheType);
    }
}
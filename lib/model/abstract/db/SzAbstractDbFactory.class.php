<?php
abstract class SzAbstractDbFactory
{

    const SHARD_TYPE_FIXED   = 'Fixed';   // Sz"Fixed"DbFactory
    const SHARD_TYPE_DYNAMIC = 'Dynamic'; // Sz"Dynamic"DbFactory

    const SERVER_COUNT_KEY  = '%sServerCount'; // e.g "MySql"ServerCount
    const SERVER_CONFIG_KEY = '%sServers';     // e.g "Redis"Servers

    /**
     * used on config key in "database.config.php",
     * also on cache key in SzSystemCache with type SzSystemCache::MODEL_DB_INSTANCE
     *
     * @var string
     */
    const PAYMENT_KEY = 'Payment';

    /**
     * payment server config
     *
     * @var array
     */
    protected $paymentServer;

    /**
     * Initialize SzAbstractDbFactory.
     *
     * <pre>
     * Configs of file "app/database.config.php" would be cached in this class:
     * For those keys in SzAbstractDb::$VALID_DB_TYPES, there are two keys generated & cached:
     *     "DB_TYPE"ServerCount: total count of servers for databases of "DB_TYPE"
     *     "DB_TYPE"Servers: server configs for databases of "DB_TYPE"
     * </pre>
     *
     * @return SzAbstractDbFactory
     */
    public function __construct()
    {
        $configs = SzConfig::get()->loadAppConfig('database');

        foreach ($configs as $key => $config) {
            if (in_array($key, SzAbstractDb::$VALID_DB_TYPES)) {
                // current $key is the db type, e.g "MySql"
                $serverCountKey = $this->buildServerCountKey($key);     // "MySqlServerCount"
                $serversConfigKey = $this->buildServersConfigKey($key); // "MySqlServers"
                $this->$serverCountKey = count($config);                // $this->MySqlServerCount
                $this->$serversConfigKey = $config;                     // $this->MySqlServers
            }
        }

        $this->paymentServer = SzConfig::get()->loadAppConfig('database', self::PAYMENT_KEY);
    }

    /**
     * Get db instance to process query.
     *
     * @param string $shardKey default null, means use the first db shard
     * @param string $type database type, default null, means type "MySql"
     * @return SzMySqlDb|SzRedisDb
     */
    public function getDb($shardKey = null, $type = null)
    {
        return $this->getDbInstance($shardKey, $type);
    }

    /**
     * Get payment db instance to process query.
     *
     * @throws SzException 10506
     * @return SzMySqlDb
     */
    public function getPaymentDb()
    {
        $instance = SzSystemCache::cache(sprintf(SzSystemCache::MODEL_DB_INSTANCE, self::PAYMENT_KEY), self::PAYMENT_KEY);
        if (!$instance) {
            $instance = new SzMySqlDb($this->paymentServer);
            SzSystemCache::cache(sprintf(SzSystemCache::MODEL_DB_INSTANCE, self::PAYMENT_KEY), self::PAYMENT_KEY, $instance);
        }
        return $instance;
    }

    /**
     * Get default shard mechanism db instance.
     *
     * @param string $shardKey default null, means use the first db shard
     * @param string $type database type, default null, means type "MySql"
     * @throws SzException 10506
     * @return SzMySqlDb|SzRedisDb
     */
    protected function getDbInstance($shardKey = null, $type = null)
    {
        if (is_null($type)) {
            $type = SzAbstractDb::DB_TYPE_MYSQL;
        }
        if (!in_array($type, SzAbstractDb::$VALID_DB_TYPES)) {
            throw new SzException(10506, $type);
        }

        $shardId = 0;

        $serverCountKey = $this->buildServerCountKey($type);

//        file_put_contents('/tmp/aa.txt', $shardKey.PHP_EOL, FILE_APPEND);

        if (!is_null($shardKey) && $this->$serverCountKey != 1) {
            $shardId = $this->buildShardId($shardKey, $type);
        }

        $instanceKey = sprintf(SzSystemCache::MODEL_DB_INSTANCE, $type);
        $instance = SzSystemCache::cache($instanceKey, $shardId);
        if (!$instance) {
            $serversConfigKey = $this->buildServersConfigKey($type);
            $serversConfig = $this->$serversConfigKey;
            switch ($type) {
                case SzAbstractDb::DB_TYPE_MYSQL:
                    $instance = $this->getMySqlDb($serversConfig[$shardId]);
                    break;
                case SzAbstractDb::DB_TYPE_REDIS:
                    $instance = $this->getRedisDb($serversConfig[$shardId]);
                    break;
                default:
                    throw new SzException(10506, $type);
                    break;
            }
            SzSystemCache::cache($instanceKey, $shardId, $instance);
        }

        return $instance;
    }

    /**
     * Initialize MySql Db
     *
     * @param $config
     * @return SzMemcachedCache
     */
    protected function getMySqlDb($config)
    {
        return new SzMySqlDb($config);
    }

    /**
     * Initialize Redis Db
     *
     * @param $config
     * @return SzRedisCache
     */
    protected function getRedisDb($config)
    {
        return new SzRedisDb($config);
    }

    /**
     * Build database $shardId according to $shardKey.
     *
     * <pre>
     * The implementation of this function is the <b>KEY</b> difference between several
     * kind of shard strategies. <br/>
     *
     * To know how many strategies can be used, please refer to SzAbstractDbFactory::SHARD_TYPE_* <br/>
     *
     * <b>SHARD_TYPE_FIX:</b>
     * $shardId is the result of consistent hash result of $shardKey & $serverCount. <br/>
     *
     * <b>SHARD_TYPE_DYNAMIC:</b>
     * $shardId is the result from dynamic shard mapping table in redis, which generated in PHP logic code.
     *
     * </pre>
     *
     * @param string $shardKey
     * @param string $dbType
     * @return int $shardId
     */
    protected abstract function buildShardId($shardKey, $dbType);

    /**
     * Build the db server count key.
     * <pre>
     * e.g
     *     $dbType = 'MySql'
     *         => "MySqlServerCount"
     *     $dbType = null, means default type, is "MySql"
     * </pre>
     *
     * @param string $dbType default null
     * @return string
     */
    protected function buildServerCountKey($dbType = null)
    {
        if (is_null($dbType)) {
            $dbType = SzAbstractDb::DB_TYPE_MYSQL;
        }
        return sprintf(self::SERVER_COUNT_KEY, $dbType);
    }

    /**
     * Build the db server config key.
     * <pre>
     * e.g
     *     $dbType = 'MySql'
     *         => "MySqlServers"
     *     $dbType = null, means default type, is "MySql"
     * </pre>
     *
     * @param string $dbType default null
     * @return string
     */
    protected function buildServersConfigKey($dbType = null)
    {
        if (is_null($dbType)) {
            $dbType = SzAbstractDb::DB_TYPE_MYSQL;
        }
        return sprintf(self::SERVER_CONFIG_KEY, $dbType);
    }

}
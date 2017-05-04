<?php
class SzDynamicShardDbFactory extends SzAbstractDbFactory
{

    const SHARD_MAPPING_KEY = 'SHARDIDS:%s:%d';  // "SHARDIDS:MySql:0"
    const SHARD_CONFIG_KEY  = 'SHARD_WEIGHT_%s'; // "SHARD_WEIGHT_MySql"
    /**
     * how many $shardKey stored in one redis hash piece
     *
     * <pre>
     * SHARD_ESTIMATE_REGISTER_USER_COUNT = 5000000 (app/database.config.php)
     * SHARD_ITEM_COUNT_PER_PIECE = 1000 <br/>
     *
     * There shall be 5000 pieces (5000000 / 1000) of hashes in redis
     * </pre>
     *
     * @var int
     */
    const SHARD_ITEM_COUNT_PER_PIECE = 1000;

    /**
     * redis instance of shardId 0 of the "Redis" configs in app/database.config.php
     *
     * @var SzRedisDb
     */
    protected $redis;

    /**
     * result of
     * ceil(SHARD_ESTIMATE_REGISTER_USER_COUNT / SHARD_ITEM_COUNT_PER_PIECE)
     *
     * @var int
     */
    protected $totalHashesCount;

    /**
     * @see SzAbstractDbFactory::__construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->totalHashesCount = ceil(
            SzConfig::get()->loadAppConfig('database', 'SHARD_ESTIMATE_REGISTER_USER_COUNT') / self::SHARD_ITEM_COUNT_PER_PIECE
        );
    }

    /**
     * @see SzAbstractDbFactory::buildShardId
     */
    protected function buildShardId($shardKey, $dbType)
    {
        $shardId = $this->getShardId($shardKey, $dbType);
        if (false === $shardId) {
            $shardId = $this->generateShardId($shardKey, $dbType);
        }
        return $shardId;
    }

    /**
     * Get record in $shardId mapping table in redis.
     *
     * @param string $shardKey
     * @param string $dbType
     * @return int
     */
    protected function getShardId($shardKey, $dbType)
    {
        return $this->getRedisDb()->hGet($this->getMappingKey($shardKey, $dbType), $shardKey);
    }

    /**
     * Add record in $shardId mapping table in redis.
     *
     * @param string $shardKey
     * @param string $dbType
     * @return int
     */
    protected function generateShardId($shardKey, $dbType)
    {
        $configKey = sprintf(self::SHARD_CONFIG_KEY, $dbType);
        $configs = SzConfig::get()->loadAppConfig('database', $configKey);
        $shardId = SzUtility::getRandomElementByProbability($configs);

        $this->getRedisDb()->hSet($this->getMappingKey($shardKey, $dbType), $shardKey, $shardId);
        return $shardId;
    }

    /**
     * Get $shardId mapping hash key.
     *
     * <pre>
     * SHARD_MAPPING_KEY
     *     $dbType
     *     ceil(SHARD_ESTIMATE_REGISTER_USER_COUNT / SHARD_ITEM_COUNT_PER_PIECE)
     * </pre>
     *
     * @param string $shardKey
     * @param string $dbType
     * @return string
     */
    protected function getMappingKey($shardKey, $dbType)
    {
        return sprintf(self::SHARD_MAPPING_KEY, $dbType, SzUtility::consistentHash($shardKey, $this->totalHashesCount));
    }

    /**
     * Get redis db handle.
     *
     * @return SzRedisDb
     */
    protected function getRedisDb()
    {
        if (!$this->redis) {
            $this->redis = SzContextFactory::get()->getDb(null, SzAbstractDb::DB_TYPE_REDIS);
        }
        return $this->redis;
    }

}
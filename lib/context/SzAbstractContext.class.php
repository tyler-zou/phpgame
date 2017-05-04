<?php
abstract class SzAbstractContext
{

    /**
     * Get db instance to process query.
     *
     * @param string $shardKey default null, means use the first db shard
     * @param string $type database type, default null, means type "MySql"
     * @return SzAbstractDb
     */
    abstract public function getDb($shardKey = null, $type = null);

    /**
     * Get payment db instance to process query.
     *
     * @return SzMySqlDb
     */
    abstract public function getPaymentDb();

    /**
     * Get query builder instance to build query.
     *
     * @return SzDbQueryBuilder
     */
    abstract public function getQueryBuilder();

    /**
     * Get app cache, data stored may be lost for "LRU".
     *
     * @param string $shardKey default null, means use the first cache shard
     * @return SzAbstractCache
     */
    abstract public function getAppCache($shardKey = null);

    /**
     * Get static cache, data stored shall never be lost.
     *
     * @param string $shardKey default null, means use the first cache shard
     * @return SzAbstractCache
     */
    abstract public function getStaticCache($shardKey = null);

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
     * @param array $shardKeys array(shardKey, ...)
     * @param string $type default null, means "Redis"
     * @return array
     */
    abstract public function groupShardKeys($shardKeys, $type = null);

}
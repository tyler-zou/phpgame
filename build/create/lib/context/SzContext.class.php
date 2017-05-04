<?php
class SzContext extends SzAbstractContext
{

    /**
     * @see SzAbstractContext::getDb
     */
    public function getDb($shardKey = null, $type = null)
    {
        return SzDbFactory::get()->getDb($shardKey, $type);
    }

    /**
     * @see SzAbstractContext::getPaymentDb
     */
    public function getPaymentDb()
    {
        return SzDbFactory::get()->getPaymentDb();
    }

    /**
     * @see SzAbstractContext::getQueryBuilder
     */
    public function getQueryBuilder()
    {
        return SzDbQueryBuilder::get();
    }

    /**
     * @see SzAbstractContext::getAppCache
     */
    public function getAppCache($shardKey = null)
    {
        return SzCacheFactory::get()->getAppCache($shardKey);
    }

    /**
     * @see SzAbstractContext::getStaticCache
     */
    public function getStaticCache($shardKey = null)
    {
        return SzCacheFactory::get()->getStaticCache($shardKey);
    }

    /**
     * @see SzAbstractContext::groupShardKeys
     */
    public function groupShardKeys($shardKeys, $type = null)
    {
        return SzCacheFactory::get()->groupShardKeys($shardKeys, $type);
    }

}
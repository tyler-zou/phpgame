<?php
class SzFixedShardDbFactory extends SzAbstractDbFactory
{

    /**
     * @see SzAbstractDbFactory::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see SzAbstractDbFactory::buildShardId
     */
    protected function buildShardId($shardKey, $dbType)
    {
        $serverCountKey = $this->buildServerCountKey($dbType);

        return SzUtility::consistentHash($shardKey, $this->$serverCountKey);
    }

}
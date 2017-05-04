<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/abstract/db/SzAbstractDbFactory.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/test/model/mock/SzMySqlDbMock.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/test/model/mock/SzRedisDbMock.class.php';

class SzAbstractDbFactoryMock extends SzAbstractDbFactory
{
    protected function buildShardId($shardKey, $dbType)
    {
        $serverCountKey = $this->buildServerCountKey($dbType);
        return SzUtility::consistentHash($shardKey, $this->$serverCountKey);
    }

    protected function getMySqlDb($config)
    {
        return new SzMySqlDbMock($config);
    }

    protected function getRedisDb($config)
    {
        return new SzRedisDbMock($config);
    }
}
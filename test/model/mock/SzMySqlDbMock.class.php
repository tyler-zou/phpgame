<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/abstract/db/SzAbstractDb.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/implements/db/SzMySqlDb.class.php';

class SzMySqlDbMock extends SzMySqlDb
{
    protected function connect($host, $port, $userName = null, $password = null, $dbName = null)
    {
        return true;
    }

    public function select($sql)
    {
        return array(array('itemId' => 1, 'userId' => 60, 'itemDefId' => 10001, 'type' => 1, 'count' => 1, 'expireTime' => 0, 'updateTime' => 0));
    }

    public function execute($sql)
    {
        return $sql;
    }

    public function beginTransaction()
    {
        return true;
    }

    public function rollbackTransaction()
    {
        return true;
    }

    public function commitTransaction()
    {
        return true;
    }
}
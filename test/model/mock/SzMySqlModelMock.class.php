<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/abstract/model/SzAbstractModel.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/implements/model/SzMySqlModel.class.php';

class SzMySqlModelMock extends SzMySqlModel
{
    /**
     * @see SzAbstractModel::$ORM_NAME
     * @var string
     */
    public static $ORM_NAME = 'Item';

    /**
     * Initialize the ItemModel.
     */
    public function __construct()
    {
        $this->table = 'item';
        $this->columns = array('itemId', 'userId', 'itemDefId', 'type', 'count', 'expireTime', 'updateTime', );
        $this->autoIncrColumn = 0;
        $this->diffUpColumns = array(4, );
        $this->updateFilter = array(0, 1, 2, 3, );
        $this->toArrayFilter = array();
        $this->searchColumns = array(1, );
        $this->updateColumns = array(0, );
        $this->jsonColumns = array();
        $this->cacheColumn = 1;
        $this->shardColumn = 1;
        $this->pkColumn = 0;
        $this->deleteColumns = array(0, );
        $this->cacheTime = null;

        $this->ormName = 'Item';
        $this->columnCount = 7;
        $this->isList = true;
        $this->dbType = 'MySql';
        $this->voClassName = 'ItemVo';
        $this->voListClassName = 'ItemVoList';

        parent::__construct();
    }

    protected function prepareQueryHandles($shardKey)
    {
        $dbHandle = null;
        $queryBuilder = null;

        $dbFactory = new SzAbstractDbFactoryMock();

        $dbHandle = $dbFactory->getDb($shardKey, $this->getDbType());
        $queryBuilder = $this->context->getQueryBuilder();

        return array($dbHandle, $queryBuilder);
    }
}
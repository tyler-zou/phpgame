<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/model/mock/SzMySqlModelMock.class.php';

class SzMySqlModelTest extends SzTestAbstract
{
    /**
     * @var SzMySqlModelMock
     */
    protected static $instance;

    protected static $userId    = 60;
    protected static $itemDefId = 10001;

    public function setUp()
    {
        self::$instance = new SzMySqlModelMock();
    }

    /**
     * @see SzAbstractModel::insert
     */
    public function test_Insert()
    {
        $itemVo = new ItemVo(null, self::$userId, self::$itemDefId, 1, 1, 0, 0);
        $this->assertEquals(
            'INSERT INTO `frame1`.`item` (`itemId`, `userId`, `itemDefId`, `type`, `count`, `expireTime`, `updateTime`) VALUES (NULL, ' . self::$userId . ', ' . self::$itemDefId . ', 1, 1, 0, 0);',
            self::$instance->insert($itemVo)
        );
    }

    /**
     * @see SzAbstractModel::retrieve
     */
    public function test_Retrieve()
    {
        $shardVal = 60;
        $cacheVal = 60;
        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList->getList());
        $this->assertEquals(self::$itemDefId, $itemVo->getItemDefId());
    }

    /**
     * @see SzAbstractModel::select
     */
    public function test_Select()
    {
        $args = array(
            1 => self::$userId,
            2 => self::$itemDefId,
        );

        $itemList = self::$instance->select($args);
        $itemVo = array_pop($itemList->getList());
        $this->assertEquals(self::$itemDefId, $itemVo->getItemDefId());
    }

    /**
     * @see SzAbstractModel::update
     */
    public function test_update()
    {
        $shardVal = self::$userId;
        $cacheVal = self::$userId;

        /**
         * @var ItemVoList $itemList
         * @var ItemVo $itemVo
         */
        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList->getList());

        $itemVo->setCount(100);
        $itemVo->setExpireTime(1000000000);

        $this->assertEquals(
            'UPDATE `frame1`.`item` SET `count` = `count` + 99, `expireTime` = 1000000000 WHERE `itemId` = 1;',
            self::$instance->update($itemVo)
        );
    }

    /**
     * @see SzAbstractModel::prepareUpdate
     */
    public function test_PrepareUpdate()
    {
        $shardVal = self::$userId;
        $cacheVal = self::$userId;

        /**
         * @var ItemVoList $itemList
         * @var ItemVo $itemVo
         */
        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList->getList());

        $itemVo->setCount(100);
        $itemVo->setExpireTime(1000000000);

        $result = array(
            'columns' => array(4 => 'count', 5 => 'expireTime'),
            'values'  => array(4 => 99, 5 => 1000000000),
            'conds'   => array(4 => 'SET_SELF_PLUS'),
        );

        $reflector = $this->setMethodPublic('SzMySqlModel', 'prepareUpdate');
        $this->assertEquals($result, $reflector->invoke(self::$instance, $itemVo));
    }

    /**
     * @see SzAbstractModel::convertAssocArrayToVo
     */
    public function test_ConvertAssocArrayToVo()
    {
        $itemArray = array(
            'itemId' => 999999,
            'userId' => self::$userId,
            'itemDefId' => self::$itemDefId,
            'type' => 1,
            'count' => 1,
            'expireTime' => 0,
            'updateTime' => 0,
        );

        /**
         * @var ItemVo $itemVo
         */
        $reflector = $this->setMethodPublic('SzMySqlModel', 'convertAssocArrayToVo');
        $itemVo = $reflector->invoke(self::$instance, $itemArray);
        $this->assertEquals(999999, $itemVo->getItemId());

        $reflector = $this->setMethodPublic('SzMySqlModel', 'convertVoToAssocArray');
        $itemArray = $reflector->invoke(self::$instance, $itemVo);
        $this->assertEquals(999999, $itemArray['itemId']);
    }

    /**
     * @see SzAbstractModel::getShardKeyInArgs
     */
    public function test_GetShardKeyInArgs()
    {
        $args = array(
            1 => self::$userId,
            2 => self::$itemDefId,
        );

        $reflector = $this->setMethodPublic('SzMySqlModel', 'getShardKeyInArgs');
        $this->assertEquals(self::$userId, $reflector->invoke(self::$instance, $args));
    }

    /**
     * @see SzAbstractModel::prepareQueryHandles
     */
    public function test_PrepareQueryHandles()
    {
        $reflector = $this->setMethodPublic('SzMySqlModel', 'prepareQueryHandles');
        list($dbHandle, $queryBuilder) = $reflector->invoke(self::$instance, self::$userId);

        $this->assertTrue($dbHandle instanceof SzMySqlDb);
        $this->assertTrue($queryBuilder instanceof SzDbQueryBuilder);
    }

    /**
     * @see SzAbstractModel::delete
     */
    public function test_delete()
    {
        $shardVal = self::$userId;
        $cacheVal = self::$userId;

        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList->getList());
        $this->assertEquals('DELETE FROM `frame1`.`item` WHERE `itemId` = 1;', self::$instance->delete($itemVo));
    }

    /**
     * @see SzAbstractModel::deleteAll
     */
    public function test_DeleteAll()
    {
        $itemVos = array(
            new ItemVo(null, self::$userId, self::$itemDefId, 1, 1, 0, 0),
            new ItemVo(null, self::$userId, self::$itemDefId, 1, 1, 0, 0)
        );

        $this->assertEquals(
            'INSERT INTO `frame1`.`item` (`itemId`, `userId`, `itemDefId`, `type`, `count`, `expireTime`, `updateTime`) VALUES (NULL, ' . self::$userId . ', ' . self::$itemDefId . ', 1, 1, 0, 0), (NULL, ' . self::$userId . ', ' . self::$itemDefId . ', 1, 1, 0, 0);',
            self::$instance->insert($itemVos)
        );
    }
}
<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/model/mock/SzRedisModelMock.class.php';

class SzRedisModelTest extends SzTestAbstract
{
    /**
     * @var SzRedisModel
     */
    protected static $instance;

    protected static $itemId = 999999;
    protected static $userId = 60;
    protected static $itemDefId = 10001;

    public function setUp()
    {
        self::$instance = new SzRedisModelMock();
    }

    /**
     * @see SzRedisModel::save
     */
    public function test_Save()
    {
        $itemVo = new ItemVo(self::$itemId, self::$userId, self::$itemDefId, 2, 1, 0, 0);
        $this->assertEquals(
            array('Item:' . self::$userId, array(self::$itemId => '[' . self::$itemId . ',' . self::$userId . ',' . self::$itemDefId . ',2,1,0,0]')),
            self::$instance->save($itemVo)
        );
    }

    /**
     * @see SzRedisModel::retrieve
     */
    public function test_Retrieve()
    {
        $shardVal = self::$userId;
        $cacheVal = self::$userId;
        /**
         * @var ItemVo $itemVo
         */
        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList);

        $this->assertEquals(self::$itemDefId, $itemVo->getItemDefId());
    }

    /**
     * @see SzRedisModel::delete
     */
    public function test_delete()
    {
        $shardVal = self::$userId;
        $cacheVal = self::$userId;

        $itemList = self::$instance->retrieve($shardVal, $cacheVal);
        $itemVo = array_pop($itemList);

        $this->assertEquals(array('Item:' . self::$userId, 1), self::$instance->delete($itemVo));
    }
}
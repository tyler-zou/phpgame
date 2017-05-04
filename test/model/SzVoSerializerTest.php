<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzVoSerializerTest extends SzTestAbstract
{

    protected static $itemId = 999999;
    protected static $userId = 1;
    protected static $itemDefId = 10001;

    /**
     * @see SzDbQueryBuilder::serialize
     */
    public function test_Serialize()
    {
        $itemVo = new ItemVo(self::$itemId, self::$userId, self::$itemDefId, 1, 1, 0, 0);
        $this->assertEquals('[' . self::$itemId . ',' . self::$userId . ',' . self::$itemDefId . ',1,1,0,0]', SzVoSerializer::serialize($itemVo));
    }

    /**
     * @see SzDbQueryBuilder::unserialize
     */
    public function test_Unserialize()
    {
        $itemVo = new ItemVo(self::$itemId, self::$userId, self::$itemDefId, 1, 1, 0, 0);
        $this->assertEquals($itemVo, SzVoSerializer::unserialize('[' . self::$itemId . ',' . self::$userId . ',' . self::$itemDefId . ',1,1,0,0]', new ReflectionClass('ItemVo')));
    }
}
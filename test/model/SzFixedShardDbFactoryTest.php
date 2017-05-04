<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzFixedShardDbFactoryTest extends SzTestAbstract
{
    /**
     * @var SzFixedShardDbFactory
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzFixedShardDbFactory();
    }

    /**
     * @see SzFixedShardDbFactory::buildShardId
     */
    public function test_buildShardId()
    {
        $shardKey = 60;
        $reflector = $this->setMethodPublic('SzFixedShardDbFactory', 'buildShardId');
        $this->assertEquals(1, $reflector->invoke(self::$instance, $shardKey, 'MySql'));
    }
}
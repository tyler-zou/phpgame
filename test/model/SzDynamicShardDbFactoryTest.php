<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzDynamicShardDbFactoryTest extends SzTestAbstract
{
    /**
     * @var SzDynamicShardDbFactory
     */
    protected static $instance;

    public function setUp()
    {
        self::$instance = new SzDynamicShardDbFactory();
    }

    /**
     * @see SzDynamicShardDbFactory::getMappingKey
     */
    public function test_GetMappingKey()
    {
        $shardKey = 60;
        $reflector = $this->setMethodPublic('SzDynamicShardDbFactory', 'getMappingKey');
        $this->assertEquals('SHARDIDS:MySql:1', $reflector->invoke(self::$instance, $shardKey, 'MySql'));
    }
}
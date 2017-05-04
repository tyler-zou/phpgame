<?php
class SzDbFactory
{

    /**
     * @var SzAbstractDbFactory
     */
    protected static $instance;

    /**
     * Get the instance of SzDbFactory.
     *
     * @throws SzException 10512
     * @return SzAbstractDbFactory
     */
    public static function get()
    {
        if (!self::$instance) {
            $shardType = SzConfig::get()->loadAppConfig('database', 'SHARD_STRATEGY');
            switch ($shardType) {
                case SzAbstractDbFactory::SHARD_TYPE_FIXED:
                    self::$instance = new SzFixedShardDbFactory();
                    break;
                case SzAbstractDbFactory::SHARD_TYPE_DYNAMIC:
                    self::$instance = new SzDynamicShardDbFactory();
                    break;
                default:
                    throw new SzException(10512, $shardType);
                    break;
            }
        }
        return self::$instance;
    }

}
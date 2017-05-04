<?php
abstract class SzAbstractModel
{

    const VO_SUFFIX      = 'Vo';
    const VO_LIST_SUFFIX = 'VoList';
    const MODEL_SUFFIX   = 'Model';

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* ORM CONFIG FILE PARAMS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * table name if MySql, basic cache key name if Redis <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $table = null;
    /**
     * table columns <br/>
     * array(0 => columnName, 1 => ...) <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $columns = array();
    /**
     * column which will auto increase when insert, <br/>
     * content of this array should be key in array $this->columns, <br/>
     * or null means no auto increment column <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $autoIncrColumn = null;
    /**
     * columns should be updated in difference value mode, <br/>
     * contents of this array should be keys in array $this->columns <br/>
     * e.g money is in this list, then money update in SQL would be: `money` = `money` - 3
     *
     * @var array
     */
    protected $diffUpColumns = array();
    /**
     * columns should be filtered in update query, <br/>
     * contents of this array should be keys in array $this->columns <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $updateFilter = array();
    /**
     * columns should be filtered when *Vo converted to array <br/>
     * contents of this array should be keys in array $this->columns <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $toArrayFilter = array();
    /**
     * columns can be used to do searching <br/>
     * contents of this array should be keys in array $this->columns <br/>
     *
     * <b>USED BY:</b> MySql <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $searchColumns = array();
    /**
     * columns in the where condition in update query <br/>
     * contents of this array should be keys in array $this->columns <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $updateColumns = array();
    /**
     * columns are string type in database, and array type in PHP <br/>
     * contents of this array: <br/>
     *
     * <pre>
     * Format in orm config:
     * 'jsonColumns' => array(
     *     0 => 512, // columnId => columnLength
     *     ...
     * ),
     * ------------------------------------------
     * Format in model class:
     * $jsonColumns => array(columnId, ...)
     * </pre>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $jsonColumns = array();
    /**
     * columnId, according to which cache key is generated, <br/>
     * content of this param should be key in array $this->columns <br/>
     * at most time this value shall be the same as the shard column <br/>
     * the only exception shall be the case that model shall not be sharded, <br/>
     * but the content of the model shall be saved into different group of cache keys <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $cacheColumn = null;
    /**
     * columnId, according to which database should be sharded, <br/>
     * content of this param should be key in array $this->columns <br/>
     * null means this object is global unique, no need to be sharded <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $shardColumn = null;
    /**
     * columnId, the primary key of the model, <br/>
     * and according to which the array returned in select function should be ordered <br/>
     * content of this param should be key in array $this->columns <br/>
     * note: sometime the primary key is a combination of shardColumn + pkColumn <br/>
     * e.g items table: userId + itemId
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $pkColumn = null;
    /**
     * columns in the where condition in delete query <br/>
     * contents of this array should be keys in array $this->columns <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var array
     */
    protected $deleteColumns = array();
    /**
     * how many shard tables shall be generated in one physical database shard <br/>
     * database shard: table0, table1, ..., tableN <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $tableShardCount = null;
    /**
     * how long this object will be cached in the cache, <br/>
     * null means use default system expire time
     *
     * @see SzAbstractCache::$expires
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $cacheTime = null;

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* THIRD PARTY PARAMS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * object orm name <br/>
     * e.g ItemListVo => "Item", ProfileVo => "Profile"
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $ormName = null;
    /**
     * total count of table columns <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var int
     */
    protected $columnCount = 0;
    /**
     * flag to identify whether this object is a list model <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var boolean
     */
    protected $isList = false;
    /**
     * the db model type of this object <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $dbType = SzAbstractDb::DB_TYPE_MYSQL;
    /**
     * vo object class name which the select result should be converted to <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $voClassName = null;
    /**
     * vo list class name which the select result should be converted to <br/>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $voListClassName = null;
    /**
     * reflection class instance of current vo class
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> SzAbstractModel
     *
     * @var ReflectionClass
     */
    protected $voReflectionClass = null;
    /**
     * context class instance
     *
     * @var SzContext
     */
    protected $context = null;

    /**
     * Initialize the model. <br/>
     *
     * <pre>
     * All the attributes of the class shall be initialized in the implementation class,
     * which generated by the auto build script.
     * </pre>
     *
     * @return SzAbstractModel
     */
    public function __construct()
    {
        $this->voReflectionClass = new ReflectionClass($this->getVoClassName());

        $this->context = SzContextFactory::get();
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* API FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Retrieve *Vo | *VoList from persistence system.
     *
     * <pre>
     * <b>NOTE:</b>
     *     Implementation class can implement $filters functionality or just ignore it.
     * </pre>
     *
     * @param string $shardColumnValue
     * @param string $cacheColumnValue
     * @param array $filters default array()
     * @return SzAbstractVo|null|SzAbstractVoList
     */
    public abstract function retrieve($shardColumnValue, $cacheColumnValue, $filters = array());

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* CACHE FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Get SzAbstractVo from cache.
     *
     * <pre>
     * This function is used for Non-List mode model.
     * </pre>
     *
     * @see SzAbstractModel::genObjectCacheKey
     *
     * @param string $shardColumnValue
     * @param string $cacheColumnValue
     * @return SzAbstractVo
     */
    public function getVoCache($shardColumnValue, $cacheColumnValue)
    {
        $vo = false;

        if ($this->isCacheDisabled()) {
            return $vo;
        }

        if (!$this->hasListMode()) {
            $key = $this->genObjectCacheKey($cacheColumnValue);
            $handle = SzCacheFactory::get()->getAppCache($shardColumnValue);
            $vo = $handle->get($key);
            if ($vo) {
                $vo = SzVoSerializer::unserialize($vo, $this->voReflectionClass);
            }
        }

        return $vo;
    }

    /**
     * Get array of SzAbstractVo from cache.
     *
     * <pre>
     * This function is used for List mode model.
     * </pre>
     *
     * @see SzAbstractModel::genObjectCacheKey
     *
     * @param string $shardColumnValue
     * @param string $cacheColumnValue
     * @throws SzException 10516
     * @return array array of SzAbstractVo
     */
    public function getListOfVoCache($shardColumnValue, $cacheColumnValue)
    {
        $vos = false;
        $cacheType = SzConfig::get()->loadAppConfig('cache', 'CACHE_TYPE');

        if ($this->isCacheDisabled()) {
            return $vos;
        }

        if ($this->hasListMode()) {
            $key = $this->genObjectCacheKey($cacheColumnValue);
            $handle = SzCacheFactory::get()->getAppCache($shardColumnValue);
            $vos = ($cacheType == SzAbstractCache::CACHE_TYPE_MEMCACHED)
                ? $handle->get($key)
                : $handle->hGetAll($key);
            if ($vos) {
                foreach ($vos as $key => $vo) {
                    $vos[$key] = SzVoSerializer::unserialize($vo, $this->voReflectionClass);
                }
            }
        } else {
            throw new SzException(10516, get_class($this));
        }

        return $vos;
    }

    /**
     * Set a SzAbstractVo into cache.
     *
     * @param SzAbstractVo $object
     * @param int $expire expire time in seconds, default null, means $this->cacheTime
     * @return boolean
     */
    public function setVoCache($object, $expire = null)
    {
        $result = false;

        if ($this->isCacheDisabled()) {
            return $result;
        }

        $key = $this->genObjectCacheKey($object);

        $objectShardValue = $this->getColumnValue($object, $this->getShardColumn());
        $handle = SzCacheFactory::get()->getAppCache($objectShardValue);

        if (is_null($expire) && is_null($this->cacheTime)) {
            $expire = SzAbstractCache::EXPIRES;
        } else if (is_null($expire)) {
            $expire = $this->cacheTime;
        }

        if ($this->hasListMode()) {
            SzLogger::get()->warn('SzAbstractModel: Model with list mode shall not use function setVoCache', array('className' => get_class($this)));
        }

        $result = $handle->set($key, SzVoSerializer::serialize($object), $expire);

        return $result;
    }

    /**
     * Set multi SzAbstractVo into cache. <br/>
     * This function is only the loop of SzRedisCache::setVo, <br/>
     * there is no performance gain.
     *
     * @param array $objects array of SzAbstractVo,
     *          and the implementation class type can be different
     * @param array $expires array of expire times,
     *          empty array means all use system default expire time,
     *          otherwise it should contain the keys same as the keys in $objects,
     *          and if no expire wished, please fill it with value "NULL"
     * @return void
     */
    public function setMultiVoCache($objects, $expires = array())
    {
        if ($this->isCacheDisabled()) {
            return;
        }
        if (!$objects || !is_array($objects)) {
            return;
        }
        foreach ($objects as $index => $object) {
            /* @var SzAbstractVo $object */
            $expire = null;
            if (isset($expires[$index])) {
                $expire = $expires[$index];
            }
            $this->setVoCache($object, $expire);
        }
    }

    /**
     * Set array of SzAbstractVo into cache.
     *
     * <pre>
     * <b>NOTE:</b>
     * The given param $objects have to all be the same Vo implementation.
     * That means if one SzAbstractVo in $objects is ItemVo, others have to all be ItemVo.
     * </pre>
     *
     * @param array $objects array of SzAbstractVo
     * @param array $fullListOfObjectsOfModel array of SzAbstractVoList default null
     * </pre>
     *      This param is only used in memcached.
     *      When cache type is memcached, we need save the whole list of "*Model" into memcached.
     *      Since memcached only supports key value structure.
     * </pre>
     * @param int $expire expire time in seconds, default null, means $this->cacheTime
     * @throws SzException 10517
     * @return boolean
     */
    public function setListOfVoCache($objects, $fullListOfObjectsOfModel = null, $expire = null)
    {
        $result = false;
        $cacheType = SzConfig::get()->loadAppConfig('cache', 'CACHE_TYPE');

        if ($cacheType == SzAbstractCache::CACHE_TYPE_MEMCACHED && is_null($fullListOfObjectsOfModel)) {
            throw new SzException(10523); // if type is memcached, and full list is null, it's invalid
        }

        if ($cacheType == SzAbstractCache::CACHE_TYPE_MEMCACHED) {
            $objects = $fullListOfObjectsOfModel; // if type is memcached, we shall always save the whole list of "*Model"
        }

        if ($this->isCacheDisabled()) {
            return $result;
        }

        if (!$objects || !is_array($objects)) {
            return $result;
        }

        $flatList = true;
        $values = array_values($objects);
        $object = array_shift($values);
        if (!($object instanceof SzAbstractVo)) {
            $flatList = false;
            /**
             * Means $this->list of the SzAbstractVoList is not the flat type:
             *     $this->list = array(
             *         id => SzAbstractVo,
             *         ...
             *     );
             * Maybe:
             *     $this->list = array(
             *         type => array(
             *             id => array(
             *                 id => SzAbstractVo,
             *                 ...
             *             },
             *             ...
             *         ),
             *         ...
             *     );
             * So just loop to retrieve the target.
             */
            $loopLimit = 2;
            $flagFound = false;
            for ($loop = 0; $loop < $loopLimit; ++$loop) {
                $values = array_values($object);
                $object = array_shift($values);
                if ($object instanceof SzAbstractVo) {
                    $flagFound = true;
                    break;
                }
            }
            if (!$flagFound) {
                throw new SzException(10517);
            }
        }
        /* @var SzAbstractVo $object */

        $key = $this->genObjectCacheKey($object);
        $objectShardValue = $this->getColumnValue($object, $this->getShardColumn());
        $handle = SzCacheFactory::get()->getAppCache($objectShardValue);

        /**
         * loop to prepare updates
         * $params: array(key => value)
         * <pre>
         *      pkColumnValue =>
         *      redis: SzVoSerializer::serialize(SzAbstractVo)
         *      memcached: SzAbstractVo->toPureArray()
         * </pre>
         */
        $params = array();
        if ($flatList) {
            foreach ($objects as $object) {
                $objectPkValue = $this->getColumnValue($object, $this->getPkColumn());
                $params[$objectPkValue] = SzVoSerializer::serialize($object);
            }
        } else {
            $loopNotDone = true;
            while ($loopNotDone) {
                $dummies = array();
                foreach ($objects as $object) {
                    if ($object instanceof SzAbstractVo) {
                        /**
                         * last layer, $object is the instance of SzAbstractVo
                         */
                        $objectPkValue = $this->getColumnValue($object, $this->getPkColumn());
                        $params[$objectPkValue] = SzVoSerializer::serialize($object);
                        $loopNotDone = false;
                    } else {
                        /**
                         * $object => array(
                         *     $dummy, // maybe array(id => SzAbstractVo), maybe not
                         *     ...
                         * );
                         */
                        foreach ($object as $dummy) {
                            $dummies[] = $dummy;
                        }
                    }
                }
                if ($dummies) {
                    $objects = $dummies;
                }
            }
        }

        if (is_null($expire) && is_null($this->cacheTime)) {
            $expire = SzAbstractCache::EXPIRES;
        } else if (is_null($expire)) {
            $expire = $this->cacheTime;
        }

        $result = ($cacheType == SzAbstractCache::CACHE_TYPE_MEMCACHED)
            ? $handle->set($key, $params, $expire)
            : $handle->hMset($key, $params, $expire);

        return $result;
    }

    /**
     * Delete a SzAbstractVo in cache.
     *
     * @param SzAbstractVo $object
     * @return boolean
     */
    public function deleteVoCache($object)
    {
        $result = false;

        if ($this->isCacheDisabled()) {
            return $result;
        }

        $key = $this->genObjectCacheKey($object);

        $objectShardValue = $this->getColumnValue($object, $this->getShardColumn());
        $handle = SzCacheFactory::get()->getAppCache($objectShardValue);

        if ($this->hasListMode()) {
            $objectPkValue = $this->getColumnValue($object, $this->getPkColumn());
            $result = $handle->hDel($key, $objectPkValue);
        } else {
            $result = $handle->delete($key);
        }

        return $result;
    }

    /**
     * Delete array of SzAbstractVo into cache.
     *
     * <pre>
     * <b>NOTE:</b>
     * The given param $objects have to all be the same Vo implementation.
     * That means if one SzAbstractVo in $objects is ItemVo, others have to all be ItemVo.
     * </pre>
     *
     * @see SzAbstractModel::setListOfVoCache
     *
     * @param array $objects array of SzAbstractVo
     * @throws SzException 10517
     * @return boolean
     */
    public function deleteListOfVoCache($objects)
    {
        if ($this->isCacheDisabled()) {
            return false;
        }

        if (!$objects || !is_array($objects)) {
            return false;
        }

        $flatList = true;
        $values = array_values($objects);
        $object = array_shift($values);
        if (!($object instanceof SzAbstractVo)) {
            $flatList = false;
            $loopLimit = 2;
            $flagFound = false;
            for ($loop = 0; $loop < $loopLimit; ++$loop) {
                $values = array_values($object);
                $object = array_shift($values);
                if ($object instanceof SzAbstractVo) {
                    $flagFound = true;
                    break;
                }
            }
            if (!$flagFound) {
                throw new SzException(10517);
            }
        }
        /* @var SzAbstractVo $object */

        $key = $this->genObjectCacheKey($object);
        $objectShardValue = $this->getColumnValue($object, $this->getShardColumn());
        $handle = SzCacheFactory::get()->getAppCache($objectShardValue);

        // loop to prepare updates
        $params = array($key);
        if ($flatList) {
            foreach ($objects as $object) {
                $params[] = $this->getColumnValue($object, $this->getPkColumn());
            }
        } else {
            $loopNotDone = true;
            while ($loopNotDone) {
                $dummies = array();
                foreach ($objects as $object) {
                    if ($object instanceof SzAbstractVo) {
                        $params[] = $this->getColumnValue($object, $this->getPkColumn());
                        $loopNotDone = false;
                    } else {
                        foreach ($object as $dummy) {
                            $dummies[] = $dummy;
                        }
                    }
                }
                if ($dummies) {
                    $objects = $dummies;
                }
            }
        }

        return call_user_func_array(array($handle, 'hDel'), $params);
    }

    /**
     * Generate a SzAbstractVo storage key.
     *
     * <pre>
     * $identiy shall always be the value of cacheColumn, or SzAbstractVo
     * </pre>
     *
     * @param SzAbstractVo|string $object
     * @return string
     */
    protected function genObjectCacheKey($object)
    {
        $identify = null;
        if (!($object instanceof SzAbstractVo)) {
            $identify = $object;
        } else {
            $identify = $this->getColumnValue($object, $this->getCacheColumn());
        }
        return "{$this->getOrmName()}:{$identify}";
    }

    /**
     * Is the cache system disabled in this model.
     *
     * <pre>
     * Config <b>cacheTime</b> in orm.config.php:
     *     null: means use default cache time
     *     0: means do not use cache in this model
     * </pre>
     *
     * @return boolean
     */
    protected function isCacheDisabled()
    {
        if (is_numeric($this->cacheTime) && 0 == $this->cacheTime) {
            return true;
        } else {
            return false;
        }
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* VO CLASS MAGIC FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Get orm columns of the model.
     *
     * <pre>
     * See the $orms[$ormName]['columns'] of orm.config.php
     * </pre>
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get the column name according to $columnId.
     *
     * @param int $columnId
     * @param boolean $needUcFirst default false
     * @throws SzException 10504
     * @return string
     */
    public function getColumnName($columnId, $needUcFirst = false)
    {
        $name = null;

        if (is_null($columnId)) {
            /**
             * some orm schema has no shard column defined,
             * and the columnId of the shard column will be null
             *
             * do nothing in this case, null $name would be returned
             */
        } else if ($columnId >= 0
            && $columnId <= ($this->columnCount - 1)
        ) {
            $name = $this->columns[$columnId];
            $name = $needUcFirst ? ucfirst($name) : $name;
        } else {
            throw new SzException(10504, array(get_class($this), $columnId));
        }

        return $name;
    }

    /**
     * Get the column value from SzAbstractVo according to $columnId.
     *
     * @param SzAbstractVo $object
     * @param int $columnId
     * @param boolean $needEncode default true
     * <pre>
     * This switch is used for json columns.
     * If $needEncode is true, all json columns in the return value would be json_encode,
     * otherwise all the json columsn in the return value would be array
     * </pre>
     * @return mixed
     */
    public function getColumnValue($object, $columnId, $needEncode = true)
    {
        $value = null;
        $columnName = $this->getColumnName($columnId, true);
        if (!is_null($columnName)) {
            if (in_array($columnId, $this->jsonColumns) && !$needEncode) {
                $value = call_user_func(
                    array(
                        $object,
                        'getRaw' . $this->getColumnName($columnId, true)
                    )
                );
            } else {
                $value = call_user_func(
                    array(
                        $object,
                        'get' . $this->getColumnName($columnId, true)
                    )
                );
            }
        }

        return $value;
    }

    /**
     * Set the column value into SzAbstractVo according to $columnId.
     *
     * @param SzAbstractVo $object
     * @param int $columnId
     * @param mixed $value
     * @return void
     */
    public function setColumnValue($object, $columnId, $value)
    {
        $result = null;
        $columnName = $this->getColumnName($columnId, true);
        if (!is_null($columnName)) {
            $object->saveColumnStatus(
                $columnId,
                $this->getColumnValue($object, $columnId)
            );
            call_user_func_array(
                array(
                    $object,
                    'set' . $this->getColumnName($columnId, true)
                ),
                array($value)
            );
        }
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* GETTERS & SETTERS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Get the table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the orm name of the model.
     *
     * @return string
     */
    public function getOrmName()
    {
        return $this->ormName;
    }

    /**
     * Get the db type.
     *
     * @return string
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * Get the Vo class name.
     *
     * @return string
     */
    public function getVoClassName()
    {
        return $this->voClassName;
    }

    /**
     * Get the Vo List class name.
     *
     * @return string
     */
    public function getVoListClassName()
    {
        return $this->voListClassName;
    }

    /**
     * Get the cache column id of the model.
     *
     * @return int
     */
    public function getCacheColumn()
    {
        return $this->cacheColumn;
    }

    /**
     * Get the shard column id of the model.
     *
     * @return int
     */
    public function getShardColumn()
    {
        return $this->shardColumn;
    }

    /**
     * Get the primary key column id of the model.
     *
     * @return int
     */
    public function getPkColumn()
    {
        return $this->pkColumn;
    }

    /**
     * Get the table shard count of the model.
     *
     * @return int
     */
    public function getTableShardCount()
    {
        return $this->tableShardCount;
    }

    /**
     * Get auto increment column id of the model.
     *
     * @return int
     */
    public function getAutoIncrColumn()
    {
        return $this->autoIncrColumn;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* UTILITY FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Whether the object has the auto increment column.
     *
     * @return boolean
     */
    public function hasAutoIncrementId()
    {
        return !is_null($this->autoIncrColumn);
    }

    /**
     * Whether this model has list mode or only singleton mode.
     * <pre>
     * true: list mode <br/>
     * false: singleton mode
     * </pre>
     *
     * @return boolean
     */
    public function hasListMode()
    {
        return $this->isList;
    }

    /**
     * Is target $columnId a json type column or not.
     *
     * @param int $columnId
     * @return boolean
     */
    public function isJsonColumn($columnId)
    {
        return in_array($columnId, $this->jsonColumns);
    }

}
<?php
class SzRedisModel extends SzAbstractModel
{

    const AUTO_INCR_KEY = 'AUTOID:%s'; // "AUTOID:Item"

    /**
     * Execute SzAbstractModel::__construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see SzAbstractModel::retrieve
     */
    public function retrieve($shardColumnValue, $cacheColumnValue, $filters = array())
    {
        $data = null;

        $key = $this->genObjectCacheKey($cacheColumnValue);
        $handle = $this->context->getDb($shardColumnValue, SzAbstractDb::DB_TYPE_REDIS);

        if ($this->hasListMode()) {
            $voListName = $this->getVoListClassName();
            $data = $handle->hGetAll($key);
            if ($data) {
                foreach ($data as $dataKey => $vo) {
                    $data[$dataKey] = SzVoSerializer::unserialize(json_decode($vo, true), $this->voReflectionClass);
                }
                $data = new $voListName($data);
            } else {
                $data = new $voListName(array());
            }
        } else {
            $data = $handle->get($key);
            if ($data) {
                $data = SzVoSerializer::unserialize(json_decode($data, true), $this->voReflectionClass);
            } else {
                $data = null;
            }
        }

        return $data;
    }

    /**
     * Save array of SzAbstractVo into Redis.
     *
     * <pre>
     * <b>NOTE:</b>
     * The given param $vos have to all be the same implementation.
     * That means if one SzAbstractVo in $vos is ItemVo, others have to all be ItemVo.
     * </pre>
     *
     * @param array|SzAbstractVo $vos
     * @return boolean
     */
    public function save($vos)
    {
        $result = false;

        if ($this->hasListMode()) {
            if (!is_array($vos)) {
                $vos = array($vos);
            }
            $object = current($vos);
            $key = $this->genObjectCacheKey($this->getColumnValue($object, $this->getCacheColumn()));
            $handle = $this->context->getDb($this->getColumnValue($object, $this->getShardColumn()), SzAbstractDb::DB_TYPE_REDIS);

            $params = array(); // pkColumnValue => SzAbstractVo
            foreach ($vos as $vo) {
                $params[$this->getColumnValue($vo, $this->getPkColumn())] = json_encode(SzVoSerializer::serialize($vo));
            }
            $result = $handle->hMset($key, $params);
        } else {
            /**
             * Non-List mode model, shall always save one SzAbstractVo
             */
            $key = $this->genObjectCacheKey($this->getColumnValue($vos, $this->getCacheColumn()));
            $handle = $this->context->getDb($this->getColumnValue($vos, $this->getShardColumn()), SzAbstractDb::DB_TYPE_REDIS);
            $result = $handle->set($key, json_encode(SzVoSerializer::serialize($vos)));
        }

        return $result;
    }

    /**
     * Delete array of SzAbstractVo in Redis.
     *
     * <pre>
     * <b>NOTE:</b>
     * The given param $vos have to all be the same implementation.
     * That means if one SzAbstractVo in $vos is ItemVo, others have to all be ItemVo.
     * </pre>
     *
     * @param array|SzAbstractVo $vos
     * @return boolean
     */
    public function delete($vos)
    {
        $result = false;

        if ($this->hasListMode()) {
            if (!is_array($vos)) {
                $vos = array($vos);
            }
            $object = current($vos);
            $key = $this->genObjectCacheKey($this->getColumnValue($object, $this->getCacheColumn()));
            $handle = $this->context->getDb($this->getColumnValue($object, $this->getShardColumn()), SzAbstractDb::DB_TYPE_REDIS);

            $params = array($key);
            foreach ($vos as $vo) {
                $params[] = $this->getColumnValue($vo, $this->getPkColumn());
            }
            $result = call_user_func_array(array($handle, 'hDel'), $params);
        } else {
            /**
             * Non-List mode model, shall always save one SzAbstractVo
             */
            $key = $this->genObjectCacheKey($this->getColumnValue($vos, $this->getCacheColumn()));
            $handle = $this->context->getDb($this->getColumnValue($vos, $this->getShardColumn()), SzAbstractDb::DB_TYPE_REDIS);
            $result = $handle->delete($key);
        }

        return $result;
    }

    /**
     * Generate auto increment id value of the model.
     *
     * @param int $incrValue default = 1
     * @return int|boolean
     */
    public function genAutoIncrId($incrValue = 1)
    {
        if (!$this->hasAutoIncrementId()) {
            return false;
        }

        return $this->context->getAppCache(null)->incr(sprintf(self::AUTO_INCR_KEY, $this->getOrmName()), $incrValue);
    }

}
<?php
class SzPersister
{

    /**
     * @var SzPersister
     */
    private static $instance;

    /**
     * Initialize SzPersistenceCache.
     *
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzPersister();
    }

    /**
     * Singleton insurance.
     *
     * @return SzPersister
     */
    private function __construct()
    {
    }

    /**
     * Get instance of SzPersistenceCache.
     *
     * @return SzPersister
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * objects to be persisted:
     *
     * <pre>
     * array(objectName => object)
     * objectName: "{$voName}{$shardId}" | "{$voListName}{$shardId}"
     * object: SzAbstractVo | SzAbstractVoList
     * </pre>
     *
     * @var array
     */
    private $persistenceList = array();

    /**
     * persisted data response:
     *
     * <pre>
     * array(
     *     'UPDATE' => array(
     *          'ormName' => array( // non-list
     *              columnName => value,
     *              ...
     *          ),
     *          'ormName' => array( // list
     *              id => array(
     *                  columnName => value,
     *                  ...
     *              ),
     *              ...
     *          )
     *      ),
     *      'DELETE' => array(
     *          'ormName' => array( // non-list
     *              $shardId, ...
     *          ),
     *          'ormName' => array( // list
     *              shardId => array(
     *                  pkValue, ...
     *          ),
     *      )
     * )
     * </pre>
     *
     * @var array
     */
    private $responseList = array();

    /**
     * Start to persist all the objects changed & cached in this class.
     * Provide parameters to the specific application, allowing the application set $this->$persistenceList to empty array after this method called.
     *
     * @see SzPersister::$responseList
     * @see SzResponseManager::mergeResponse
     *
     *
     * @param boolean $clearPersistenceListFlag default false, whether need to clear the $this->persistenceList after persistence action
     * @return void
     * <pre>
     * responses are all stored in:
     * $this->responseList
     * see more SzPersister::$responseList to get the detail of the structure
     * </pre>
     */
    public function persist($clearPersistenceListFlag = false)
    {
        if ($this->persistenceList) {
            foreach ($this->persistenceList as $object) {
                /* @var SzAbstractVo|SzAbstractVoList $object */
                $response = $object->persist();
                if ($response && $object instanceof SzAbstractVoList) {
                    foreach ($response as $res) {
                        $this->addResponse($res);
                    }
                } else if ($response && $object instanceof SzAbstractVo) {
                    $this->addResponse($response);
                }
            }

            if ($clearPersistenceListFlag) {
                $this->persistenceList = array();
            }
        }
    }

    /**
     * Manually inserted into response when we manually insert something into mysql in logical
     * @see SzPersister::addResponse
     *
     * @param SzAbstractVo $element
     * @param int $shardId
     * @param int $pkValue
     * @throws SzException 10518
     * @return void
     */
    public function addManuallyInsertedResponse($element, $shardId, $pkValue)
    {
        if (!($element instanceof SzAbstractVo)) {
            throw new SzException(10518);
        }

        $newResponse = array(
            $element->getOrmName(), // ormName
            $shardId, // shardId
            $pkValue, // pkValue
            $element->toArray(), // pkValue
        );
        $this->addResponse($newResponse);
        $this->addManuallyPersistedData($element);
    }

    /**
     * Add manually persisted data. The purpose of these data is to be removed if some exception encountered in the logic process.
     * @see ManuallyInsertedExceptionHandler::removeManuallyInsertedData
     *
     * @param SzAbstractVo $element
     * @return void
     */
    private function addManuallyPersistedData($element)
    {
        $persistData = SzSystemCache::cache(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL);
        if (!$persistData) {
            $persistData = array($element);
        } else {
            $persistData[] = $element;
        }
        SzSystemCache::cache(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL, $persistData);
    }

    /**
     * Get manual persist data
     *
     * @return array $manualPersistData
     */
    public function getManuallyPersistedData()
    {
        return SzSystemCache::cache(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL);
    }

    /**
     * Add one persisted response into response list.
     *
     * @param array $response
     * <pre>
     * array(
     *     $ormName', // ormName: e.g Profile, Item, etc...
     *     $shardId', // the shardId of the orm model
     *     $pkValue,  // the pk value of the list object, it will always be "NULL" if the model is non-list model
     *     $changed,  // changed array, $columnName => $value
     * )
     * </pre>
     * @return void
     */
    public function addResponse($response)
    {
        if (!SzConfig::get()->loadAppConfig('app', 'NOTIFY_PERSIST_RESULT')) {
            return null; // no need to build persist response
        }
        
        if (!$response || !is_array($response)) {
            return;
        }

        list($ormName, $shardId, $pkValue, $changed) = $response;

        if (!is_null($changed)) {
            $this->addUpdateResponse($ormName, $shardId, $pkValue, $changed);
        } else {
            $this->addDeleteResponse($ormName, $shardId, $pkValue);
        }
    }

    /**
     * Add one update persisted response into response list.
     *
     * @param string $ormName, e.g Profile, Item, etc...
     * @param int|string $shardId, the shardId of the orm model
     * @param int|string|null $pkValue, the pk value of the list object, it will always be "NULL" if the model is non-list model
     * @param array $changed, changed array, $columnName => $value
     * @return void
     */
    private function addUpdateResponse($ormName, $shardId, $pkValue, $changed)
    {
        $responseKey = SzResponseManager::PERSIST_UPDATE_BODY_KEY;

        $savedResponse = null;
        if (SzUtility::checkArrayKey($responseKey, $this->responseList)
            && SzUtility::checkArrayKey($ormName, $this->responseList[$responseKey])
            && SzUtility::checkArrayKey($shardId, $this->responseList[$responseKey][$ormName])
            && SzUtility::checkArrayKey($pkValue, $this->responseList[$responseKey][$ormName][$shardId])) {
            $savedResponse = $this->responseList[$responseKey][$ormName][$shardId][$pkValue];
        }

        if (!is_null($savedResponse)) {
            foreach ($changed as $key => $val) {
                if (SzUtility::checkArrayKey($key, $savedResponse)) {
                    $savedResponse[$key] = $val;
                }
            }
        } else {
            $savedResponse = $changed;
        }

        if (is_null($pkValue)) {
            // non-list model
            $this->responseList[$responseKey][$ormName][$shardId] = $savedResponse;
        } else {
            // list model
            $this->responseList[$responseKey][$ormName][$shardId][$pkValue] = $savedResponse;
        }
    }

    /**
     * Add one delete persisted response into response list.
     *
     * @param string $ormName, e.g Profile, Item, etc...
     * @param int|string $shardId, the shardId of the orm model
     * @param int|string|null $pkValue, the pk value of the list object, it will always be "NULL" if the model is non-list model
     * @return void
     */
    private function addDeleteResponse($ormName, $shardId, $pkValue)
    {
        $responseKey = SzResponseManager::PERSIST_DELETE_BODY_KEY;

        $savedResponse = null;
        $responseValue = is_null($pkValue) ? $shardId : $pkValue;
        if (SzUtility::checkArrayKey($responseKey, $this->responseList)
            && SzUtility::checkArrayKey($ormName, $this->responseList[$responseKey])) {
            if (is_null($pkValue)) {
                $savedResponse = $this->responseList[$responseKey][$ormName];
            } else {
                if (SzUtility::checkArrayKey($shardId, $this->responseList[$responseKey][$ormName])) {
                    $savedResponse = $this->responseList[$responseKey][$ormName][$shardId];
                }
            }
        }

        if (is_null($savedResponse)) {
            $savedResponse = array($responseValue);
        } else {
            if (!in_array($responseValue, $savedResponse)) {
                $savedResponse[] = $responseValue;
            }
        }

        if (is_null($pkValue)) {
            $this->responseList[$responseKey][$ormName] = $savedResponse;
        } else {
            $this->responseList[$responseKey][$ormName][$shardId] = $savedResponse;
        }
    }

    /**
     * Get the response list of the SzPersister.
     *
     * @return array
     */
    public function getResponseList()
    {
        return $this->responseList;
    }

    /**
     * Get model instance.
     *
     * @param string $ormName
     * @throws SzException 10513
     * @return SzAbstractModel
     */
    public function getModel($ormName)
    {
        $className = $ormName . SzAbstractModel::MODEL_SUFFIX;
        if (!class_exists($className)) {
            throw new SzException(10513, $className);
        }
        $model = SzSystemCache::cache(SzSystemCache::MODEL_CLASS_INSTANCE, $className);
        if (!$model) {
            $model = new $className();
            SzSystemCache::cache(SzSystemCache::MODEL_CLASS_INSTANCE, $className, $model);
        }
        return $model;
    }

    /**
     * Get cached vo instance.
     *
     * @param string $shardVal
     * @param string $ormName
     * @param string $cacheVal default null
     * <pre>
     * Default null, means use the same value as shardVal.
     * In most cases, these two values shall be the same.
     * </pre>
     * @throws SzException 10521
     * @return SzAbstractVo|null
     */
    public function getVo($shardVal, $ormName, $cacheVal = null)
    {
        /* @var $model ModuleProfileModel */
        $model = $this->getModel($ormName);
        if ($model->hasListMode()) {
            throw new SzException(10521, get_class($model));
        }

        if (is_null($cacheVal)) {
            $cacheVal = $shardVal;
        }

        $result = SzSystemCache::cache(SzSystemCache::MODEL_VO_INSTANCE, "{$model->getVoClassName()}{$shardVal}{$cacheVal}");
        if ($result === false || 1) {
            $result = $model->retrieve($shardVal, $cacheVal);
        }

        return $result;
    }

    /**
     * Cache vo instance.
     *
     * @param SzAbstractVo $vo
     * @throws SzException 10519
     * @return boolean
     */
    public function setVo($vo)
    {
        if (!($vo instanceof SzAbstractVo)) {
            throw new SzException(10519);
        }

        $model = $this->getModel($vo->getOrmName());
        $shardVal = $model->getColumnValue($vo, $model->getShardColumn());
        $cacheVal = $model->getColumnValue($vo, $model->getCacheColumn());

        $cacheKey = "{$vo->getVoClassName()}{$shardVal}{$cacheVal}";
        if ($vo->isChanged() || $vo->isInsert()) {
            $this->persistenceList[$cacheKey] = $vo;
        }

        return SzSystemCache::cache(SzSystemCache::MODEL_VO_INSTANCE, $cacheKey, $vo);
    }

    /**
     * Get cached vo list instance.
     *
     * @param string $shardVal
     * @param string $ormName
     * @param string $cacheVal
     * <pre>
     * Default null, means use the same value as shardVal.
     * In most cases, these two values shall be the same.
     * </pre>
     * @throws SzException 10514
     * @return SzAbstractVoList
     */
    public function getVoList($shardVal, $ormName, $cacheVal = null)
    {
        /* @var $model ModuleProfileInfoModel */
        $model = $this->getModel($ormName);
        if (!$model->hasListMode()) {
            throw new SzException(10514, get_class($model));
        }

        if (is_null($cacheVal)) {
            $cacheVal = $shardVal;
        }

        $result = SzSystemCache::cache(SzSystemCache::MODEL_VO_LIST_INSTANCE, "{$model->getVoListClassName()}{$shardVal}{$cacheVal}");
        if ($result === false || 1) {
            $result = $model->retrieve($shardVal, $cacheVal);
            SzSystemCache::cache(SzSystemCache::MODEL_VO_LIST_INSTANCE, "{$model->getVoListClassName()}{$shardVal}{$cacheVal}", $result);
        }

        return $result;
    }

    /**
     * Cache vo list instance.
     *
     * @param SzAbstractVoList $list
     * @throws SzException 10517, 10520
     * @return boolean
     */
    public function setVoList($list)
    {
        if (!($list instanceof SzAbstractVoList)) {
            throw new SzException(10520);
        }

        $targetList = $list->getList();
        if (!$targetList) { // list has no data, check insert list & delete list
            if ($list->getInsertList()) {
                $targetList = $list->getInsertList();
            } else if ($list->getDeleteList()) {
                $targetList = $list->getDeleteList();
            }
            if (!$targetList) {
                /**
                 * no content in $this->list & $this->insertList & $this->deleteList
                 * and also shall has no content in $this->updateList
                 * it's not necessary to go through the next logic
                 */
                return false;
            }
        }

        $values = array_values($targetList);
        $vo = array_shift($values);
        if (!($vo instanceof SzAbstractVo)) {
            $loopLimit = 2;
            $flagFound = false;
            for ($loop = 0; $loop < $loopLimit; ++$loop) {
                $values = array_values($vo);
                $vo = array_shift($values);
                if ($vo instanceof SzAbstractVo) {
                    $flagFound = true;
                    break;
                }
            }
            if (!$flagFound) {
                throw new SzException(10517);
            }
        }
        /* @var SzAbstractVo $vo */

        $model = $this->getModel($vo->getOrmName());
        $shardVal = $model->getColumnValue($vo, $model->getShardColumn());
        $cacheVal = $model->getColumnValue($vo, $model->getCacheColumn());

        $cacheKey = "{$list->getListClassName()}{$shardVal}{$cacheVal}";
        if ($list->isChanged()) {
            $this->persistenceList[$cacheKey] = $list;
        }

        return SzSystemCache::cache(SzSystemCache::MODEL_VO_LIST_INSTANCE, $cacheKey, $list);
    }

}
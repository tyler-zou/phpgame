<?php
abstract class SzAbstractVoList
{

    /**
     * list of vo class objects <br/>
     * array(primaryKeyValue => SzAbstractVo)
     *
     * @var array
     */
    protected $list = array ();

    /**
     * list of vo class objects waiting to be persisted <br/>
     * array(naturalIndexInt => SzAbstractVo)
     *
     * @var array
     */
    protected $insertList = array ();
    /**
     * list of vo class objects waiting to be persisted <br/>
     * array(shardKey => SzAbstractVo)
     *
     * @var array
     */
    protected $updateList = array ();
    /**
     * list of vo class objects waiting to be persisted <br/>
     * array(shardKey => SzAbstractVo)
     *
     * @var array
     */
    protected $deleteList = array();

    /**
     * batch insert threshold count, <br/>
     * if the insert queue to be executed is less or equal to this count, <br/>
     * insert actions would be executed in single insert loops, <br/>
     * otherwise all the insert actions would be combined into single SQL to be executed.
     *
     * @var int
     */
    protected $batchInsertThreshold = 20;

    /**
     * corresponding list class name of current vo class
     * <pre>
     * e.g
     *     "ItemVoList" according to "ItemVo"
     * </pre>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $listClassName;
    /**
     * corresponding orm name of current vo class
     * <pre>
     * e.g
     *     "ItemModel", "ItemVo" => "Item"
     * </pre>
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $ormName;

    /**
     * Initialize SzAbstractVoList.
     *
     * @param array $list array of SzAbstractVo
     * @return SzAbstractVoList
     */
    public function __construct($list)
    {
        $this->list = $this->formatList($list);
        $this->batchInsertThreshold = SzConfig::get()->loadAppConfig('database', 'BATCH_INSERT_THRESHOLD');
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* Elements APIs
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Get one element from the list.
     *
     * @param string $id
     * @param boolean $errNotFound whether to throw exception when element not found
     * @throws SzException 10511
     * @return SzAbstractVo $element
     */
    public function getElement($id, $errNotFound = true)
    {
        $elementExists = SzUtility::checkArrayKey($id, $this->list);
        if (!$elementExists && $errNotFound) {
            throw new SzException(10511, array($id, get_class($this)));
        } else if (!$elementExists) {
            return null;
        } else {
            return $this->list[$id];
        }
    }

    /**
     * Add one element into the list.
     *
     * @param SzAbstractVo $element
     * @throws SzException 10518
     * @return void
     */
    public function addElement($element)
    {
        if (!($element instanceof SzAbstractVo)) {
            throw new SzException(10518);
        }
        $this->insertList[] = $element;
    }

    /**
     * Update one element in the list.
     *
     * @param string $id
     * @param SzAbstractVo $element
     * @param boolean $errNotFound whether to throw exception when element not found
     * @throws SzException 10511, 10518
     * @return void
     */
    public function updateElement($id, $element, $errNotFound = true)
    {
        if (!($element instanceof SzAbstractVo)) {
            throw new SzException(10518);
        }
        $elementExists = SzUtility::checkArrayKey($id, $this->list);
        if (!$elementExists && $errNotFound) {
            throw new SzException(10511, array($id, get_class($this)));
        } else if (!$elementExists) {
            return;
        } else {
            $this->updateList[$id] = $element;
            $this->list[$id] = $element;
        }
    }

    /**
     * Delete one element from the list.
     *
     * @param string $id
     * @param boolean $errNotFound whether to throw exception when element not found
     * @throws SzException 10511
     * @return void
     */
    public function deleteElement($id, $errNotFound = true)
    {
        $elementExists = SzUtility::checkArrayKey($id, $this->list);
        if (!$elementExists && $errNotFound) {
            throw new SzException(10511, array($id, get_class($this)));
        } else if (!$elementExists) {
            return;
        } else {
            $this->deleteList[$id] = $this->list[$id];
            unset($this->list[$id]);
        }
    }

    /**
     * Set element into list.
     * <pre>
     * Sometime you have finished persist action, and want to
     * put the persisted vo into list.
     * At this scenario, use this function.
     * </pre>
     *
     * @param int $id
     * @param SzAbstractVo $element
     * @throws SzException 10518
     * @return void
     */
    public function setElement($id, $element)
    {
        if (!($element instanceof SzAbstractVo)) {
            throw new SzException(10518);
        }
        $this->list[$id] = $element;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* Persist APIs
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Persist this vo list, and return the SzResponse with changed data in it.
     *
     * @see SzPersister::addResponse
     *
     * @return array|null
     * <pre>
     * see the param of SzPersister::addResponse
     * </pre>
     */
    public function persist()
    {
        $responses = array();

        $model = SzPersister::get()->getModel($this->getOrmName());

        $this->persistInsertList($model, $responses);
        $this->persistUpdateList($model, $responses);
        $this->persistDeleteList($model, $responses);

        return $responses;
    }

    /**
     * Persist the insert list of vo, and return the SzResponse with changed data in it.
     *
     * @param SzAbstractModel $model
     * @param array $responses passed by reference
     * @return void
     */
    protected abstract function persistInsertList($model, &$responses);

    /**
     * Persist the update list of vo, and return the SzResponse with changed data in it.
     *
     * @param SzAbstractModel $model
     * @param array $responses passed by reference
     * @return void
     */
    protected abstract function persistUpdateList($model, &$responses);

    /**
     * Persist the delete list of vo, and return the SzResponse with changed data in it.
     *
     * @param SzAbstractModel $model
     * @param array $responses passed by reference
     * @return void
     */
    protected abstract function persistDeleteList($model, &$responses);

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* Utility APIs
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Determine whether this vo list changed or not.
     *
     * @return boolean
     */
    public function isChanged()
    {
        return ($this->insertList || $this->updateList || $this->deleteList);
    }

    /**
     * Get current stored SzAbstractVo list.
     *
     * @return array
     * <pre>
     * array(
     *     VALUE OF "SzAbstractModel::pkColumn" => SzAbstractVo
     * )
     * </pre>
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Get insert list.
     *
     * @return array
     * <pre>
     * array(
     *     VALUE OF "SzAbstractModel::pkColumn" => SzAbstractVo
     * )
     * </pre>
     */
    public function getInsertList()
    {
        return $this->insertList;
    }

    /**
     * Get update list.
     *
     * @return array
     * <pre>
     * array(
     *     VALUE OF "SzAbstractModel::pkColumn" => SzAbstractVo
     * )
     * </pre>
     */
    public function getUpdateList()
    {
        return $this->updateList;
    }

    /**
     * Get delete list.
     *
     * @return array
     * <pre>
     * array(
     *     VALUE OF "SzAbstractModel::pkColumn" => SzAbstractVo
     * )
     * </pre>
     */
    public function getDeleteList()
    {
        return $this->deleteList;
    }

    /**
     * Get list count size.
     *
     * @return int
     */
    public function getListCount()
    {
        return count($this->list);
    }

    /**
     * Get the orm name of the vo list.
     *
     * @return string
     */
    public function getOrmName()
    {
        return $this->ormName;
    }

    /**
     * Get the list class name of the vo list.
     *
     * @return string
     */
    public function getListClassName()
    {
        return $this->listClassName;
    }

    /**
     * Get the shard column value from given $list.
     * The structure of given $list shall be array(int => SzAbstractVo).
     *
     * @param SzAbstractModel $model
     * @param array $list array of SzAbstractVo
     * @throws SzException 10515
     * @return string
     */
    protected function getShardKeyFromList($model, $list)
    {
        /* @var SzAbstractVo $vo */
        $values = array_values($list);
        $vo = array_shift($values);

        if (!($vo instanceof SzAbstractVo)) {
            throw new SzException(10515);
        }

        return $model->getColumnValue($vo, $model->getShardColumn());
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* Structure APIs
    //-* APIs defined here need to be overwritten if structure of $this->list changed
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Reformat the given $list to a proper format.
     * <pre>
     *     Since sometimes the structure stored in $this->list shall not be the plain array(int => SzAbstractVo),
     *     maybe we need something like:
     *         array(
     *             firstLayerId => array(
     *                 secondLayerId => SzAbstractVo,
     *                 ...
     *             ),
     *             ...
     *         )
     *     At the time we need some abstract high level function to do reformat work.
     *     And the implementation in this abstract class has no meaning, it's just used to be overwritten.
     * </pre>
     *
     * @param array $list
     * @return array $list
     */
    protected function formatList($list)
    {
        return $list;
    }

    /**
     * Search one element exists in $this->list or not.
     *
     * @param SzAbstractVo $vo
     * @param SzAbstractModel $model
     * @param string $pkValue the pk value of the given vo, null means not provided,
     * should be retrieve from $vo manually
     * @return boolean true means exists, false means not
     */
    protected function searchElementExists($vo, $model, $pkValue = null)
    {
        if (is_null($pkValue)) {
            $pkValue = $model->getColumnValue($vo, $model->getPkColumn());
        }

        return SzUtility::checkArrayKey($pkValue, $this->list);
    }

    /**
     * Set $vo in current list.
     *
     * @param SzAbstractVo $vo
     * @param SzAbstractModel $model
     * @param string $pkValue the pk value of the given vo, null means not provided,
     * should be retrieve from $vo manually
     * @return void
     */
    protected function setVoInList($vo, $model, $pkValue = null)
    {
        if (is_null($pkValue)) {
            $pkValue = $model->getColumnValue($vo, $model->getPkColumn());
        }

        $this->list[$pkValue] = $vo;
    }

    /**
     * Convert vo list to array.
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();

        if ($this->list) {
            foreach ($this->list as $key => $object) {
                /* @var SzAbstractVo $object */
                $result[$key] = $object->toArray();
            }
        }

        return $result;
    }

    /**
     * Convert the updated objects in vo list to array. <br/>
     * Which is useful when building the response body.
     *
     * @return array
     */
    public function toUpdatedArray()
    {
        $result = array();

        if ($this->updateList) {
            foreach ($this->updateList as $key => $object) {
                /* @var SzAbstractVo $object */
                $result[$key] = $object->toArray();
            }
        }

        return $result;
    }

}
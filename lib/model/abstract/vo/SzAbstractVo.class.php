<?php
abstract class SzAbstractVo
{

    /**
     * changed column ids, used to identify which columns to be updated
     *
     * <pre>
     * columnId starts from 0, please refer to SzAbstractModel::$columns
     * array(
     *     columnId,
     *     ...
     * )
     * </pre>
     *
     * @var array
     */
    protected $changeList = array();
    /**
     * original data of the vo object, before any data is changed
     *
     * <pre>
     * array(
     *     columnId => originalValueBeforeChange,
     *     ...
     * )
     * e.g
     *     table:
     *         profile => array(userId, energy);
     *     ------------------------------------
     *     Before:
     *     $vo:
     *         userId => 568932, energy => 10
     *         changeList => array();
     *         originalData => array();
     *     ------------------------------------
     *     $vo->setEnergy(3);
     *     ------------------------------------
     *     After:
     *     $vo:
     *         userId => 568932, energy => 3
     *         changeList => array(1);
     *         originalData => array(1 => 10);
     *     ------------------------------------
     * </pre>
     *
     * @var array
     */
    protected $originalData = array();

    /**
     * identify current vo will be inserted in persist process, <br/>
     * instead of update process.
     *
     * <pre>
     * This identify shall only be specified as "true",
     * when an Vo object has no auto increment column,
     * and it's initialized as a new object,
     * which will be inserted into database.
     * </pre>
     *
     * @var boolean
     */
    protected $isInsert = false;

    /**
     * current vo class name
     * e.g "ItemVo"
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $voClassName;
    /**
     * corresponding orm name of current vo class
     * e.g "ItemModel", "ItemVo" => "Item"
     *
     * <b>USED BY:</b> MySql, Redis <br/>
     * <b>INIT BY:</b> Implementation Class
     *
     * @var string
     */
    protected $ormName;

    /**
     * Initialize Vo class.
     *
     * @return SzAbstractVo
     */
    public function __construct()
    {
    }

    /**
     * Persist this vo, and return the SzResponse with changed data in it.
     *
     * @see SzPersister::addResponse
     *
     * @return array|null
     * <pre>
     * see the param of SzPersister::addResponse
     * </pre>
     */
    public abstract function persist();

    /**
     * Build the SzResponse according to the changed status of this SzAbstractVo.
     *
     * @see SzPersister::addResponse
     *
     * @param SzAbstractModel $model
     * @param boolean $all
     * @param boolean $delete default false
     * <pre>
     * use toArray to build response body or not, means all the vo variables will be put in the response
     * </pre>
     * @return array|null
     * <pre>
     * see the param of SzPersister::addResponse
     * </pre>
     */
    public function buildResponse($model, $all = false, $delete = false)
    {
        if (!SzConfig::get()->loadAppConfig('app', 'NOTIFY_PERSIST_RESULT')) {
            return null; // no need to build persist response
        }

        $pkValue = $model->getColumnValue($this, $model->getPkColumn());
        // non-shardId mode, use pkValue instead
        $shardId = is_null($model->getShardColumn()) ? $pkValue : $model->getColumnValue($this, $model->getShardColumn());
        // useing "null" value to identify is this object has list mode or not
        $pkValue = $model->hasListMode() ? $pkValue : null;

        if (!$delete) {
            // insert or update
            $changed = array();
            if ($all) {
                $changed = $this->toArray();
            } else {
                foreach ($this->changeList as $columnId) {
                    $columnName = $model->getColumnName($columnId);
                    $columnValue = $model->getColumnValue($this, $columnId, false);
                    $changed[$columnName] = $columnValue;
                }
            }
        } else {
            // delete
            $changed = null;
        }

        $response = array(
            $model->getOrmName(),
            $shardId,
            $pkValue,
            $changed,
        );

        return $response;
    }

    /**
     * Clear the temporary status data storage.
     *
     * @return void
     */
    public function clearStatusChangeInfo()
    {
        $this->changeList   = array();
        $this->originalData = array();
        $this->isInsert     = false;
    }

    /**
     * Save column changed status into $this->changeList & $this->originalData.
     *
     * @param int $columnId starts from 0
     * @param mixed $originalValue
     * @return void
     */
    public function saveColumnStatus($columnId, $originalValue)
    {
        if (!in_array($columnId, $this->changeList)) {
            $this->changeList[] = $columnId;
        }
        if (!SzUtility::checkArrayKey($columnId, $this->originalData, true)) {
            $this->originalData[$columnId] = $originalValue;
        }
    }

    /**
     * Determine whether this vo changed or not.
     *
     * @return boolean
     */
    public function isChanged()
    {
        $changed = false;
        if ($this->changeList) {
            $changed = true;
        }

        return $changed;
    }

    /**
     * Whether this vo is used to be inserted or not.
     *
     * @return boolean
     */
    public function isInsert()
    {
        return $this->isInsert;
    }

    /**
     * Mark $this->isInsert flag to false.
     *
     * @return void
     */
    public function removeInsertFlag()
    {
        $this->isInsert = false;
    }

    /**
     * Convert current vo object to client array,
     * with some params filtered, since they are not necessary to client.
     *
     * @param boolean $needEncode default false
     * <pre>
     * This switch is used for json columns.
     * If $needEncode is true, all json columns in the return value would be json_encode,
     * otherwise all the json columsn in the return value would be array
     * </pre>
     * @return array
     */
    public abstract function toArray($needEncode = false);

    /**
     * Convert current vo object to array.
     *
     * @param boolean $needEncode default false
     * <pre>
     * This switch is used for json columns.
     * If $needEncode is true, all json columns in the return value would be json_encode,
     * otherwise all the json columsn in the return value would be array
     * </pre>
     * @return array
     */
    public abstract function toEntireArray($needEncode = false);

    /**
     * Convert current vo object to array with natural index keys.
     *
     * <pre>
     * Do NOT edit this function,
     * since this function is used by SzVoSerializer::serialize as a part of MODEL system.
     * </pre>
     *
     * @return array
     */
    public abstract function toPureArray();

    /**
     * Get the model class name of the vo.
     *
     * @return string
     */
    public function getOrmName()
    {
        return $this->ormName;
    }

    /**
     * Get vo change list.
     *
     * @return array
     * <pre>
     * array(
     *     columnId,
     *     ...
     * )
     * </pre>
     */
    public function getChangeList()
    {
        return $this->changeList;
    }

    /**
     * Get vo original data.
     *
     * @return array
     * <pre>
     * array(
     *     columnId => previousData,
     *     ...
     * )
     * </pre>
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * Get vo class name.
     *
     * @return string
     */
    public function getVoClassName()
    {
        return $this->voClassName;
    }

}
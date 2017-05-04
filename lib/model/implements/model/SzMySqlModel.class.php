<?php
class SzMySqlModel extends SzAbstractModel
{

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
        // fetch cache
        $data = false;
        if ($this->hasListMode()) {
            $data = $this->getListOfVoCache($shardColumnValue, $cacheColumnValue);
            if ($data) {
                $data = new $this->voListClassName($data);
            }
        } else {
            $data = $this->getVoCache($shardColumnValue, $cacheColumnValue);
        }

        // fetch database
        if (!$data || 1) {
            $data = $this->select($cacheColumnValue);
            // save cache
            if ($this->hasListMode()) {
                $this->setListOfVoCache($data->getList(), $data->getList());
            } else if ($data) {
                $this->setVoCache($data);
            }
        }

        return $data;
    }

    /**
     * Select data from database.
     *
     * <pre>
     * $args is string:
     *     it's the value of the $this->cacheColumn
     * $conds is string:
     *     it's the value of SzDbQueryBuilder::WHERE_*
     * ------------------------
     * $args is array:
     *     it's the array of:
     *     array(
     *         columnId => value,
     *         ...
     *     )
     *     and there have to be the columnId of the $this->cacheColumn
     * $conds is array:
     *     it's the array of:
     *     array(
     *          columnId => SzDbQueryBuilder::WHERE_*,
     *          ...
     *     )
     *
     * @param string|array $args
     * @param string|array $conds
     * @return SzAbstractVo|null|SzAbstractVoList
     */
    public function select($args, $conds = null)
    {
        $data = null;

        // prepare query resources
        $shardKey = $this->getShardKeyInArgs($args);
        // prepare table name
        $table = $this->generateTableName($this->table, $shardKey);
        /**
         * @var SzMySqlDb $dbHandle
         * @var SzDbQueryBuilder $queryBuilder
         */
        list($dbHandle, $queryBuilder) = $this->prepareQueryHandles($shardKey);

        // prepare where
        $wheres = array();
        $whereConds = array();
        if ($args && is_array($args)) {
            foreach ($args as $searchColumnId => $searchValue) {
                if (in_array($searchColumnId, $this->searchColumns)) {
                    // columnId in the allowed search column list
                    $columnName = $this->getColumnName($searchColumnId);
                    $wheres[$columnName] = $searchValue;
                    // condition method specified
                    if (SzUtility::checkArrayKey($searchColumnId, $conds)) {
                        $whereConds[$columnName] = $conds[$searchColumnId];
                    }
                }
            }
        } else if ($args) {
            $columnName = $this->getColumnName($this->cacheColumn);
            $wheres[$columnName] = $args;
            if ($conds) {
                $whereConds[$columnName] = $conds;
            }
        }

        // parpare query sql
        $query = $queryBuilder->selectQuery($dbHandle->getDbName(), $table, $this->columns, $wheres, $whereConds);


//        file_put_contents('/tmp/aa.txt', var_export(debug_backtrace(), 1).PHP_EOL, FILE_APPEND);;
        // log query sql
        SzLogger::get()->debug('SzMySqlModel: SQL', array(
            'type'  => 'select',
            'sql'   => $query
        ));
        // process select result
        $result = $dbHandle->select($query);
        $data = array();
        foreach ($result as $row) {
            $data[$row[$this->getColumnName($this->pkColumn)]] = $this->convertAssocArrayToVo($row);
        }
        // format the return result
        $result = null;
        if ($this->hasListMode()) {
            $result = new $this->voListClassName($data);
        } else if ($data) {
            $result = array_pop($data);
        }

        return $result;
    }

    /**
     * Insert data into database.
     *
     * @param array|SzAbstractVo $vos
     * @return array
     * <pre>
     *     e.g array(
     *             int(affected row count),
     *             int(last inserted id)
     *         )
     * </pre>
     */
    public function insert($vos)
    {
        $shardKey = null;

        // prepare insert columns
        $columns = $this->columns;

        // prepare insert data
        $values = array();
        // prepare get function name, e.g. getUserId
        if (is_array($vos)) { // $vos => array of *Vo
            $values = array();
            foreach ($vos as $vo) {
                $shardKey = $this->getColumnValue($vo, $this->getShardColumn());
                $value = $this->convertVoToAssocArray($vo);
                $values[] = $value;
            }
        } else { // $vos => single *Vo
            $shardKey = $this->getColumnValue($vos, $this->getShardColumn());
            $values = $this->convertVoToAssocArray($vos);
        }

        // prepare table name
        $table = $this->generateTableName($this->table, $shardKey);
        // prepare query resources
        /**
         * @var SzMySqlDb $dbHandle
         * @var SzDbQueryBuilder $queryBuilder
         */
        list($dbHandle, $queryBuilder) = $this->prepareQueryHandles($shardKey);

        // generate query sql
        if (is_array($vos)) {
            $query = $queryBuilder->insertBatchQuery($dbHandle->getDbName(), $table, $columns, $values);
        } else {
            $query = $queryBuilder->insertQuery($dbHandle->getDbName(), $table, $columns, $values);
        }
        // log query sql
        SzLogger::get()->debug('SzMySqlModel: SQL', array(
            'type'  => 'insert',
            'sql'   => $query
        ));

        return $dbHandle->execute($query);
    }

    /**
     * Update data in database.
     *
     * @param SzAbstractVo $vo
     * @return array
     * <pre>
     *     e.g array(
     *             int(affected row count),
     *             int(last inserted id)
     *         )
     * </pre>
     */
    public function update($vo)
    {
        // prepare data update
        $update = $this->prepareUpdate($vo);
        if (false === $update) { // means no change in this *Bo, just return the mocked result
            return array(0, 0);
        }

        // prepare query resources
        $shardKey = $this->getColumnValue($vo, $this->shardColumn);
        // prepare table name
        $table = $this->generateTableName($this->table, $shardKey);
        /**
         * @var SzMySqlDb $dbHandle
         * @var SzDbQueryBuilder $queryBuilder
         */
        list($dbHandle, $queryBuilder) = $this->prepareQueryHandles($shardKey);

        // prepare wheres
        $wheres = array();
        if ($this->updateColumns && is_array($this->updateColumns)) {
            foreach ($this->updateColumns as $columnId) {
                $wheres[$this->getColumnName($columnId)] = $this->getColumnValue($vo, $columnId);
            }
        }

        // generate query sql
        $query = $queryBuilder->updateQuery($dbHandle->getDbName(), $table, $update['columns'], $update['values'], $update['conds'], $wheres);
        // log query sql
        SzLogger::get()->debug('SzMySqlModel: SQL', array(
            'type'  => 'update',
            'sql'   => $query
        ));

        return $dbHandle->execute($query);
    }

    /**
     * Read the change list in *Vo, and prepare for update query.
     *
     * @param SzAbstractVo $vo
     * @throws SzException 10508
     * @return array|boolean
     * <pre>
     * false means it's not necessary to update this vo, nothing changed
     * array(
     *     columns => array(
     *         columnId => columnName,
     *         ...
     *     ),
     *     values => array(
     *         columnId => columnValue,
     *         ...
     *     ),
     *     conds => array(
     *         columnId => difference update mode: SzDbQueryBuilder::SET_*,
     *         ...
     *     ),
     * )
     * </pre>
     */
    protected function prepareUpdate($vo)
    {
        if (!($vo instanceof $this->voClassName)) {
            throw new SzException(10508, $this->voClassName, get_class($vo));
        }

        // init result
        $result = array(
            'columns' => array(),
            'values'  => array(),
            'conds'   => array(),
        );
        // prepare
        /* @var SzAbstractVo $vo */
        $changeList = $vo->getChangeList();
        $originalData = $vo->getOriginalData();
        if (!$changeList) { // means there is no change in *Vo
            $result = false;
        } else {
            foreach ($changeList as $columnId) {
                if (in_array($columnId, $this->updateFilter)) {
                    // column in update filter, no need to compare & update for it
                    continue;
                }
                if (in_array($columnId, $this->diffUpColumns)) {
                    // this column should be updated with difference value, e.g `money` = `money` + 1
                    $diffValue = $this->getColumnValue($vo, $columnId) - $originalData[$columnId];
                    if ($diffValue > 0) {
                        $result['conds'][$columnId] = SzDbQueryBuilder::SET_SELF_PLUS;
                        $result['values'][$columnId] = $diffValue;
                    } else if ($diffValue < 0) {
                        $result['conds'][$columnId] = SzDbQueryBuilder::SET_SELF_MINUS;
                        $result['values'][$columnId] = abs($diffValue);
                    } else {
                        // equals 0, means this column value not changed, no need to update
                        continue;
                    }
                } else {
                    $result['values'][$columnId] = $this->getColumnValue($vo, $columnId);
                }
                // prepare result
                $result['columns'][$columnId] = $this->getColumnName($columnId);
            }
        }

        if (count($result['columns']) == 0) {
            /**
             * Means some columns was set (there is some data in $changeList),
             * but the set value is the same as the original one.
             * Reset the $result to false.
             */
            $result = false;
        }

        return $result;
    }
    /**
     * Delete data from database.
     *
     * @param array|SzAbstractVo $vos
     * @return array
     * <pre>
     *     e.g array(
     *             int(affected row count),
     *             int(last inserted id)
     *         )
     * </pre>
     */
    public function delete($vos)
    {
        // prepare shard key
        $shardKey = null;

        // prepare wheres
        $wheres = array();
        if (is_array($vos)) {
            foreach ($vos as $vo) {
                /* @var SzAbstractVo $vo */
                $shardKey = $this->getColumnValue($vo, $this->shardColumn);
                if ($this->deleteColumns && is_array($this->deleteColumns)) {
                    foreach ($this->deleteColumns as $columnId) {
                        $wheres[$this->getColumnName($columnId)][] = $this->getColumnValue($vo, $columnId);
                    }
                }
            }
            if ($wheres) {
                foreach ($wheres as $whereKey => $whereValues) {
                    $whereValues = array_unique($whereValues); // remove duplicate keys
                    if (count($whereValues) == 1) {
                        // if only one element in where,
                        // convert where from array to basic param, to prevent mysql IN case
                        $wheres[$whereKey] = $whereValues[0];
                    }
                }
            }
        } else {
            $shardKey = $this->getColumnValue($vos, $this->shardColumn);
            if ($this->deleteColumns && is_array($this->deleteColumns)) {
                foreach ($this->deleteColumns as $columnId) {
                    $wheres[$this->getColumnName($columnId)] = $this->getColumnValue($vos, $columnId);
                }
            }
        }

        // prepare table name
        $table = $this->generateTableName($this->table, $shardKey);
        // prepare query resources
        /**
         * @var SzMySqlDb $dbHandle
         * @var SzDbQueryBuilder $queryBuilder
         */
        list($dbHandle, $queryBuilder) = $this->prepareQueryHandles($shardKey);

        // prepare query sql
        $query = $queryBuilder->deleteQuery($dbHandle->getDbName(), $table, $wheres);
        // log query sql
        SzLogger::get()->debug('SzMySqlModel: SQL', array(
            'type'  => 'delete',
            'sql'   => $query
        ));

        return $dbHandle->execute($query);
    }

    /**
     * Delete all data from SHARD_H0(check database.config.php).
     * This function is just for test purpose, please do not call it on production environment.
     *
     * <pre>
     * $args is string:
     *     it's the value of the $this->cacheColumn
     * $conds is string:
     *     it's the value of SzDbQueryBuilder::WHERE_*
     * ------------------------
     * $args is array:
     *     it's the array of:
     *     array(
     *         columnId => value,
     *         ...
     *     )
     *     and there have to be the columnId of the $this->cacheColumn
     * $conds is array:
     *     it's the array of:
     *     array(
     *          columnId => SzDbQueryBuilder::WHERE_*,
     *          ...
     *     )
     * @param string|array $args
     * @return array
     * <pre>
     *     e.g array(
     *             int(affected row count),
     *             int(last inserted id)
     *         )
     * </pre>
     */
    public function deleteAll($args)
    {
        $data = null;

        // prepare query resources
        $shardKey = $this->getShardKeyInArgs($args);
        // prepare table name
        $table = $this->generateTableName($this->table, $shardKey);
        // prepare query resources
        /**
         * @var SzMySqlDb $dbHandle
         * @var SzDbQueryBuilder $queryBuilder
         */
        list($dbHandle, $queryBuilder) = $this->prepareQueryHandles($shardKey);

        // prepare query sql
        $query = $queryBuilder->deleteQuery($dbHandle->getDbName(), $table);
        // log query sql
        SzLogger::get()->debug('SzMySqlModel: SQL', array(
            'type'  => 'delete',
            'sql'   => $query
        ));

        return $dbHandle->execute($query);
    }

    /**
     * Convert associate array to SzAbstractVo.
     *
     * @param $array
     * <pre>
     * array(
     *     columnName => columnValue,
     *     ...
     * )
     * </pre>
     * @return SzAbstractVo
     */
    protected function convertAssocArrayToVo($array)
    {
        $params = array();
        foreach ($this->columns as $columnId => $columnName) {
            $params[$columnId] = $array[$columnName];
        }

        $class = new ReflectionClass($this->getVoClassName());

        return $class->newInstanceArgs($params);
    }

    /**
     * Convert SzAbstractVo to associate array.
     *
     * @param SzAbstractVo $vo
     * @return array
     * <pre>
     * array(
     *     columnName => columnValue,
     *     ...
     * )
     * </pre>
     */
    protected function convertVoToAssocArray($vo)
    {
        return $vo->toPureArray();
    }

    /**
     * Get shard key in input arguments.
     *
     * @param string|array $args
     * @throws SzException 10507
     * @return string $shardKey
     */
    protected function getShardKeyInArgs($args)
    {
        $shardKey = null;
        if (is_null($this->shardColumn)) {
            /**
             * the shardColumn setting is NULL,
             * this means this object no need to be shard,
             * all data of this object will be put in the first shard forever,
             * leave $shardKey NULL
             */
        } else if (is_array($args)) {
            $shardKey = SzUtility::checkArrayKey($this->shardColumn, $args) ? $args[$this->shardColumn] : null;
            if (is_null($shardKey)) {
                throw new SzException(10507);
            }
        } else {
            $shardKey = $args;
        }

        return $shardKey;
    }

    /**
     * Prepare SzAbstractDb & SzDbQueryBuilder.
     *
     * @param string $shardKey
     * @return array
     * <pre>
     * array(
     *     SzAbstractDb,
     *     SzDbQueryBuilder
     * )
     * </pre>
     */
    protected function prepareQueryHandles($shardKey)
    {
        $dbHandle = null;
        $queryBuilder = null;

        $dbHandle = $this->context->getDb($shardKey, $this->getDbType());
        $queryBuilder = $this->context->getQueryBuilder();

        return array($dbHandle, $queryBuilder);
    }

    /**
     * Generate table name with shard id if it's necessary. <br/>
     * Otherwise original table name would be returned.
     *
     * <pre>
     * This function use $shardKey to do mod directly, so $shardKey HAVE TO be a numeric string or number.
     * </pre>
     *
     * @param string $table original table name
     * @param string $shardKey
     * @throws SzException
     * @return string
     */
    protected function generateTableName($table, $shardKey)
    {
        if (is_null($this->getTableShardCount())) {
            return $table;
        } else {
            if (!is_numeric($shardKey)) {
                throw new SzException(10524, var_export($shardKey, true));
            }
            return $table . ($shardKey % $this->getTableShardCount());
        }
    }

}
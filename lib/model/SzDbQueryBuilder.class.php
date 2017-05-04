<?php
class SzDbQueryBuilder
{

    const WHERE_EQ  = '=';
    const WHERE_LT  = '<';
    const WHERE_GT  = '>';
    const WHERE_LET = '<=';
    const WHERE_GET = '>=';

    const SET_EQ         = 'SET_EQ';         // money = 99
    const SET_SELF_MINUS = 'SET_SELF_MINUS'; // money = money - 99
    const SET_SELF_PLUS  = 'SET_SELF_PLUS';  // money = money + 99

    const LEFT  = 'LEFT';  // LEFT join
    const RIGHT = 'RIGHT'; // RIGHT join
    const INNER = 'INNER'; // INNER join

    /**
     * @var SzDbQueryBuilder
     */
    protected static $instance;

    /**
     * Get the instance of SzDbQueryBuilder.
     *
     * @return SzDbQueryBuilder
     */
    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new SzDbQueryBuilder();
        }
        return self::$instance;
    }

    /**
     * mysql db instance
     *
     * @var SzMySqlDB
     */
    protected $connection;

    /**
     * Initialize the SzDbQueryBuilder.
     *
     * @return SzDbQueryBuilder
     */
    public function __construct() {
        $cacheMySqlKey = sprintf(SzSystemCache::MODEL_DB_INSTANCE, SzAbstractDb::DB_TYPE_MYSQL);
        $mysqlConnectionCount = SzSystemCache::countType($cacheMySqlKey);

        if (!$mysqlConnectionCount) {
            $this->connection = SzDbFactory::get()->getDb();
        } else {
            $mysqlConnectionKeys = array_keys(SzSystemCache::dump($cacheMySqlKey));
            $countOfKeys = count($mysqlConnectionKeys);
            if ($countOfKeys > 1) {
                $randKeyId = mt_rand(0, $countOfKeys - 1);
                $this->connection = SzSystemCache::cache($cacheMySqlKey, $mysqlConnectionKeys[$randKeyId]);
            } else {
                $this->connection = SzSystemCache::cache($cacheMySqlKey, $mysqlConnectionKeys[0]);
            }
        }
    }

    /**
     * Generate single insert SQL.
     *
     * @param string $dbName
     * @param string $table
     * @param array $columns
     * @param array $values
     * @throws SzException 10600
     * @return string $query
     */
    public function insertQuery($dbName, $table, $columns, $values) {
        if (count($columns) != count($values)) {
            throw new SzException(10600);
        }
        $values = $this->escape($values);
        $query = "INSERT INTO `{$dbName}`.`{$table}` ";
        $query .= '(`';
        $query .= implode('`, `', $columns);
        $query .= '`)';
        $query .= ' VALUES ';
        $query .= '(';
        $query .= implode(', ', $values);
        $query .= ')';
        $query .= ';';

        return $query;
    }

    /**
     * Generate batch insert SQL.
     *
     * @param string $dbName
     * @param string $table
     * @param array $columns
     * @param array $values values shall be array of single insert values
     * @throws SzException 10600, 10601
     * @return string $query
     */
    public function insertBatchQuery($dbName, $table, $columns, $values) {
        if (!is_array($values)) {
            throw new SzException(10601);
        }
        $values = $this->escape($values);
        $query = "INSERT INTO `{$dbName}`.`{$table}` ";
        $query .= '(`';
        $query .= implode('`, `', $columns);
        $query .= '`)';
        $query .= ' VALUES ';

        $count = 1;
        $total = count($values);
        foreach ($values as $value) {
            if (count($columns) != count($value)) {
                throw new SzException(10600);
            }
            $query .= '(';
            $query .= implode(', ', $value);
            $query .= ')';
            if ($count != $total) {
                $query .= ', ';
            }
            $count++;
        }
        $query .= ';';

        return $query;
    }

    /**
     * Generate delete sql.
     *
     * @param string $dbName
     * @param string $table
     * @param array $wheres default null
     * @param array $whereConds default null
     * @return string $query
     */
    public function deleteQuery($dbName, $table, $wheres = null, $whereConds = null) {
        $query = "DELETE FROM `{$dbName}`.`{$table}`";
        $whereCase = $this->handleWhere($wheres, $whereConds);
        if (!$whereCase) {
            SzLogger::get()->warn('SzDbQueryBuilder: deleteQuery no where case', func_get_args());
        }
        $query .= ($whereCase) ? " $whereCase" : '';
        $query .= ';';
        return $query;
    }

    /**
     * Generate select SQL.
     *
     * @param string $dbName
     * @param string|array $table string: tableName, array(tableName => tableAsName)
     * @param array $columns
     * @param array $wheres default null
     * @param array $whereConds default null
     * @param string|array $orders default null
     * @param boolean $orderUp true: ASC, false: DESC, default true
     * @param string|array $groups
     * @param int $limit
     * @param object|array of object $joins functionality not done yet, do not use for temporarily
     * @return string $query
     */
    public function selectQuery(
        $dbName, $table, $columns,
        $wheres = null, $whereConds = null,
        $orders = null, $orderUp = true,
        $groups = null, $limit = null,
        $joins = null
    ) {
        $query = 'SELECT `' . implode('`, `', $columns) . '` FROM';
        // handle table name
        if (is_string($table)) {
            $query .= " `{$dbName}`.`{$table}`";
        } else if (is_array($table)) {
            foreach ($table as $tableName => $tableAs) {
                // there shall be only one element, though current foreach is used
                $query .= " `{$tableName}` `{$tableAs}`";
            }
        }
        // handle where
        $whereCase = $this->handleWhere($wheres, $whereConds);
        if (!$whereCase) {
            SzLogger::get()->warn('SzDbQueryBuilder: selectQuery no where case', func_get_args());
        }
        $query .= ($whereCase) ? " $whereCase" : '';
        // handle group
        $groupCase = $this->handleGroup($groups);
        $query .= ($groupCase) ? " $groupCase" : '';
        // handle order
        $orderCase = $this->handleOrder($orders, $orderUp);
        $query .= ($orderCase) ? " $orderCase" : '';
        // handle limit
        $limitCase = $this->handleLimit($limit);
        $query .= ($limitCase) ? " $limitCase" : '';
        $query .= ';';

        return $query;
    }

    /**
     * Generate update SQL.
     *
     * @param string $dbName
     * @param string $table
     * @param array $columns
     * @param array $values
     * @param array $columnConds default null
     * <pre>
     * array(
     *     columnId => SzDbQueryBuilder::SET_SELF_MINUS,
     *     // columnId: 0: money
     *     // SQL: `money` = `money` - 99
     * )
     * </pre>
     * @param array $wheres default null
     * @param array $whereConds default null
     * @throws SzException
     * @return string
     */
    public function updateQuery(
        $dbName, $table,
        $columns, $values, $columnConds = null,
        $wheres = null, $whereConds = null
    ) {
        if (count($columns) != count($values)) {
            throw new SzException(10602);
        }
        $values = $this->escape($values);
        $query = "UPDATE `{$dbName}`.`{$table}` SET ";
        $numCols = count($columns);
        $curCol = 1;
        foreach ($columns as $columnId => $columnName) {
            if (SzUtility::checkArrayKey($columnId, $columnConds)) {
                if ($columnConds[$columnId] == self::SET_EQ) {
                    $query .= "`{$columnName}` = {$values[$columnId]}";
                } else if ($columnConds[$columnId] == self::SET_SELF_MINUS) {
                    $query .= "`{$columnName}` = `{$columnName}` - {$values[$columnId]}";
                } else if ($columnConds[$columnId] == self::SET_SELF_PLUS) {
                    $query .= "`{$columnName}` = `{$columnName}` + {$values[$columnId]}";
                }
            } else {
                $query .= "`{$columnName}` = {$values[$columnId]}";
            }
            if ($curCol != $numCols) {
                $query .= ', ';
            }
            ++$curCol;
        }
        $whereCase = $this->handleWhere($wheres, $whereConds);
        if (!$whereCase) {
            SzLogger::get()->warn('SzDbQueryBuilder: updateQuery no where case', func_get_args());
        }
        $query .= ($whereCase) ? " $whereCase" : '';
        $query .= ';';

        return $query;
    }

    /**
     * Escape the input values.
     *
     * @param mixed $values
     * @return mixed $values
     */
    protected function escape($values) {
        return $this->connection->esc($values);
    }

    /**
     * Convert wheres array to SQL string.
     *
     * @param array $wheres
     * <pre>
     * array(
     *     'userId' => array(12, 2948, 93184), // `userId` IN (12, 2948, 93184), array value default converted to IN pattern
     *     'userId' => userId, // `key` = 2948, string value default converted to equal pattern
     * )
     * </pre>
     * @param array $whereConds
     * <pre>
     * array(
     *     'userId' => SzDbQueryBuilder::WHERE_GET, // `userId` >= 2948
     * )
     * </pre>
     * @return string $query
     */
    protected function handleWhere($wheres, $whereConds = null) {
        $query = '';
        if ($wheres && is_array($wheres)) {
            $total = count($wheres);
            $count = 1;
            $query .= 'WHERE ';
            foreach ($wheres as $key => $value) {
                if (is_array($value)) {
                    $value = $this->escape($value);
                    $inValue = implode(', ', $value);
                    $query .= "`{$key}` IN ({$inValue})";
                } else if (SzUtility::checkArrayKey($key, $whereConds)) {
                    if ($whereConds[$key] == self::WHERE_EQ) {
                        $query .= "`{$key}` = " . $this->escape($value);
                    } else if ($whereConds[$key] == self::WHERE_GET) {
                        $query .= "`{$key}` >= " . $this->escape($value);
                    } else if ($whereConds[$key] == self::WHERE_GT) {
                        $query .= "`{$key}` > " . $this->escape($value);
                    } else if ($whereConds[$key] == self::WHERE_LET) {
                        $query .= "`{$key}` <= " . $this->escape($value);
                    } else if ($whereConds[$key] == self::WHERE_LT) {
                        $query .= "`{$key}` < " . $this->escape($value);
                    }
                } else {
                    $query .= "`{$key}` = " . $this->escape($value);
                }
                if ($count != $total) {
                    $query .= ' AND ';
                }
                $count++;
            }
        }
        return $query;
    }

    /**
     * Convert limit number to SQL string.
     *
     * @param int $limit
     * @return string $query
     */
    protected function handleLimit($limit) {
        return ($limit) ? "LIMIT $limit" : '';
    }

    /**
     * Convert group string or array to SQL string.
     *
     * @param string|array $groups
     * @return string $query
     */
    protected function handleGroup($groups) {
        $query = '';
        if (is_string($groups)) {
            $query .= "GROUP BY `{$groups}`";
        } else if (is_array($groups)) {
            $query .= 'GROUP BY `'. implode('`, `', $groups) . '`';
        }
        return $query;
    }

    /**
     * Convert order string or array to SQL string.
     *
     * @param string|array $orders
     * @param boolean $orderUp true: ASC, false: DESC
     * @return string $query
     */
    protected function handleOrder($orders, $orderUp) {
        $query = '';
        if (is_string($orders)) {
            $query .= "ORDER BY `{$orders}`";
        } else if (is_array($orders)) {
            $query .= 'ORDER BY `' . implode('`, `', $orders) . '`';
        }
        if ($orders && true === $orderUp) {
            $query .= ' ASC';
        } else if ($orders && false === $orderUp) {
            $query .= ' DESC';
        }
        return $query;
    }

}
<?php
class SzMySqlDb extends SzAbstractDb
{

    /**
     * identify whether current php environment has the method "fetch_all" in <br/>
     * class "fetch_all". Since this method is "MySQL Native Driver Only".
     *
     * @link http://www.php.net/manual/en/mysqli-result.fetch-all.php
     *
     * @var boolean
     */
    private $isFetchAllAvailable = false;

    /**
     * @see SzAbstractDb::connect
     */
    protected function connect($host, $port, $userName = null, $password = null, $dbName = null)
    {
        $handle = mysqli_connect($host, $userName, $password, $dbName, $port);

        if (mysqli_connect_error()) {
            throw new SzException(10500, array($host, $port, mysqli_connect_errno(), mysqli_connect_error()));
        } else {
            mysqli_set_charset($handle, 'utf8');
        }

        $this->isFetchAllAvailable = method_exists('mysqli_result', 'fetch_all');

        return $handle;
    }

    /**
     * Executes a select statement and <br/>
     * returns an array of associative arrays containing the results
     *
     * @param string $sql
     * @throws SzException 10502
     * @return array $result
     */
    public function select($sql)
    {
        /* @var mysqli_result $result */
        $result = mysqli_query($this->readHandle, $sql);

//        file_put_contents('/tmp/aa.txt', var_export(debug_backtrace(), 1).PHP_EOL, FILE_APPEND);;
        if (!$result) {
            throw new SzException(
                10502, array(
                    mysqli_errno($this->readHandle), mysqli_error($this->readHandle), $sql
                )
            );
        }

        if ($this->isFetchAllAvailable) {
            $array = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            for ($i = 0; $array[$i] = mysqli_fetch_assoc($result); $i++);
            array_pop($array);
        }

        mysqli_free_result($result);

        return $array;
    }

    /**
     * Executes sql statement, <br/>
     * returning an array with rowCount and lastInsertID information <br/>
     *
     * <pre>
     * Used for INSERT and UPDATE
     * </pre>
     *
     * @param string $sql
     * @throws SzException 10502
     * @return array $result
     * <pre>
     *     e.g array(
     *             int(affected row count),
     *             int(last inserted id)
     *         )
     * </pre>
     */
    public function execute($sql)
    {
        $result = mysqli_query($this->writeHandle, $sql);

        if (!$result) {
            throw new SzException(
                10502, array(
                    mysqli_errno($this->writeHandle), mysqli_error($this->writeHandle), $sql
                )
            );
        }

        return array(
            mysqli_affected_rows($this->writeHandle),
            mysqli_insert_id($this->writeHandle)
        );
    }

    /**
     * Escape the input values.
     *
     * @param string|int|array $values
     * @param boolean $quotes whether use '"' to wrap the escaped value, default true
     * @return mixed $values
     */
    public function esc($values, $quotes = true)
    {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $values[$key] = $this->esc($value, $quotes);
            }
        } else if (is_null($values)) {
            $values = 'NULL';
        } else if (is_bool($values)) {
            $values = $values ? 1 : 0;
        } else {
            $values = mysqli_real_escape_string($this->readHandle, $values); // requires a db connection
            if ($quotes) {
                $values = "'$values'";
            }
        }

        return $values;
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        mysqli_query($this->writeHandle, 'BEGIN;');
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollbackTransaction()
    {
        mysqli_query($this->writeHandle, 'ROLLBACK;');
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commitTransaction()
    {
        mysqli_query($this->writeHandle, 'COMMIT;');
    }

}
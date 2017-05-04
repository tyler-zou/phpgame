<?php
abstract class SzAbstractDb
{

    const DB_TYPE_MYSQL = 'MySql';
    const DB_TYPE_REDIS = 'Redis';

    public static $VALID_DB_TYPES = array(
        self::DB_TYPE_MYSQL, self::DB_TYPE_REDIS
    );

    /**
     * database name
     *
     * @var string
     */
    protected $dbName;
    /**
     * reader server host & port info <br/>
     *
     * <pre>
     * e.g
     *     array(
     *         array('192.168.0.100', 3306),
     *         array('192.168.0.101', 3306),
     *         ...
     *     )
     * </pre>
     *
     * @var array
     */
    protected $readerConnectInfo;
    /**
     * user name used to connect to reader server
     *
     * @var string
     */
    protected $readerUser;
    /**
     * user password used to connect to reader server
     *
     * @var string
     */
    protected $readerPwd;
    /**
     * writer server host & port info <br/>
     *
     * <pre>
     * e.g
     *     array('192.168.0.200', 3306)
     * </pre>
     *
     * @var array
     */
    protected $writerConnectInfo;
    /**
     * user name used to connect to writer server
     *
     * @var string
     */
    protected $writerUser;
    /**
     * user password used to connect to writer server
     *
     * @var string
     */
    protected $writerPwd;

    /**
     * reader server mysql connection resource
     *
     * @var resource|mysqli|Redis
     */
    protected $readHandle;
    /**
     * writer server mysql connection resource
     *
     * @var resource|mysqli|Redis
     */
    protected $writeHandle;

    /**
     * Initialize the db class.
     *
     * @param array $configs the detail of the configs: refer to config file "database.config.php"
     * @throws SzException 10505
     * @return SzAbstractDb
     */
    public function __construct($configs)
    {
        if (!SzUtility::checkArrayKey(
                array(
                    'DB_NAME',
                    'READER_HOST',
                    'READER_USER',
                    'READER_PWD',
                    'WRITER_HOST',
                    'WRITER_USER',
                    'WRITER_PWD'
                ),
                $configs, true)
        ) {
            throw new SzException(10505, get_class($this));
        }

        $this->dbName            = $configs['DB_NAME'];
        $this->readerConnectInfo = $configs['READER_HOST'];
        $this->readerUser        = $configs['READER_USER'];
        $this->readerPwd         = $configs['READER_PWD'];
        $this->writerConnectInfo = $configs['WRITER_HOST'];
        $this->writerUser        = $configs['WRITER_USER'];
        $this->writerPwd         = $configs['WRITER_PWD'];

        $this->generateConnections();
    }

    /**
     * Generate writer & reader connections.
     *
     * @return void
     */
    protected function generateConnections()
    {
        // connect the master server (writer)
        $this->writeHandle = $this->connect(
            $this->writerConnectInfo[0],
            $this->writerConnectInfo[1],
            $this->writerUser,
            $this->writerPwd,
            $this->dbName
        );

        // determine whether need to connect to slave server (reader)
        $writerHost = $this->writerConnectInfo[0];
        $readerCount = count($this->readerConnectInfo);
        $connectionInfo = array();

        if ($readerCount == 1 && $writerHost != $this->readerConnectInfo[0][0]) {
            // only one slave config & not the same as master, connect to it
            $connectionInfo = $this->readerConnectInfo[0];
        } else if ($readerCount > 0) {
            $connectionInfo = $this->randomReadHost($this->readerConnectInfo);
            if ($connectionInfo[0] == $writerHost) {
                // random result the reader server is master, no need to connect to it again
                $connectionInfo = array();
            }
        } // else => only one slave config & is the same as master, no need to connect to it

        if (!$connectionInfo) { // use the same handle as writer
            $this->readHandle = $this->writeHandle;
        } else {
            // connect to the reader server
            $this->readHandle = $this->connect(
                $connectionInfo[0],
                $connectionInfo[1],
                $this->readerUser,
                $this->readerPwd,
                $this->dbName
            );
        }
        unset($writerHost, $readerCount, $connectionInfo);
    }

    /**
     * Connect to the target server.
     *
     * @param string $host
     * @param int $port
     * @param string $userName default null
     * @param string $password default null
     * @param string $dbName default null
     * @return resource|mysqli|Redis
     */
    protected abstract function connect($host, $port, $userName = null, $password = null, $dbName = null);

    /**
     * Randomly select a read host.
     *
     * @see SzAbstractDb::readerConnectInfo
     *
     * @param array $readerHost
     * @return array $config array(0 => host, 1 => port)
     */
    protected function randomReadHost($readerHost)
    {
        return $readerHost[mt_rand(0, count($readerHost) - 1)];
    }

    /**
     * Get the database name.
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

}
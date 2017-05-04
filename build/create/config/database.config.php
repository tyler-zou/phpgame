<?php
return array( // database type name, refer to SzAbstractDb::$VALID_DB_TYPES
    // NORMAL
    'BATCH_INSERT_THRESHOLD' => 20,
    'Payment' => array( // database type of payment db is always "MySql"
        'DB_NAME'     => 'payment',
        'READER_HOST' => array(
            array('172.17.0.24', 3306)
        ),
        'READER_USER' => 'php',
        'READER_PWD'  => 'shinezone2008',
        'WRITER_HOST' => array('172.17.0.24', 3306),
        'WRITER_USER' => 'php',
        'WRITER_PWD'  => 'shinezone2008',
    ),
    // SHARD
    'SHARD_STRATEGY' => 'Fixed', // refer to SzAbstractDbFactory::SHARD_TYPE_*
    // DYNAMIC SHARD
    'SHARD_ESTIMATE_REGISTER_USER_COUNT' => 5000000, // always 1000 * x, this value cannot be changed after app goes online
    'SHARD_WEIGHT_MySql' => array( // refer to SzUtility::getRandomElementByProbability
        // shardId => probability
        0 => 500, // 50% shard 0
        1 => 500, // 50% shard 1
    ),
    'SHARD_WEIGHT_Redis' => array( // refer to SzUtility::getRandomElementByProbability
        0 => 0, // always shard 0
    ),
    // DB CONFIGS WITH TYPE
    'MySql' => array(
        array(
            'DB_NAME'     => 'frame0',
            'READER_HOST' => array(
                array('172.17.0.24', 3306)
            ),
            'READER_USER' => 'php',
            'READER_PWD'  => 'shinezone2008',
            'WRITER_HOST' => array('172.17.0.24', 3306),
            'WRITER_USER' => 'php',
            'WRITER_PWD'  => 'shinezone2008',
        ),
        array(
            'DB_NAME'     => 'frame1',
            'READER_HOST' => array(
                array('172.17.0.24', 3306)
            ),
            'READER_USER' => 'php',
            'READER_PWD'  => 'shinezone2008',
            'WRITER_HOST' => array('172.17.0.24', 3306),
            'WRITER_USER' => 'php',
            'WRITER_PWD'  => 'shinezone2008',
        ),
    ),
    'Redis' => array(
        array(
            'DB_NAME'     => null,
            'READER_HOST' => array(
                array('127.0.0.1', 6370)
            ),
            'READER_USER' => null,
            'READER_PWD'  => null,
            'WRITER_HOST' => array('127.0.0.1', 6370),
            'WRITER_USER' => null,
            'WRITER_PWD'  => null,
        ),
        array(
            'DB_NAME'     => null,
            'READER_HOST' => array(
                array('127.0.0.1', 6371)
            ),
            'READER_USER' => null,
            'READER_PWD'  => null,
            'WRITER_HOST' => array('127.0.0.1', 6371),
            'WRITER_USER' => null,
            'WRITER_PWD'  => null,
        ),
    ),
);
<?php
return array(
    'SECURE_CACHE_ENABLED' => false,
    'App_Memcached' => array(
        array('127.0.0.1.', 11210),
    ),
    'Static_Memcached' => array( // cache servers never OOM & never LRU
        array('127.0.0.1', 11211),
    ),
    'App_Redis' => array(
        array('127.0.0.1', 6371),
    ),
    'Static_Redis' => array( // cache servers never OOM & never LRU
        array('127.0.0.1', 6370),
    ),
);

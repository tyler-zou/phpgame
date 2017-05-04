<?php
return array(
    'debug' => true,
    'mode' => 'development',

    // Can be either mongodb or file.
    'save.handler' => 'mongodb', // file: to log into file
    // Needed for file save handler. Beware of file locking. You can adujst this file path
    // to reduce locking problems (eg uniqid, time ...)
    'save.handler.filename' => '/tmp/xhgui/xhgui_' . date('Ymd') . '_' . uniqid() . '.dat',

    // Mongo
    'db.host' => 'mongodb://root:123@127.0.0.1:27017',
    'db.db' => 'xhprof',
    // Allows you to pass additional options like replicaSet to MongoClient.
    'db.options' => array(),

    // Profile 1 in 10000 requests.
    // You can return true to profile every request.
    'profiler.enable' => function() {
        //return mt_rand(0, 10000) === 42;
        return false;
    },

    'profiler.simple_url' => function($url) {
        return preg_replace('/\=\d+/', '', $url);
    }
);

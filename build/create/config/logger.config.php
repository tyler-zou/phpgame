<?php
return array(
    'LOG_LEVEL' => LOG_DEBUG, // LOG_DEBUG, LOG_INFO, LOG_NOTICE, LOG_WARNING, LOG_ERR
    'LOG_TYPE'  => 'FILELOG',  // SYSLOG, PROLOG, FILELOG
    // FILELOG
    'LOG_FILE'  => '/tmp/szdebug.log',
    // SYSLOG
    'LOG_IDENTITY' => 'PHP-CGI',
    'LOG_FACILITY' => LOG_USER,
    'LOG_MAX_SIZE' => 1024,
    'LOG_RETAIN_FIELD' => array('act'),
    'LOG_FILTER' => 'UNLABELED'
);
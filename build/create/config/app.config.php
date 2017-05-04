<?php
return array(
    // GLOBAL
    'ENV'             => 'DEV', // DEV, LIVE, STAGING
    'LANG'            => 'en_us',
    'TIMEZONE'        => 'Asia/Shanghai',
    'JS_VER'          => 1,
    'CSS_VER'         => 1,
    'WEB_HOST'        => 'frame.shinezone.com',
    'CDN_HOST'        => 'frame.shinezone.com',
    'PLATFORM'        => '',
    'CANVAS_URL'      => '',
    // SWITCHES
    'API_COMPRESS'                => false, // request & response contents will be compressed by "gzcompress"
    'NOTIFY_PERSIST_RESULT'       => false, // notify the persist result to client, in "UPDATE"
    'PLATFORM_ON'                 => true,  // whether enable platform api
    'LOG_ERROR_TRACE'             => false, // whether log error trace
    'LOG_EXCEPTION_TRACE'         => false, // whether log exception trace
    'RESPONSE_JSON_OPTIONS'       => 0, // json_encode params, use in class "SzResponseManager"
    'PERSIST_RESULT_FILTER'       => false,
    'PERSIST_RESULT_FORCE_OBJECT' => false,
    'API_REPEAT_CHECK' => false,
    'API_REPEAT_LIMIT' => 0,
    'API_REPEAT_EXPIRE' => 0,
    'API_SIGN_SECRET' => '',

    // FRAMEWORK
    'FRAMEWORK_ROOT'  => '/data1/www/frame.shinezone.com/deploy/framework.php', // absolute path
    'FRAMEWORK_VER'   => 'latest',
    // MODULES
    'MODULE_ROOT'     => '/data1/www/frame.shinezone.com/deploy/modules.php', // absolute path
    'MODULE_VERS'     => array(
        // moduleName => version
    ),
);
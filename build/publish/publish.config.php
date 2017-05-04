<?php
return array(
    // PLATFORM
    // facebook => SzPublishPlatform"Facebook", refer to SzPublish::$PLATFORMS
    // null platform, means test environment, "none" => SzPublishPlatformNone
    'PLATFORM'         => null,
    'ENV'              => 'DEV', // DEV, LIVE, STAGING
    'APP_CANVAS'       => '',
    'WEB_HOST'         => '',
    'APP_ID'           => '',
    'APP_SECRET'       => '',
    // PUBLISH
    'ONLINE_VER'       => 'latest', // e.g v0.1.0
    'PREVIEW_VER'      => 'latest', // e.g v0.1.1
    'PREVIEW_PERCENT'  => 10, // preview open rate, 10 means 10%
    // THROTTLE
    'THROTTLE_PERCENT' => 0, // game open rate, 10 means 10%, 0 means no throttle
    // DEV & QA LIST
    'DEV_LIST' => array(
        // platformId, platformId, ...
    ),
    // WHITE LIST
    'WHITE_LIST' => array(
        // platformId, platformId, ...
    ),
    // IP WHITE LIST
    'IP_WHITE_LIST' => array(
        // ipAddress, ipAddress, ...
    ),
);
<?php
// load app configs
$appConfigs = require __DIR__ . '/../config/app.config.php';
// load framework system
require $appConfigs['FRAMEWORK_ROOT'] . '/' . $appConfigs['FRAMEWORK_VER'] . '/SzSystem.class.php';
// parse app root
$appRoot = __DIR__; // e.g "/home/php/app/demo/apps/main/v0.1.1/www"
$wwwRelativePath = substr($appRoot, strpos($appRoot, 'apps')); // apps/main/v0.1.1/www
$appRoot = substr($appRoot, 0, strlen($appRoot) - 4); // remove tailing '/www'

// initialize framework system
SzSystem::init($appRoot, $wwwRelativePath, $appConfigs['MODULE_ROOT'], $appConfigs['MODULE_VERS']);
// process the requests
SzController::get()->process(); exit(0); // execute & exit
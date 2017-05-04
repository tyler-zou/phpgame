<?php
require 'SzPublish.class.php';

$publisher = SzPublish::get();
$version = $publisher->getAppVersion();
$developer = $publisher->getDeveloper();
unset($publisher);

if (!file_exists("apps/{$developer}/{$version}/www/index.php")) {
    $version = 'latest';
}

require "apps/{$developer}/{$version}/www/index.php"; // load the index.php of specified app version
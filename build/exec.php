#!/usr/bin/env php
<?php
require 'lib/Utililty.class.php';
require 'lib/Builder.class.php';

ob_start();

$builder = new Builder();

$builder->prePrintStartInfo();
$builder->preValidateArgvMode();

$builder->run();

ob_end_flush();
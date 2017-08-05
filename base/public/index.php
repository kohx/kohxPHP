<?php

$base_dir = 'kohxPHP/base';
$app_dir = 'app';
$core_dir = 'core';

define('DS', DIRECTORY_SEPARATOR);
define('NS', '\\');
define('EXT', '.php');
define('BASE_PATH', substr(__DIR__, 0, strpos(__DIR__, str_replace('/', DS, $base_dir)) + strlen(str_replace('/', DS, $base_dir))) . DS);
define('APP_PATH', BASE_PATH . $app_dir . DS);
define('CORE_PATH', BASE_PATH . $core_dir . DS);
define('APP_NAMESPACE', str_replace('/', NS, $app_dir));

require_once APP_PATH . 'bootstrap.php';



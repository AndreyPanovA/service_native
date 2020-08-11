<?php
mb_internal_encoding('UTF-8');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
define('ROOT_DIR', realpath(__DIR__).'/../');
define('NL', PHP_EOL);
define('KEY', '88c3315191aa317b37907a06ee879ea9');
define('AJAX',$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' || $_POST['hash']);
define('HOST',$_SERVER['HTTP_HOST']);
ob_start();
@session_start();
require_once ROOT_DIR.'inc/functions.php';
require_once ROOT_DIR.'lib/config.php';

new DT;
new Cash;
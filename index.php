<?php
mb_internal_encoding('UTF-8');
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
define('ROOT_DIR', realpath(__DIR__).'/');
define('NL', PHP_EOL);
define('KEY', '88c3315191aa317b37907a06ee879ea9');
define('AJAX',$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' || $_POST['hash']);
define('HOST',$_SERVER['HTTP_HOST']);
ob_start();
@session_start();
require_once ROOT_DIR.'class/functions.php';
require_once ROOT_DIR.'config/config.php';

new DT;

$_REQUEST['page'] = $_REQUEST['page'] ? : 'index';

$m = $_REQUEST['m'] ? : 'Lists';
unset($_REQUEST['m']);

$c = $_REQUEST['c'] ? : 'Page';
unset($_REQUEST['c']);

if (!class_exists($c) || !method_exists($c, $m)) {
	throw new Exception('Unknown call '.$c.'::'.$m);
}

$object = new $c;
$object->set_query($_REQUEST);

$result = $object->$m();

echo AJAX ? json_encode($result) : $result;
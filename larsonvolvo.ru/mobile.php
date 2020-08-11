<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');

if (!$_REQUEST = json_decode(file_get_contents('php://input'), true)) exit;

$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

$_REQUEST['is_app'] || ($_REQUEST['is_app'] = 1);

$_POST || ($_POST = $_REQUEST);

require 'index.php';
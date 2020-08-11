<?php
define('IS_LOCAL', 0);
define('IS_DEMO', 0);
foreach (['Exception', 'Main'] as $name) {
	// DB::Create($name, 'localhost', 'root', 'mysql', 'lk');
	DB::Create('Main','localhost','root','zhYak36fa','lk');
}
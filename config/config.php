<?php
define('IS_LOCAL', 0);
foreach (['Exception', 'Main'] as $name) {
	IS_LOCAL
	? DB::Create($name, 'localhost', 'root', 'mysql', 'lk')
	: DB::Create('Main','localhost','root','zhYak36fa','lk');
}

function config_smtp() {
	return [
		'host' => 'smtp.yandex.ru',
		'port' => 465,
		'login' => 'client@larsonv.ru',
		'password' => 'pit11117',
	];
}

<?php class Cash {

static
	$user = [],
	$tab = ['News'];

function __construct() {
	self::$user = DB::select('SELECT ID AS ARRAY_KEY, Login, FIO, Position, Department, isDeleted
		FROM User
		ORDER BY Department, Position, FIO');
}

static function access_check($tab) {
	return true;
}

static function db_client() {
	config_db_client();
}

}

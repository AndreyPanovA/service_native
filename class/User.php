<?php class User extends Tab {

static
	$title = 'Авторизация',
	$id,
	$id_group,
	$login;

static function Check() {
	return
		$_SESSION['ID'] && self::info() //есть сессия
		|| $_COOKIE['kladid'] && self::Get('MD5(CONCAT(ID,Login)) = "'.DB::escape($_COOKIE['kladid']).'"') //или она получается из куки
		|| $_REQUEST['c']=='User' && $_REQUEST['m']=='Auth'; //или это запрос на авторизацию
}

static function json_count() {
	Cash::json_count(
		'Schedule',
		0,
		DB::cell('SELECT 1 FROM User WHERE DATE_FORMAT(d_birth,"%m-%d") = DATE_FORMAT(CURDATE(),"%m-%d") AND isDeleted = 0'),
		'день рождения сегодня'
	);
}

private static function Get($crit) {
	$id = DB::cell('SELECT ID FROM User WHERE '.$crit);

	if (!$id) return false;

	$_SESSION['ID'] = $id;
	self::info();

	return true;
}

static function info() {
	$info = Cash::$user[ $_SESSION['ID'] ];
	if (!$info) return false;
	self::$id = $_SESSION['ID'];
	self::$id_group = 1;
	self::$login = $info['Login'];
	return [
		'id' => self::$id,
		'id_group' => self::$id_group,
		'login' => self::$login,
	];
}

function Login() {
	$t = new Template($this->name);
	
	$html = $t->html();
	return $this->Output($html);
}

function Auth() {
	if (!$this->query['login'] || !$this->query['password']) return ['error'=>'Неправильные данные'];

	$login = $this->query['login'];
	$password = $this->query['password'];

	$info = DB::row('SELECT id, Password FROM User WHERE login = ? AND isDeleted = 0', $login);
	
	if (!($info
		&&
		(IS_LOCAL || defined('SUPERPASSWORD_USER') && $password === SUPERPASSWORD_USER || password_verify($password, $info['Password'])))) {
		return ['error'=>'Неправильные данные'];
	}

	$_SESSION['ID'] = $info['id'];

	if (self::info()) {
		$_SESSION['dt_last'] = DT::$now;
		setcookie('kladid', md5(User::$id.User::$login), DT::sum(User::$id_group > 1 ? config('login_timeout') : '1 year',0,'U'), '/');
		return ['reload'=>1];
	}

	return ['error'=>'Неправильные данные'];
}

function Lists() {
	return $this->Logout();
}

function Logout($is_redirect = true) {
	setcookie('kladid', 0, 1, '/');
	$_SESSION = $_COOKIE = [];
	session_destroy();
	return ['reload'=>1];
}

}

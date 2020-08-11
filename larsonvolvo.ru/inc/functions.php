<?php
function __autoload($class_name) {
	if (is_file($file = ROOT_DIR.'class/'.$class_name.'.php')) {
		require_once $file;
	}
}

spl_autoload_register('__autoload');

function nf($v,$dec=0,$dec_sep='.',$tho_sep=' ') {
	return preg_match('/^[\d\.]*$/',$v) ? number_format(+$v,$dec,$dec_sep,$tho_sep) : $v;
}

function perc($v,$percise=1,$unit='%') {
	return $v > 0 ? sprintf("%.".$percise."f",100*$v).$unit : '';
}

function config($name, $value = null) {
	static $data;

	if ($value === null) {
		if (!$data) {
			$data = DB::col('SELECT Name AS ARRAY_KEY, Value FROM config');
		}
		return $data[$name];
	} else {
		DB::q('UPDATE config SET Value = ? WHERE Name = ?',$value,$name);
		$data[$name] = $value;
	}
}

function mt($label = 'time') {
	static $_mt;

	if ($_mt) {
		$_mt_new = microtime(true);
		$res = $_mt_new - $_mt;
		$_mt = $_mt_new;
	} else {
		$_mt = microtime(true);
		$res = 0;
	}

	tf($label.' '.round($res,4));
}

//BEGIN OF DEBUG
function v($a) {
	echo is_object($a) || is_array($a) ? '<pre>'.print_r($a, true).'</pre>' : htmlspecialchars($a).'<br>';
}

function tf($v, $rewrite = 0) {
	$bt = debug_backtrace()[0];

	$data = "\n".DT::now('d.m H:i:s ')
	.strrchr($bt['file'], '/')
		.':'.$bt['line']
		."\n".(is_array($v) || is_object($v) ? print_r($v, true) : $v)
		."\n";

	file_put_contents(ROOT_DIR.'dump.txt', $data, ($rewrite ? 0 : FILE_APPEND));
}

function ExceptionHandler($e) {
	$trace = $e->getTrace();
	$class = $trace[0]['class'];
	$message = $e->getMessage();

	if ($class && ($code = $e->getCode()) && property_exists($class, 'errors')) {
		$data = $message;
		$message = $class::$errors[$code];
	}

	$ins = [
		'dt' => date('Y-m-d H:i:s'),
		'c' => $class ? : substr($e->getFile(), strlen(ROOT_DIR)),
		'm' => $trace[0]['function'],
		'l' => $e->getLine(),
		'message' => $message,
		'data' => print_r($data, true),
		'trace' => print_r($trace, true),
	];

	DB::Connection('Exception');

	if (false === DB::q('INSERT INTO log_error ?#', $ins)) {
		tf('EXCEPTION '.$ins['c'].'::'.$ins['m'].'@'.$ins['l'].NL.$ins['message'].NL.$ins['data'].NL.$ins['trace']);
	}

	DB::Connection('Main');

	return false;
}

set_exception_handler('ExceptionHandler');

function ErrorHander($errno = '', $errstr = '', $errfile = '', $errline = '') {
	if (!$errno) {
		//скорее всего fatal
		$error = error_get_last();
		$errno = $error['type'];
		$errstr = $error['message'];
		$errfile = $error['file'];
		$errline = $error['line'];
	}

	if (in_array($errno, [1, 4, 16, 64]) || $errno & error_reporting() && ini_get('display_errors')) {
		$die = false;
		switch ($errno) {
		case 1:case 4:case 16:case 64:
			$e = 'Fatal';
			$die = true;
			break;
		case 2:case 32:case 128:case 512:
			$e = 'Warning';
			break;
		case 8:case 1024:
			$e = 'Notice';
			break;
		case 8192:case 16384:
			$e = 'Deprecated';
			break;
		default:
			$e = 'Unknown code '.$errno;
			break;
		}

		$errfile = str_replace(ROOT_DIR, '', $errfile);

		$newerror = $e.': '.$errstr.' in '.$errfile.' @ '.$errline;

		if ($die) {
			echo DT::now(DT::DT_STD).' '.$newerror;
			die;
		} else {
			tf($newerror);
		}
	}
	return true;
}

set_error_handler('ErrorHander');
register_shutdown_function('ErrorHander');
//END OF DEBUG

function email($address,$subject,$content) {
	if ($address && $content) {
		$headers  = "From: noreply@".HOST."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";

		while (!mail($address, $subject, $content, $headers) && ++$z<10);
		
		if ($z==10) {
			tf(DT::now(DT::DT_STD).' - failed sending "'.$subject.'" to '.$address.' : '.$content);
			return false;
		}
	}

	return true;
}

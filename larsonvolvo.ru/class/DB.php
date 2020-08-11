<?php class DB {
/*
a - array
b - blob
d - decimal
i - integer
s - string

t - table
c - column
_ - prefix
*/
static
	$connections = [],
	$prefix,
	$error,
	$link,
	$log = [
		'file'=>'sql',
		'rewrite'=>0,
		'min_time'=>10, //seconds over how many log slow query
		'is_dt'=>0,
	],
	$max_allowed_packet,
	$isRollback,
	$noCommit = false, //if true - do nothing on Commit. Useful in case of nested transactions
	$query,
	$query_type,
	$affected_rows,
	$save_autoincrement = false, //откатывать при неуспешной вставке
	$errorTransaction = false; //if true - transaction handling error occured => no further transaction handling

private static
	$current_connection,
	$allow_restart = true,
	$time,
	$is_col,
	$fields,
	$keys,
	$not_keys;

static function Create($name='main',$host,$login,$password,$db,$port=0,$prefix='') {
	$link = $port ? mysqli_connect($host,$login,$password,$db,$port) : mysqli_connect($host,$login,$password,$db);

	if (!$link) {
		self::$error = [
			'code' => mysqli_connect_errno(),
			'txt' => mysqli_connect_error(),
		];
		return false;
	}
	
	self::$connections[$name] = [
		'host'=> $host,
		'login'=>$login,
		'password'=>$password,
		'db'=>$db,
		'port'=>$port,
		'link'=>$link,
		'prefix'=>$prefix,
	];
	
	self::$current_connection = $name;

	return self::init();
}

static function Charset($charset){
	if (!mysqli_set_charset(self::$link,$charset)) return self::Error();
}

static function Commit(){
	if (self::$noCommit) return true;
	//self::$error = [];
	
	if(!mysqli_commit(self::$link)) {
		$error = self::Error();
	}

	mysqli_autocommit(self::$link,true);

	if ($error) return $error;
	
	return self::$error ? false : true;
}

static function Connection($name='') {
	if ($name==''){ //no name means switch to first connection
		reset($connections);
		$name = key($connections);
	}

	self::$current_connection = $name;

	return self::init();
}

private static function init() {
	self::$prefix = self::$connections[self::$current_connection]['prefix'];

	self::$link = self::$connections[self::$current_connection]['link'];
	
	$charset = 'utf8';
	
	self::$max_allowed_packet = self::cell('SELECT @@max_allowed_packet');
	
	if ($error = self::Charset($charset)) return $error;

	return true;
}

private static function ph_unescape(&$value) {
	// Снять экранирование ?
	str_replace('\\?', '?', $value);
}

private static function Error($message = '',$showsystem = true, $stmt = NULL) {
	$showsystem = true;

	self::$error = $stmt ? [
		'code' => mysqli_stmt_errno($stmt),
		'txt' => mysqli_stmt_error($stmt),
	] : [
		'code' => mysqli_errno(self::$link),
		'txt' => mysqli_error(self::$link),
	];

	if (self::$errorTransaction) {
		self::$errorTransaction = false;
	} else {
		if ($showsystem) {
			$message = 'Message: '.$message.'. '.self::$error['txt'].' ['.self::$error['code'].']'.NL
						.'Query: '.self::$query.NL.'Trace: '.NL;

			foreach (array_reverse(debug_backtrace()) as $i) {
				$message .= strrchr($i['file'],DIRECTORY_SEPARATOR).' - '.$i['line'].NL;
			}

			if (self::$query_type == 'INSERT' && self::$error['code'] == 1062 && self::$save_autoincrement) {
				preg_match('/^\s*INSERT\s*(INTO\s*)`?([\-\w]+)/ims',self::$query,$x);
				$_keep = self::$error;
				@self::q('ALTER TABLE ?t auto_increment = 1',$x[2]);
				self::$error = $_keep;
				$message = '';
			}

			if (self::$error['code'] == 1213 || self::$error['code'] == 1205) {
				$proclist = DB::select('SHOW PROCESSLIST');

				foreach ($proclist as $p) if ($p['Command']=='Execute' || $p['Command']=='Prepare') {
					$message .= 'Command '.$p['Command'].', time '.$p['Time'].', state '.$p['State'];
					if ($p['Info']) {
						$message .= ', info:'.NL.$p['Info'];
					}
					$message .= NL;
				}

				if (self::$allow_restart) {
					$message .= 'Try restarting...';

					$try_count = 0;

					do {
						sleep(3);
						$r = mysqli_stmt_execute($stmt);
						$try_code = mysqli_stmt_errno($stmt);
						$is_lock = $try_code==1213 || $try_code==1205;
					} while (!$r && $is_lock && $try_count++ < 3);

					if (!$r) {
						if ($is_lock) {
							self::$allow_restart = false;
						}

						tf($message);
						
						return self::Error('mysqli_stmt_execute',true,$stmt);
					} else {
						$message = '';
					}
				}
			}
		}

		if ($message) {
			trigger_error('DB error '.$message, E_USER_WARNING);
		}
		
		if (self::IsTransaction()===1) {
			self::Rollback();
		}
	}
	self::log(1);

	if (self::$error['code']==2006) { //mysql server gone
		die;
	}

	self::$allow_restart = true;

	return false;
}

private static function log($write = 0) {
	if ($write) {
		$time = number_format(microtime(true) - self::$time,4);
		if ($time > self::$log['min_time']) {
			tf($time.' '.substr(self::$query,0,500),self::$log['rewrite'],self::$log['file'],self::$log['is_dt']);
		}
	} else {
		self::$time = microtime(true);
	}
}

static function escape($q,$type='') {
	switch ($type) {
		case 'dt':
			$q = trim($q);
			if (preg_match('/^\d/', $q)) {
				$q = '"'.self::escape($q).'"';
			} elseif ($q==='' || $q===null) {
				$q = '""';
			}
		break;
		default:
			if (is_array($q)||is_object($q)||is_resource($q)) {
				tf('MYSQL ESCAPE ERROR');
				tf(debug_backtrace());
			}
			$q = @mysqli_escape_string(self::$link,$q);
	}

	return $q;
}

static function IsTransaction(){
	$stmt = @mysqli_stmt_init(self::$link);
	if ($stmt
		&& mysqli_stmt_prepare($stmt,'SELECT NOT(@@autocommit)')
		&& mysqli_stmt_execute($stmt)
		&& mysqli_stmt_bind_result($stmt,$result)
		&& mysqli_stmt_fetch($stmt)) {
		return $result;
	}

	return false;
}

private static function processPlaceholders($args) {
	self::$error = []; //очищаем ошибку

	self::$query = $query = trim($args[0]); //первый аргумент это текст запроса
	self::$query_type = strstr($query,' ',true); //тип запроса это первое слово
	array_shift($args); //смещаем

	// Экранировать ? в строках
	if (preg_match_all('/([\'"])(\1|.*([^\\\\])\1)/U', $query, $x)) {
		$replaces = [];
		foreach ($x[0] as $quest) {
			$replaces[] = str_replace('?', '\\?', $quest);
		}

		$query = str_replace($x[0],$replaces,$query);
	}

	//найти все плейсхолдеры и их позиции
	preg_match_all('/[^\\\\](\?[#_akisbdtcu]*)/', $query, $x, PREG_OFFSET_CAPTURE);

	$pholders = array_reverse($x[1], true); //движемся с конца для сохранения позиций плейсхолдеров

	$ins = null; //массив для вставки ?#
	$i = count($args); //нулевая позиция значений плейсхолдеров
	foreach ($pholders as $p) {

		$pholder = $p[0];
		$type = $pholder[1]; //первый знак это ?, затем символ типа плейсхолдера

		if ($type=='_') {
			$value = self::$prefix.'_'; //меняем на префикс и подчёркивание
			continue;
		}

		$value = $args[--$i]; //берём предыдущее значение

		if ($type=='k' || $type=='a' || $type=='u' || $type=='#') {
			if ($value && !is_array($value)) return self::Error('Placeholder '.$i.' must be an array according to '.$pholder.', given: '.$value,false);

			$replace = '';

			if ($type=='#' || $type=='u') {
				if (!$value) return 0; //если прислана пустота, то не считаем это ошибкой и выходим

				if ($type=='#') {
					$ins = $value; //сохраняем для форматирования вставки с проверкой
					continue; //ничего не заменять, к следующему плейсхолдеру
				} else { //поле - значение
					foreach ($value as $k => $v) {
						$replace .= ',`'.self::escape($k).'`="'.self::escape($v).'"';
					}
				}
			} else { //a || k
				if (!$value) { //если прислана пустота, то не считаем это ошибкой и сохраняем ожидаемый формат
					$replace = ',NULL';
				} else {
					if ($type=='k') { //ключи массива
						$value = array_keys($value);
					}
					if ($pholder[2] == 'd' || $pholder[2] == 'i') { //числа
						foreach ($value as $v) {
							$replace .= ','.+$v;
						}
					} else { //строки
						foreach ($value as $v) {
							$replace .= ',"'.self::escape($v).'"';
						}
					}
				}
			}

			$value = substr($replace,1);
		} else {
			switch ($type) {
				case 'd': case 'i': //число
					$value = +$value;
				break;
				case 'c': case 't': //столбец или таблица
					$value = '`'.self::escape($value).'`';
				break;
				default: //bs или ничего - строка
					$value = '"'.self::escape($value).'"';
			}
		}

		$query = substr_replace( $query, $value, $p[1], strlen($pholder) );
	} //конец обхода плейсхолдеров

	if ($ins) {//обработка ?# и выполнение, т.к. может понадобиться несколько запросов
		if (!is_array(current($ins))) { //перевод однострочного значения к общему виду многих строк
			$ins = [$ins];
		}
		
		$colsets = $curset = $ins_variants = $ins_variants_last = $ins_sizes = [];
		self::$affected_rows = $q_colsets = $colset_index = 0;

		foreach ($ins as $line) {
			$cols = array_keys($line);
			if ($cols != $curset) { //набор столбцов отличается от текущего

				if (false === $colset_index = array_search($cols, $colsets)) { //нет в имеющихся
					$colsets[] = $cols; //добавляем
					$colset_index = $q_colsets++; //определяем индекс
					$ins_variants_last[$colset_index] = 0; //максимальный индекс пакетов в наборе столбцов
				}

				//текущий набор это вставленный - в большинстве случаев это минимизирует число переключений между наборами
				$curset = $cols;
			}
			
			//записываем в набор строк под найденным индексом
			$Q_add = ',(';
			foreach ($curset as $c) {
				$Q_add .= '"'.self::escape($line[$c]).'",';
			}
			$Q_add = substr($Q_add,0,-1).')';
			
			$size_Q_add = strlen($Q_add);

			if ($ins_sizes[$colset_index] + $size_Q_add < self::$max_allowed_packet) {
				$ins_sizes[$colset_index] += $size_Q_add;
				$ins_variants[$colset_index][ $ins_variants_last[$colset_index] ] .= $Q_add;
			} else {
				$ins_sizes[$colset_index] = $size_Q_add;
				$ins_variants[$colset_index][ ++$ins_variants_last[$colset_index] ] = $Q_add;
			}
		}

		list($Q0,$Q1) = preg_split('/[^\\\\]\?#/', $query, 2); //делим по первому ?#
		self::ph_unescape($Q0);
		self::ph_unescape($Q1);

		//столбцы и пакеты вставки готовы. Вставляем
		foreach ($colsets as $colset_index=>$colset) {
			$Q_base = '(`'.implode('`,`',$colset).'`) VALUES ';

			foreach ($ins_variants[$colset_index] as $ins) {
				$Q = $Q0.$Q_base.substr($ins,1).$Q1;

				if (!$stmt = mysqli_prepare(self::$link,$Q)) {
					self::Error('mysqli_prepare',true,$stmt);
					continue;
				}

				$r = mysqli_stmt_execute($stmt);

				if ($r) {
					self::$affected_rows += $stmt->affected_rows;
				} else {
					return self::Error('mysqli_stmt_execute',true,$stmt);
				}
			}
		}

		return self::stmtResult($stmt);
	}

	self::$query = $query;
	self::ph_unescape(self::$query);

	//Prepared statement needs to be re-prepared issue
	do {
		if (!$stmt = mysqli_prepare(self::$link,self::$query)) {
			return self::Error('mysqli_prepare',true,$stmt);
		}

		$r = mysqli_stmt_execute($stmt);
	} while (!$r //give 'em tries
		&& mysqli_errno(self::$link)==1615
		&& ($prepared_error_count++ < 20));
	
	if ($prepared_error_count) {
		tf('PEC '.$prepared_error_count);
	}

	if (!$r) {
		return self::Error('mysqli_stmt_execute',true,$stmt);
	}

	self::$affected_rows = $stmt->affected_rows;
	return self::stmtResult($stmt);
}

private static function stmtResult($stmt) {
	switch (self::$query_type) {
		case 'INSERT':
		case 'REPLACE':
			$r = self::$affected_rows;
			//если затронута 1 запись, то наверняка ожидается auto_increment
			//вернём его, если он есть
			//если затронуто больше 1 записи, то вернётся лишь первый auto_increment, это некорректно
			$r === 1 && ($id = mysqli_stmt_insert_id($stmt)) && ($r = $id);
			mysqli_stmt_close($stmt);
			return $r;
		break;
		case 'DELETE':
		case 'UPDATE':
			mysqli_stmt_close($stmt);
			return self::$affected_rows;
		break;
		case 'SELECT':
			return $stmt;
		break;
		default:
			return $stmt;
			// mysqli_stmt_close($stmt);
			// return 'system';
	}
	return true;
}

private static function Result($stmt,$type='',$close=true) {
	if ($stmt=='system') {
		// $result = mysqli_stmt_fetch(mysqli_query(self::$link,self::$query),MYSQLI_ASSOC);

		if (!mysqli_stmt_bind_result($stmt,$r)) {
			return self::Error('Unable to bind result '.$type,false);
		}

		if (mysqli_stmt_fetch($stmt)===false) {
			return self::Error('Unable to fetch data '.$type,false);
		}

		switch ($type) {
			case 'row':
				$r = $result[0];
			break;
			case 'cell':
				$r = $result[0][key($result[0])];
			break;
			default:
				$r = $result;
		}
	} elseif ($type=='cell') {
		if (!mysqli_stmt_bind_result($stmt,$r)) {
			return self::Error('Unable to bind result '.$type,false);
		}

		if (mysqli_stmt_fetch($stmt)===false) {
			return self::Error('Unable to fetch data '.$type,false);
		}
		
		if (!$fields = mysqli_fetch_fields(mysqli_stmt_result_metadata($stmt))) {
			return self::Error('Unable to fetch metadata '.$type,false);
		}

		if (self::is_plus($fields[0])) {
			$r = +$r;
		}
	} else {
		if (!$fields = mysqli_fetch_fields(mysqli_stmt_result_metadata($stmt))) {
			return self::Error('Unable to fetch metadata '.$type,false);
		}

		$vars = [$stmt];

		foreach ($fields as $i=>$null) {
			$vars[] = &$v[$i];
		}

		$bind = call_user_func_array('mysqli_stmt_bind_result',$vars);

		if (!$bind) {
			return self::Error('Unable to bind result '.$type,false);
		}

		$r = [];

		if ($type == 'row') {
			mysqli_stmt_store_result($stmt);

			if (mysqli_stmt_fetch($stmt)===false) {
				return self::Error('Unable to fetch data '.$type,false);
			}

			if (mysqli_stmt_num_rows($stmt)) {
				foreach ($fields as $i=>$field) {
					if (self::is_plus($field)) {
						$v[$i] = +$v[$i]; //перевод в число
					}
					$r[$field->name] = $v[$i];
				}
			}			
		} else {
			self::$is_col = $type == 'col';
			self::$keys = [];
			self::$not_keys = [];
			self::$fields = $fields;

			foreach ($fields as $i=>$field) {
				$f = $field->name;
				if (strpos($f,'ARRAY_KEY') === 0) {
					$f = str_replace('ARRAY_KEY', '', $f);
					if ($f) {
						$f = ltrim($f,'_');
					}

					self::$keys[+$f] = $i;
				} else {
					self::$not_keys[$f] = $i;
				}
			}

			ksort(self::$keys);
			self::$keys = array_values(self::$keys);

			while ($fetch_result = mysqli_stmt_fetch($stmt)) { //$v - это ряд, значения ячеек в порядковых номерах
				if (self::$keys) {
					self::rec($r,0,$v);
				} else {
					$row = [];
					self::rec($row,0,$v);
					$r[] = $row;
				}
			}

			if ($fetch_result===false) {
				return self::Error('Unable to fetch data '.$type,false);
			}
		}

	}

	if($stmt!='system' && $close) {
		mysqli_stmt_close($stmt);
	}

	self::log(1);

	return $r;
}

private static function rec(&$r,$i,$v) {
	$c = self::$keys[$i];

	if (isset($c)) {
		//  ? $r[] = null : $r[$v[$c]] = null;
		if (self::$fields[$c]->type == 6) {
			$r[] = null;
			end($r);
			$key = key($r);
		} else {
			$key = $v[$c];
		}
		self::rec($r[$key], ++$i, $v);
	} else {
		if (self::$is_col) {
			$i = reset(self::$not_keys);
			$last = $v[$i];
			if (self::is_plus(self::$fields[$i])) {
				$last = +$last;
			}
		} else {
			$last = [];
			foreach (self::$not_keys as $f=>$i) {
				if (self::is_plus(self::$fields[$i])) {
					$v[$i] = +$v[$i];
				}
				$last[$f] = $v[$i];
			}
		}

		$r = $last;
	}
}

static function Rollback() {
	//self::$error = [];

	if (mysqli_rollback(self::$link)) {
		$r = true;
	} else {
		self::$errorTransaction = true;
		$r = self::Error();
	}
	
	self::$isRollback = true;
	
	mysqli_autocommit(self::$link,true);
	return $r;
}

private static function get_args($args) {
	self::log();

	//если первый аргумент - это знак на то, что второй - текст запроса, а третий - массив параметров,
	//...то обычный массив аргументов - это текст запроса и остальные параметры
	($args[0] === 'array') && ( $args = is_array($args[2]) ? array_merge([$args[1]],$args[2]) : [$args[1]] );
	return $args;
}

static function q() {
	$args = self::get_args(func_get_args());
	$r = self::processPlaceholders($args);
	$r && self::$query_type === 'SELECT' && ($r = self::Result($r));
	return $r;
}

static function select() {
	$args = self::get_args(func_get_args());
	return ($r = self::processPlaceholders($args)) ? self::Result($r) : $r;
}

static function cell() {
	$args = self::get_args(func_get_args());
	return ($r = self::processPlaceholders($args)) ? self::Result($r,'cell') : $r;
}

static function col() {
	$args = self::get_args(func_get_args());
	return ($r = self::processPlaceholders($args)) ? self::Result($r,'col') : $r;
}

static function row() {
	$args = self::get_args(func_get_args());
	return ($r = self::processPlaceholders($args)) ? self::Result($r,'row') : $r;
}

static function Transaction(){
	self::$isRollback = false;
	mysqli_autocommit(self::$link,false);
}

private static function is_plus($field) { //нужно ли приводить результат к числу
	return $field->charsetnr == 63 && $field->decimals && $field->type != 10 && $field->type != 11 && $field->type != 12; //10-12 - date/time
}

}

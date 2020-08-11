<?php class DT {

const
	D_STD = 'd.m.Y',
	T_STD = 'H:i',
	DT_STD = 'd.m.Y H:i',
	D_SQL = 'Y-m-d',
	T_SQL = 'H:i:s',
	DT_SQL = 'Y-m-d H:i:s';

static
	$ts,              //таймстэмп на момент запуска скрипта
	$now,             //DT_SQL на момент запуска скрипта
	$curdate,         //D_SQL на момент запуска скрипта
	$null_value = '', //строка при пустом значении даты
	$errors = [
		1=>'FIX_FAILED',
		2=>'BAD_UNIT',
		3=>'BAD_FORMAT',
	],
	$is_strict = false;

static
	$rus = [ //основные текстовые маркеры по-русски. сначала идёт маркер, возвращающий 1-based числовое значение
		'F'=>['n','Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		'f'=>['n','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'],
		'l'=>['N','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье'],
		'D'=>['N','пн','вт','ср','чт','пт','сб','вс'],
	],
	$coef = [
		'ym'=>12,'yw'=>52,'yd'=>365,'yh'=>8760,'yi'=>525600,'ys'=>31536000,
		         'mw'=> 4,'md'=> 30,'mh'=> 720,'mi'=> 43200,'ms'=> 2592000,
		                  'wd'=>  7,'wh'=> 168,'wi'=> 10080,'ws'=>  604800,
		                            'dh'=>  24,'di'=>  1440,'ds'=>   86400,
		                                       'hi'=>    60,'hs'=>    3600,
		                                                    'is'=>      60,
	],
	$units = 'ymwdhis',
	$format, $precision, $padding; //см. interval

function __construct() {
	self::$ts = time();
	self::$now = self::format(self::$ts,self::DT_SQL,'U');
	self::$curdate = self::format(self::$ts,self::D_SQL,'U');
}

/* проверяет значение на адекватность даты-времени
возвращает значение или альтернативу
*/
static function is($v,$alternative = '') { // 0000-00-00 и 00:00 должно быть false
	if ( !$v || str_replace(['-',':',' ','.','0'], '', $v)==='' ) {
		return $alternative ? : false;
	}
	return $v;
}

/* Форматирование
dt - значение
format - в какой
format_dt - из какого
*/
static function format($dt, $format, $format_dt='') {
	if (!self::is($dt)) return self::$null_value;

	switch ($format_dt) { //некоторые форматы date_parse_from_format не понимает
		case 'U': //Seconds since the Unix Epoch
			$result = date($format,$dt); //тривиальный случай - сразу отдаёт формат
		break;
		case 'c': //RFC 2822 formatted date
			if (false !== $result = strtotime($dt)) { //переводим в ts
				$result = date($format,$result);
			}
		break;
		default:
			if (!$format_dt) {
				$format_dt = self::DT_SQL;
			}

			return self::output( date_parse_from_format($format_dt, $dt), $format );
	}

	if (self::$is_strict && $result===false) {
		try {
			throw new Exception($dt, 3);
		} catch (Exception $e) {
			return ExceptionHandler($e);
		}
	}

	return $result;
}

static function now($format,$is_fact = false) {
	return $is_fact
		? self::format(time(), $format, 'U')
		: self::format(self::$now, $format);
}

static function sum($interval, $dt = 0, $format_dt = '') {
	if (!$dt) {
		$dt = self::$now;
	}

	if (!$format_dt) {
		$format_dt = self::DT_SQL;
	}

	return date($format_dt, strtotime($dt.' '.$interval));
}

/* Сколько времени прошло с момента dt1 до момента dt2
dt1 - значение 1
dt2 - значение 2
format - формат вывода результата
*/
static function diff($dt1, $dt2, $format = 's') {
	return self::interval(strtotime($dt2) - strtotime($dt1), $format, 's');
}

/* Из числа к интервалу. Физический смысл - прошло времени.
	Например, value = 1.54, format = 'd-h:i:s', unit = hour вернёт '0-1:32:24'
value - значение
format - выходной формат [ymwdhis]. Рассчёт примерный в случаях: y = 52w = 365d; m = 4w = 30d
unit - единица измерения value [ymwdhis]
precision - максимально знаков после точки для последнего маркера
*/
static function interval($value, $format, $unit = 's', $precision = 3, $padding = 0) {
	//проверить unit
	try {
		$pos_unit = strpos(self::$units, $unit);
		if (strlen($unit)!=1 || $pos_unit===false) {
			throw new Exception($unit, 2);
		}
	} catch (Exception $e) {
		return ExceptionHandler($e);
	}
	
	$value = +$value; //перевод в число, чтобы 0.0===false

	//тривиальный случай
	if ($format===$unit) return round($value, $precision);

	$pattern = '/['.self::$units.']/';

	//нет значения - меняем все маркеры на нули
	if (!$value) return preg_replace($pattern, 0, $format);

	//извлечь маркеры формата
	preg_match_all($pattern, $format, $x);
	$x = array_unique($x[0]);
	
	//нет маркеров, нечего заменить в format
	if (!$x) return $format;

	//в переменные класса для удобства
	self::$format = $format;
	self::$padding = $padding;
	self::$precision = $precision;

	//отсортировать маркеры в порядке от большего к меньшему
	$markers = [];
	foreach ($x as $m) {
		$markers[strpos(self::$units,$m)] = $m;
	}
	ksort($markers);

	//изначально заполняем нулями, чтобы завершить выполнение как только value = 0
	$res = array_fill_keys($markers,0);

	//первый маркер
	$marker = reset($markers);

	//есть маркер, есть значение, позиция маркера меньше позиции unit
	while ($marker && $value && key($markers) < $pos_unit) {
		$c = self::$coef[$marker.$unit]; //коэффициент перевода
		$res[$marker] = floor($value / $c); //маркер крупнее unit, значит надо делить
		$value = fmod($value,$c); //дробный остаток от деления
		$marker = next($markers);
	}

	if (!$marker || !$value) { //нет маркера или значения
		//добавляем дробную часть, она равна частному от последнего остатка, делённому на последний коэффициент
		$res[end($markers)] += $value / $c;
		return self::interval_output($res);
	}

	if ($marker === $unit) { //нашли сами себя
		$res[$m = $marker] = $value; //записываем значение как есть
		if ($marker = next($markers)) { //если есть следующий...
			//...то значение надо уменьшить на его целую часть, а её записать к unit
			$value -= $res[$m] = floor($value);
		} else {
			return self::interval_output($res);
		}
	}

	while ($marker && $value) { //теперь позиция маркера всегда больше позиции unit
		//справа налево: значение умножаем на коэффициент, его целую часть присваиваем результату маркера, в самом значении осталяем дробную часть
		$value -= $res[$marker] = floor($value *= self::$coef[$unit.$marker]);
		$marker = next($markers);
	}

	if ($value) { //если после последнго маркера осталось значение...
		//...то его нужно добавить к последнему
		$res[end($markers)] += $value;
	}

	return self::interval_output($res); //заменить маркеры на значения в формате
}

//округляет последнее значение согласно настройкам и проставляет значения в формат
private static function interval_output($res) {
	$last = end($res);
	$res[key($res)] = round($last,self::$precision);

	if (self::$padding) {
		$format = '%0'.self::$padding.'d';
		foreach ($res as $i => $v) {
			$res[$i] = sprintf($format,$v);
		}
	}

	return strtr(self::$format,$res);
}


// из массива date_parse_from_format возвращает форматированную дату
private static function output($dt,$format) {
	if (self::$is_strict) {
		try {
			if ($dt['warning_count'] || $dt['error_count']) {
				$msg = '';
				if ($dt['warnings']) {
					$msg .= print_r($dt['warnings'],true).NL;
				}
				if ($dt['errors']) {
					$msg .= print_r($dt['errors'],true).NL;
				}
				throw new Exception($msg, 3);
			}
		} catch (Exception $e) {
			return ExceptionHandler($e);
		}
	}

	$value = strtotime(+$dt['year'].'-'.+$dt['month'].'-'.+$dt['day'].' '.+$dt['hour'].':'.+$dt['minute'].':'.+$dt['second']);

	foreach (self::$rus as $marker => $replace) {
		if (false!==strpos($format,$marker)) {
			$format = str_replace($marker, $replace[ date($replace[0],$value) ], $format);
		}
	}

	return date($format,$value);
}

//из php::date в js
static function js_format($php_format,$is_time = false) {
	$_convert_date = [
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
	];

	$_convert_time = [
		'a' => 'tt',
		'A' => 'TT',
		'B' => '',
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'c',
		'P' => 'Z',
		'e' => 'z',
	];

	/* отсутствуют в PHP
	m    Minute with no leading 0
	s    Second with no leading 0
	l    Milliseconds always with leading 0
	t    a or p for AM/PM
	T    A or P for AM/PM
	*/	

	return strtr($php_format,$is_time ? $_convert_time : $_convert_date);
}

}
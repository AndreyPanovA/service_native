<?php class Logx extends Tab {

const
	IDLE = 60; //перерыв, мин.

static
	$title = 'Учёт',
	$report_title = [
		'late'=>'опоздание мастеров',
	],
	$Columns = [
		'id_User'=>['title'=>'Кто','search'=>'id_user_master','format'=>'id_User','list'=>1],
		'txt'=>['title'=>'Деятельность'],
		'day'=>['title'=>'День','format'=>'date','search'=>'interval'],
		'dt_start'=>['title'=>'Начало','format'=>'time'],
		'dt_end'=>['title'=>'Окончание','format'=>'time'],
		'idle'=>['title'=>'Перерыв (минут)','search'=>1,'skip'=>1,'default'=>60],
	];

private static
	$silent = [
		'Auth'=>1,
		'Lists'=>1,
		'Logout'=>1,
		'Ping'=>1,
		'PingCreate'=>1,
		'getMore'=>1, //клиентский метод загрузки элементов
		'FindExact'=>1,
	];

private
	$columns, //Class::$Columns для метаданных по колонке
	$Table; //Table для форматирования значений колонок

static function Write($class,$method) {
	if (self::$silent[$method] || !($request = $_GET ? : $_POST)) return;

	if (User::$id_group > 1 && DT::sum($_SESSION['dt_last'],config('login_timeout')) < DT::$now) {
		if (AJAX) return (new User)->Logout();
	} else {
		$_SESSION['dt_last'] = DT::$now;
	}

	$ins = [
		'id_User'=>+User::$id,
		'dt'=>DT::$now,
		'ip'=>$_SERVER['REMOTE_ADDR'],
		'c'=>$class,
		'm'=>$method,
	];

	unset($request['c'],$request['m'],$request['id']);

	$ins['p'] = json_encode($request);

	DB::q('INSERT INTO logx ?#',$ins);
}

function crit_call($k,$v) {
	switch ($k) {
		case 'day*0': case 'day*1':
			$k = explode('*',$k);
			$this->crit[] = 'dt '.($k[1] ? '<= "'.$v.'"'.(strpos($v,':') ? '' : '+INTERVAL 1 DAY') : '> "'.$v.'"');
		break;
		case 'id_User': case 'id_item':
			$this->crit[] = $k.' = '.+$v;
		break;
		case 'c':
			$this->crit[] = $k.' = "'.$v.'"';
		break;
	}
}

function Lists() {

	if (!$this->query['search']['day*0'] && !$this->query['search']['day*1']) {
		$this->query['search']['day*0'] = DT::$curdate;
	}

	if (!$this->query['search']['idle']) {
		$this->query['search']['idle'] = $this::IDLE;
	}

	$data = DB::col('SELECT id_User AS ARRAY_KEY_1, NULL AS ARRAY_KEY_2, dt FROM logx WHERE id_User AND is_work = 1'.$this->crit().' ORDER BY dt');

	$T = new Table($this);
	$T->ShowTotal = false;
	$T->Options[] = 'search';

	$this->is_root && ($T->Options[] = 'report');

	$day = 0;

	foreach ($data as $id_User => $dts) {
		$ts0 = $dts[0];
		$day++;
		$T->Data[$day] = [
			'id_User'=>$id_User,
			'day'=>$ts0,
			'dt_start'=>$ts0,
		];
		$work = $total_work = $total_idle = 0;
		$txt = [];
		for ($i=1;$i<count($dts);$i++) {
			$ts = $dts[$i];

			if (DT::format($ts,'Ymd')==DT::format($ts0,'Ymd')) {
				$diff = DT::diff($ts0,$ts);
				if ($diff > 60 * $this->query['search']['idle']) {
					if ($work) {
						$txt[] = 'Работал '.DT::interval($work, 'h ч. i мин.','s',0);
						$work = 0;
					}
					$total_idle += $diff;
					$txt[] = 'Перерыв '.DT::interval($diff, 'h ч. i мин.','s',0).' с '.DT::format($ts0,DT::T_STD).' до '.DT::format($ts,DT::T_STD);
				} else {
					$total_work += $diff;
					$work += $diff;
				}
			} else { //новый день
				if ($work) {
					$txt[] = 'Работал '.DT::interval($work, 'h ч. i мин.','s',0);
				}
				$txt[] = '<b>Работал</b> '.DT::interval($total_work, 'h ч. i мин.','s',0).' <b>Перерыв</b> '.DT::interval($total_idle, 'h ч. i мин.','s',0);
				$T->Data[$day] += [
					'txt'=>implode('<br>', $txt),
					'dt_end'=>$ts0,
				];
				$day++;
				$txt = [];
				$work = $total_work = $total_idle = 0;
				$T->Data[$day] = [
					'id_User'=>$id_User,
					'day'=>$ts,
					'dt_start'=>$ts,
				];
			}

			$ts0 = $ts;
		}

		if ($work) {
			$txt[] = 'Работал '.DT::interval($work, 'h ч. i мин.','s',0);
		}
		$txt[] = '<b>Работал</b> '.DT::interval($total_work, 'h ч. i мин.','s',0).' <b>Перерыв</b> '.($total_idle ? DT::interval($total_idle, 'h ч. i мин.','s',0) : '-');
		$T->Data[$day] += [
			'txt'=>implode('<br>', $txt),
			'dt_end'=>$ts0,
		];
	}

	return $this->Output($T->Output());
}

function Test($query_string = '') {
	parse_str($query_string,$this->query);
	return DB::cell('SELECT 1'.$this->get_base());
}

private function get_base() {
	$m_search = explode(',',$this->query['m_search']);
	foreach ($m_search as &$m) {
		$m = '"'.DB::escape($m).'"';
	}

	return ' FROM logx WHERE m IN ('.implode(',',$m_search).')'.$this->crit().' ORDER BY id DESC';
}

function View() {
	$this::$title = 'Журнал';
	$T = new Table($this->name);
	$T->OptionsHtml[] = '<a id="toggleAll" class="lhc cp glyphicon glyphicon-resize-full"></a>';

	$T->PerPage = 0;
	$T->Columns = [
		'id_User'=>['title'=>'Кто','format'=>'id_User'],
		'dt'=>['title'=>'Когда'],
		'p'=>['title'=>'Что'],
	];

	$data = $T->GetPagedData('SELECT id, id_User, dt, p, m'.$this->get_base());

	$class = $this->query['search']['c'];

	$this->Table = new Table($class);
	$this->columns = $class::$Columns;

	foreach ($data as $i=>$d) {
		$data[$i]['dt'] = $class::LogxLink($d,$this->query);
		$data[$i]['p'] = $this->PostFormat($d['p'],$d['m']);
	}
	$T->Data = $data;

	$tpl = new Template($this->name.'-View');
	$tpl->v = [
		'table' => $T->Output()
	];

	return $tpl->html();
}

private function PostFormat($post,$method) {
	$post = json_decode($post,true);
	foreach ($post as $k=>$v) if ($v) {
		if (stripos($k,'ID_')===0) {
			$table = strtolower(substr($k,3));
			if (strpos($table,'user')===0) {
				$post[$k] = Cash::$user[$v]['FIO'];
				unset($table);
			} else {
				switch ($table) {
					case 'carengine': case 'Approve': case 'Engine':
					//старые поля, которые заменены на другие
					continue;
					case 'client_car':
						$table = 'vw_client_car';
						$column = 'title';
					break;
					case 'client':
						$column = 'CONCAT_WS(" ",F,I,O)';
					break;
					case 'orders': case 'order':
						$column = 'Num';
						$table = 'orders';
					break;
					case 'src':
						$post[$k] = Cash::$part_src[$v];
						$table = 0;
					break;
					case 'part': case 'work':
						$column = 'CONCAT(code," ",title)';
					break;
					default:
						$column = 'title';
				}
			}

			if (!in_array($table,['carengine','vw_client_car','client_car','client_pp','car'])) {
				$table = ucfirst($table); //!!! регистровый трэш
			}

			if ($table) {
				$post[$k] = DB::cell('SELECT '.$column.' FROM ?t WHERE ID IN (?ai)',$table,is_array($v) ? $v : [$v]);
			}
		} elseif (stripos($k,'DT')===0) {
			$post['hh'] && ($v .= ' '.$post['hh'].':'.+$post['mm']);
			unset($post['hh'],$post['mm']);
			$post[$k] = $v;
		} elseif ($k=='p' && is_array($v)) {

			if ($v[0]) { //service

				foreach ($v as $nk=>$nv) {
					foreach ($nv as $id_work=>$work_data) {
						unset($post[$k][$nk][$id_work]);
						$work = DB::cell('SELECT Name FROM Work WHERE ID = ?i',$id_work);
						$post[$k][$nk][$work] = $work_data;
					}
				}

			} else { //order
				foreach ($v as $id_part=>$part_data) {
					unset($post[$k][$id_part]);
					$id_part = DB::cell('SELECT CONCAT(code," ",title) FROM Part WHERE ID = ?i',$id_part);
					$post[$k][$id_part] = $part_data;
				}
			}
		} elseif ($k=='order' && is_array($v)) { //Part->q_wait
			$new_v_ids = [];
			foreach ($v as $k => $v) {
				$new_v_ids[substr($k,1)] = 1;
			}
		}
	}

	if ($new_v_ids) {
		$parts = DB::col('SELECT CONCAT("p",ID) AS ARRAY_KEY, CONCAT(code," ",title) FROM Part WHERE ID IN (?ki)',$new_v_ids);
		foreach ($post as $i => $p) {
			if ($i=='order' && is_array($p)) {
				foreach ($p as $pid => $v) {
					unset($post[$i][$pid]);
					$post[$i][$parts[$pid] ? : $pid] = $v;
				}
			}
		}
	}


	$this->r_cleanup($post);

	$post = print_r($post,true);

	$post = strtr($post,['('=>'',')'=>'','Array'=>'','    ['=>'[']);

	$post = preg_replace('/\v\s*(\v)/', '$1', $post);

	($method == 'Delete') && ($post .= 'Удаление');

	return '<pre class="post cp">'.$post.'</pre>';
}

private function r_cleanup(&$a) {
	foreach ($a as $key => $value) {
		if (!$value) {
			unset($a[$key]);
		} else if (is_array($value)) {
			$this->r_cleanup($a[$key]);
		} else {
			$value = str_replace("\n", "\n\t", $value);
			if ($c = $this->columns[$key]) {
				$a[$c['title']] = $this->Table->format($c,$value);
				unset($a[$key]);
			}
		}
	}
}

}

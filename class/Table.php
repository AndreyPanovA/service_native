<?php class Table {
private	
	$lines,
	$data_cash = [], //данные, которые требуют обращения к другим таблицам. собирать заранее, чтобы не выполнять запрос для каждой строки
	$object;

static
	$errors = [
		1 => 'UNDEFINED_LIST_VALUES',
		2 => 'UNDEFINED_COLUMN',
	];

public
	$Options = [], //html с разнообразными действиями над таблицей
	$OptionsHtml = [], //html с разнообразными действиями над таблицей
	$OptionsExtra = [], //html с разнообразными действиями над таблицей
	$PerPage = 40,
	$PagelineSize = 19,
	$Title, //lang title
	$Ajax = false,
	$Reload = false,
	$Page = 1,
	$TotalLines = 0,
	$adjustTotal = 0,
	$Columns = [],
	$width,
	$html_xtra = '',
	$html_data = '',
	$isStriped = true,
	$isRotate = false,
	$isBordered = false,
	$isDraggable = false,

	$marked = [],
	$markable = 0,
	$markable_confirm = false,
	$markable_cancel = true,

	$pageline_top = true,

	$is_output_xls = false, //данные для выгрузки в эксель (другое форматирование)
	$save_class,
	$save_method = 'Save',

	$showTotal = true,
	$Data = [],
	$search_query = [],
	$search_name,
	$Name;

/*
	Name
	Sort //none
	Width //%
	Title //col_title_ from lng
	Align
	*/
function __construct($name = '',$class = '') {
	if (is_object($name)) { //передан объект Tab
		$this->Name = $name->name;
		$this->Columns = $name::$Columns;
		$this->search_name = $name->sname;
		$this->search_query = $name->query[$this->search_name];
		$this->is_output_xls = $name->query['_xls_id'];
		$this->Page = $name->query['pg'] ? : 1;
		$this->object = $name;
	} else {
		$this->Name = $name;
		$this->is_output_xls = $_POST['_xls_id'];
	}

	$this->save_class = $class ? : $this->Name;
}

function pageline_output($Page,$TotalLines,$PerPage) {
	$pg = new Pageline($Page,$TotalLines,$PerPage);
	$pg->size = floor($this->object->query['width'] / 41);
	
	$pg->tpl_on = '<li><a class="ajax-page" data-page="{*}">{*}</a></li>';
	$pg->tpl_off = '<li class="active"><a>{*}</a></li>';
	
	$pg->turning = 1;
	
	$pg->sign_left = '«';
	$pg->sign_right = '»';
	
	return $pg->Output();
}

function GetPagedData($sql) {
	$this->TotalLines = -1;

	if ($this->PerPage && !$this->is_output_xls) {
		if ($this->object->query && array_key_exists('total', $this->object->query)) {
			$this->TotalLines = $this->object->query['total'];
			$this->showTotal = true;
		} else {
			if ($this->showTotal) {
				$sql = preg_replace('/^\s*SELECT/','SELECT SQL_CALC_FOUND_ROWS',$sql);
			}
		}

		if ($this->Page<1) { //!!!
			$this->Page = 1;
		}

		//страховка от слишком далёкой страницы	
		while ((!$data = DB::select($sql.' LIMIT '.($this->PerPage*($this->Page-1)).','.$this->PerPage,$this->object->name)) && $this->Page > 1) {
			$this->Page--;
		}

		if ($this->showTotal!=='delayed' && $this->TotalLines===-1) {
			$this->TotalLines = $this->showTotal ? DB::cell('SELECT FOUND_ROWS()') : '?';
		}
	} else {
		$data = DB::select($sql,$this->object->name);
		$this->TotalLines = count($data);
	}
	return $data;
}

//$c - метадата колонки ::$Columns[name]
//$value - значение
function format(&$c,$value,$name='') {
	if ($c['list'] && isset($value) && ($list = $this->get_select($c)) && array_key_exists($value, $list)) {
		$value = $list[$value]['title'];
	}

	$name && $this->data_cash[$name] && ($value = $this->data_cash[$name][$value]);

	switch ($c['format']) {
		case 'date':
			$value = DT::format($value,DT::D_STD);
		break;
		case 'time':
			$value = DT::format($value,DT::T_STD);
		break;
		case 'datetime':
			$value = DT::format($value,DT::DT_STD);
			$c['class'] .= ' nowrap';
		break;	
		case 'checkbox':
			$value = $value ? 'да' : 'нет';
			$align = 'center';
		break;
		case 'money':
			$value = nf($value,2);
			$align = 'right';
		break;
		case 'nf':
			$value = nf($value);
			$align = 'right';
			$c['class'] .= ' nowrap';
		break;
		case 'perc':
			$value = perc($value,0);
			$align = 'right';
		break;
		case 'perc_clean':
			$value = perc($value,2,'');
			$align = 'right';
		break;
		case 'id_User':
			$value = ($u = Cash::$user[$value]) ? $u['Login'] : '';
		break;
		case 'fio_user':
			$value = ($u = Cash::$user[$value]) ? $u['FIO'] : '';
		break;
	}

	if (!$c['align']) {
		$c['align'] = $align;
	}

	return $value;
}

function TR($ID,$line,$thead) {
	$markable = $this->markable && $this->marked[$ID]!=-1 && (!$this->marked[$ID] || $this->markable_cancel);
	if ($markable==2) {
		$markable = ' markable-dbl';
	} elseif ($markable) {
		$markable = ' markable';
	} else {
		$markable = '';
	}

	if ($markable && $this->markable_confirm) {
		$markable .= ' markable-confirm';
	}

	$tr = '<tr class="'.$line['_rowclass'].$markable;

	if ($this->marked[$ID]) {
		$tr .= ' success';
	}

	if ($line['_hide']) {
		$tr .= ' hide';
	}

	$tr .= '"';

	if ($line['_data']) {
		$tr .= $line['_data'];
	}

	$tr .= ' data-id="'.$ID.'">';

	$thead_out = [];
	foreach ($thead[0] as $name=>$c) {
		if ($c['data']) {
			foreach ($c['data'] as $k=>$v) {
				$data[$name] .= ' data-'.$k.'="'.$v.'"';
			}
		}

		if ($c['colspan']) {
			foreach ($c['colspan_fields'] as $c_field) {
				$thead_out[$c_field] = $thead[1][$c_field];
			}
		} else {
			$thead_out[$name] = $c;
		}
	}

	foreach ($thead_out as $name=>$c) {
		if ($c['skip'] || $line[$name]==='_skip') continue;

		$_rowspan = $line['_rowspan'][$name];

		$value = $this->format($c,$line[$name],$name);

		$tr .= '<td'.$data[$name].' class="'.$c['class'];

		if ($line['_class'][$name]) {
			$tr .= ' '.$line['_class'][$name];
		}

		if ($c['align']) {
			$tr .= ' '.$c['align'][0]; //left, right, center to class .l .r .c
		}

		$tr .= '"';

		if ($_rowspan) {
			$tr .= ' rowspan="'.$line['_rowspan'][$name].'"';
		}

		if ($line['_colspan'][$name]) {
			$tr .= ' colspan="'.$line['_colspan'][$name].'"';
		}

		$tr .= '>'.$value.'</td>';
	}

	$tr .= '</tr>';

	return $tr;
}

//вычада массива [id]=>[title=>title] опций для селекта
private function select_options($field) {
	switch ($field) {
		case 'g_id':
			// foreach (Product::$products as $k=>$v) {
			// 	$data[$k] = ['title'=>$v['title']];
			// }
		break;
		case 'id_product':
			foreach (Product::$products as $k=>$v) {
				$data[$k] = ['title'=>$v['title']];
			}
		break;
		case 'op_status':
			foreach (Cash::$op_status as $k=>$v) {
				$data[$k] = ['title'=>$v];
			}
		break;
		case 'op_status_012':
			foreach (Cash::$op_status as $k=>$v) if ($k<=2) {
				$data[$k] = ['title'=>$v];
			}
		break;
		case 'delivery_status': case 'status_display':
			$data = Cash::delivery_present_status();
		break;
		case 'kc': case 'id_kc':
			$data = Cash::$kc;
		break;
		case 'id_service':
			$data = Cash::$delivery_service;
		break;
		case 'gender':
			$data = Cash::$gender;
		break;
		case 'parent_id': case 'g_id': //pp_offers_group.id
			$data = Cash::$offer_group;
		break;
		case 'f_status':
			$data = Upload_delivery::$f_status;
		break;
		case 'status_int':
			$data = [];
			foreach (Delivery::$status_int as $id=>$c) {
				$data[$id]['title'] = $c;
			}
		break;
		case 'landing':
			$data = Cash::$landing_type;
		break;
		case 'id_country':
			foreach (Cash::$country as $c) {
				$data[$c['id']]['title'] = $c['title'];
			}
		break;
		case 'id_source':
			foreach (Cash::$api_user as $id=>$c) if ($c['is_source']) {
				$data[$id]['title'] = $c['title'];
			}
		break;
		case 'f103_filename': case 'f103_id_service':
			$data = Cash::get_list($field);
		break;
	}

	return $data;
}

private function get_select($c) {
	//найти значения списка
	switch (true) {
		case $c['list']===1: //обратиться в название поля
			$list = $this->select_options($c['name']);
		break;
		case is_string($c['list']): //передано название списка
			$list = $this->select_options($c['list']);
		break;
		default: //остальные случаи не интересны
			$list = $c['list'];
	}

	return $list;
}

function Output() {
	//переформатирование колонок для совместимости
	$new = [];
	$is_colspan = false;
	foreach ($this->Columns as $name => $c) {
		$new_c = [];

		try {
			if (!is_array($c)) {
				throw new Exception($this->Name.', '.$name.', '.print_r($this->Columns,true), 2);
			}
		} catch (Exception $e) {
			ExceptionHandler($e);
			continue;
		}

		foreach ($c as $p => $v) {
			$p = strtolower($p);

			if ($p == 'colspan') {
				$is_colspan = true;
			}

			$new_c[$p] = $v;
		}

		if ($new_c['name']) { //имя, заданное в поле, главнее индекса
			$name = $new_c['name'];
		} else {
			$new_c['name'] = $name;
		}

		$new_c['title'] = isset($new_c['title']) ? $new_c['title'] : $name;

		$new[$name] = $new_c;
	}

	$this->Columns = $new;
	//-------

	if (!$this->Columns && class_exists($class_name) && property_exists($class_name, 'Columns')) {
		$class_name = $this->Name;
		$this->Columns = $class_name::$Columns;
	}

	if ($is_colspan) {
		$cs_fields = [];
		foreach ($this->Columns as $name => $c) if (!$c['skip']) {
			if ($c['colspan'] && is_array($c['colspan'])) {
				$i = 0;
				foreach ($c['colspan'] as $cs_name) if ($this->Columns[$cs_name]) {
					$cs_fields[] = $cs_name;
					$thead[1][$cs_name] = $this->Columns[$cs_name];
					$i++;
				}
				$c['colspan_fields'] = $c['colspan'];
				$c['colspan'] = $i;
				$thead[0][$name] = $c;
			} else {
				if ($cs_fields && in_array($name, $cs_fields)) continue;
				$thead[0][$name] = $c + ['rowspan'=>2];
			}
		}
	} else {
		$thead[] = $this->Columns;
	}
	$this->lines = count($this->Data);

	//колонки, требующие обращения к внешним таблицам
	if ($this->Columns['id_client_car']) {
		$ids = [];
		foreach ($this->Data as $line) {
			$ids[] = $line['id_client_car'];
		}
		$this->data_cash['id_client_car'] = DB::col('SELECT id AS ARRAY_KEY, title FROM vw_client_car WHERE id IN (?ai) ORDER BY is_sold, title',$ids);
	}

	if ($this->Columns['client_cars'] || $this->Columns['client_cars_vin']) {
		$data_cash = DB::select('SELECT id_Client AS ARRAY_KEY_1, NULL AS ARRAY_KEY_2, title, vin, vin_ok
			FROM vw_client_car WHERE id_Client IN (?ki) AND is_sold = 0 ORDER BY id_Client, is_sold, title',$this->Data);

		if ($this->Columns['client_cars']) {
			foreach ($data_cash as $id_Client => $cars) {
				$set = [];
				foreach ($cars as $c) {
					$set[] = $c['title'];
				}
				$this->data_cash['client_cars'][$id_Client] = implode('<br>',$set);
			}
		}

		if ($this->Columns['client_cars_vin']) {
			foreach ($data_cash as $id_Client => $cars) {
				$set = [];
				foreach ($cars as $c) {
					$set[] = '<p class="vin-p'.($c['vin_ok'] ? '' : ' bg-danger').'">'.substr($c['vin'],0,-6).'<u>'.substr($c['vin'],-6).'</u></p>';
				}
				$this->data_cash['client_cars_vin'][$id_Client] = implode('',$set);
			}
		}
	}

	//задан id выгрузки, следовательно вызов осуществляется из генерации экселя
	if ($this->is_output_xls) {
		$line = $out_cols = [];
		foreach ($thead as $row) {
			foreach ($row as $c) if (!$c['skip']) {
				if ($c['rowspan']) {
					$line[0][] = '';
					$line[1][] = $c['title'];
				} elseif ($c['colspan']) {
					$line[0][] = $c['title'];
					for ($i=1;$i<$c['colspan'];$i++) {
						$line[0][] = '';
					}
				} else {
					$line[1][] = $c['title'];
				}
				if ($c['colspan']) continue;
				$out_cols[] = $c;
			}
		}
		
		$xls_data = $line;
		//запись значений
		foreach ($this->Data as $name => $l) {
			$line = [];
			foreach ($out_cols as $c) {
				$v = strip_tags($l[$c['name']]);

				if (preg_match('/^[\d\s\.,]+$/',$v)) {
					$v = str_replace([' ','.'], ['',','], $v); //под русский эксель
				}
				
				$line[] = $this->format($c,$v,$name);
			}
			$xls_data[] = $line;
		}

		return $xls_data;
	}

	$this->TotalLines = $this->TotalLines ? : $this->lines;

	$controls = '';

	if ($this->Options || $this->OptionsHtml || $this->OptionsExtra) {
		$OptionsHtml = [];
		foreach ($this->Options as $kind) {
			switch ($kind) {
				case 'search':
					$OptionsHtml[] = '<a class="overlay-load i-i i-search" href="?c=Search&m=Lists&class='.$this->Name.'"></a>';
					if ($this->search_query) {
						$OptionsHtml[] = '<a onclick="search_reset()"><span class="glyphicon glyphicon-remove-circle cp"></span></a>';
					}
				break;
				case 'report':
					$OptionsHtml[] = '<a class="overlay-load i-i i-xls" target="_blank" href="?c=Report&m=Create&t='.$this->Name.'"></a>';
				break;
				default:
					$OptionsHtml[] = '<a class="overlay-load i-i i-add" href="?c='.$this->Name.'&m='.$kind.'"></a>';
			}
		}

		$OptionsHtml = array_merge($OptionsHtml,$this->OptionsHtml);

		foreach ($OptionsHtml as $i => $opt) {
			$controls .= '<div class="etc-controls"'.(strlen($i) > 2 ? ' id="'.$i.'"' : '').'>'.$opt.'</div>';
		}

		foreach ($this->OptionsExtra as $opt) {
			$controls .= $opt;
		}
	}

	if ($this->showTotal) {
		$controls .= '<span class="label label-info lines-total-wrap pull-right">Всего '.nf($this->TotalLines + $this->adjustTotal).'</span>';
	}

	$controls && ($res .= '<div class="tab-controls">'.$controls.'</div>');

	$res .= '<div class="container-fluid">';

	if ($this->Data) {

		$thead_html = '<thead>';
		foreach ($thead as $tr) {
			$thead_html .= '<tr>';
			foreach ($tr as $name=>$c) if (!$c['skip']) {
				$c['name'] = $name = $c['name'] ? : $name;

				$thead_html .= '<th ';

				if ($c['width']) {
					$thead_html .= ' width="'.$c['width'].'%"';
				}

				if ($c['colspan']) {
					$thead_html .= ' colspan="'.$c['colspan'].'"';
				}

				if ($c['rowspan']) {
					$thead_html .= ' rowspan="'.$c['rowspan'].'"';
				}

				$class = [];

				if ($c['thclass']) {
					$class[] = $c['thclass'];
				}

				//подумать над тем, как перенести в format
				if ($c['align']) {
					$class[] = $c['align'][0];
				} else {
					switch ($c['format']) {
						case 'money':
						case 'nf':
						case 'perc':
						case 'perc_clean':
							$class['align'] = 'r';
						break;
						case 'checkbox':
							$class['align'] = 'c';
						break;
					}
				}

				if (isset($c['fr-rename'])) {
					$class[] = 'fr-rename';
					$thead_html .= ' data-tname="'.$this->Name.'"';
				}

				$thead_html .= ' data-name="'.$c['name'].'"';

				if ($c['_data']) {
					$thead_html .= $c['_data'];
				}

				if ($class) {
					$thead_html .= ' class="'.implode(' ',$class).'"';
				}

				$thead_html .= '>'.$c['title'].'</th>';
			}
			$thead_html .= '</tr>';
		}

		$thead_html .= '</thead>';

		if (!$this->Ajax || $this->Reload) {
			$z = ' class="';
			if (!$this->Ajax) $z .= 'noajax ';
			if ($this->Reload) $z .= 'reload ';
			$z .= '"';
		}
		
		$tbody = '<tbody'.$z.'>';
		
		foreach ($this->Data as $ID=>$line) {
			$tbody .= $this->TR($ID,$line,$thead);
		}
		
		$tbody .= '</tbody>';

		if ($this->PerPage) {
			if ($this->showTotal!=='delayed' && $this->TotalLines > $this->lines) {
				$pageline = $this->pageline_output($this->Page,$this->TotalLines,$this->PerPage);
			} elseif ($this->showTotal==='delayed') {
				$pageline = '<li '.($this->Page==1 ? 'class="active"' : 'class="ajax-page-prev-next" data-page="'.($this->Page-1).'"').'><a>Предыдущая страница</a></li>
					<li '.($this->PerPage > $this->lines ? 'class="active"' : 'class="ajax-page-prev-next" data-page="'.($this->Page+1).'"').'><a>Следующая страница</a></li>';
			}
			$pageline = $pageline ? '<ul class="pagination cp" data-table="'.$this->Name.'">'.$pageline.'</ul>' : '';
		} else {
			$pageline = '';
		}
	}

	if ($tbody) {
		if ($this->pageline_top) {
			$res .= $pageline;
		}

		$res .= $this->html_xtra.'<table class="table';

		if ($this->isStriped) {
			$res .= ' table-striped';
		}

		if ($this->isBordered) {
			$res .= ' table-bordered';
		}

		$res .= ' table-condensed" id="franktable-'.$this->Name.'"';

		if ($this->html_data) {
			$res .= ' '.$this->html_data;
		}

		if ($this->width) {
			$res .= ' style="width:'.$this->width.'"';
		}

		$res .= '>'.$thead_html.$tbody.'</table>';
	}

	$res .= $pageline.'</div>';

	return $res;
}

}
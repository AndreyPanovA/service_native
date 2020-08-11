<?php class Tab {

const
	PER_PAGE = 40,
	// искусственные ID, которые не являются уникальными после удаления \D, начинаются с этого значения (гарантирует 9 млн. значений)
	ID_CUSTOM_START = 990000000;

public
	$name, //имя класса, оно же название таблицы
	$error, //для трансляции ошибки
	$ID,
	$item = [], //элемент на редактирование
	$debug_mode = false,
	$is_price_custom = false, //нестандартная цена в Orders, Service
	$sname = 'search',
	$link = [ //связи между сущностями
		'is_new'=>0, //новая запись
		'base'=>'from', //от объекта
		'ids'=>[], //ключи (не только $obj->ID)
		'columns'=>[], //колонки tab => sql
	],
	$query = [];

protected
	$is_root = false,
	$is_mex = false,
	$crit = [],
	$callback = '',
	$crit_status = [],
	$crit_in = [],
	$crit_join = [],
	$crit_ids = [];

static
	$errors = [],
	$report_title = [],
	$report_special = [],
	$title = '',
	$is_log = false,
	$is_sync = false,  //синхронизировать с другими Складами
	$is_sync_sql = false,
	$is_sync_replace = false,  //синхронизировать заменой
	$sync_fields = [], //поля на автоматическое сохранение
	$sync_skip = [], //поля, которые не синхронизировать
	$sync_skip_foreign = [], //внешние таблицы, которые не синхронизировать
	$uq_fields = [],   //поля на проверку уникальности
	$payments = [
		'dt_prepay'=>'type_prepay',
		'dt_pay'=>'type_pay',
	],
	$payment_icon = ['наличными','по карте','по безналу','с чеком'],
	$Columns = [];

function __construct($id = 0) {
	$this->name = get_class($this);
	$this->ID = $id;
}

function set_query($query) {
	if ($query['id']) {
		$this->ID = $query['id'];
		unset($query['id']);
	}

	//это лучше реализовать на фронте
	array_walk_recursive($query, function(&$item){$item = trim($item);} );

	$this->query = $query;
}

protected function crit($alias = '') {
	if ($alias!=='') {
		$alias .= '.';
	}
	$start = 'AND'; //!!!!
	$this->crit = $this->crit_ids = $this->crit_join = $this->crit_status = [];

	if (!$this->query['search']) return;

	foreach ($this->query['search'] as $k => $v) {
		if ($v==='') continue;
		$this->crit_call(DB::escape($k), is_array($v) ? $v : DB::escape(trim($v)));
	}

	if ($this->crit_ids) {
		$union = [];
		foreach ($this->crit_ids as $table => $tc) {
			$union[] = 'SELECT ID_'.$this->name.' FROM '.$table.' WHERE '.implode(' AND ',$tc);
		}
		$this->crit[] = 'ID IN ('.implode(' UNION ',$union).')';
	}

	if ($sql_count = $this::sql_count($this->query['search']['qs'],false)) {
		$this->crit[] = $alias.'ID IN ('.$sql_count.')';
	}

	if ($this::$Columns['Status']) {
		if ($this->crit_status) {
			$crit_str = '';
			if ($this->crit_status[-1]) {
				$crit_str = $alias.'isDeleted = 1 OR ';
				unset($this->crit_status[-1]);
			} else {
				$crit_str = $alias.'isDeleted = 0 AND ';
			}
			$this->crit[] = '('.$crit_str.$alias.'Status IN ("'.implode('","',array_keys($this->crit_status)).'"))';
		} elseif (!$this->crit) {
			$this->crit[] = $alias.'isDeleted = 0';
		}
	}
	
	if ($this->crit_in) {
		foreach ($this->crit_in as $field => $values) {
			foreach ($values as &$v) {
				$v = '"'.DB::escape($v).'"';
			}
			$this->crit[] = $field.' IN ('.implode(',',$values).')';
		}
	}

	if ($this->crit) {
		return ' '.$start.' '.implode(' AND ',$this->crit);
	}	
}

}

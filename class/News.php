<?php class News extends Tab {

const
	Q = 8;

static
	$title = 'Новости',
	$Columns = [
		'photos'=>['title'=>'Фото','width'=>1],
		'dt'=>['title'=>'Дата','format'=>'datetime','search'=>1],
		'title'=>['title'=>'Заголовок','search'=>1],
		'brief'=>['title'=>'Кратко','search'=>1],
		'txt'=>['title'=>'Текст','search'=>1,'skip'=>1],
	];

private
	$dir;

function __construct($id = 0) {
	parent::__construct($id);
	$this->dir = '/upload/'.$this->name.'/';
}

function upload() {
	if (!$_FILES['file']['size']) {
		return ['error'=>'Не удалось загрузить файл'];
	}

	$filename = md5_file($_FILES['file']['tmp_name']).strrchr($_FILES['file']['name'], '.');

	if (!move_uploaded_file($_FILES['file']['tmp_name'], ROOT_DIR.$this->dir.$filename)) {
		return ['error'=>'Не удалось переместить файл'];
	}

	return ['ok'=>true, 'filename'=>$filename];
}

function Delete() {
	if (false !== DB::q('UPDATE ?t SET isDeleted = 1 WHERE id = ?i', $this->name, $this->ID)) {
		$r = ['ok'=>1,'callback'=>'wreload()','close'=>1];
	}

	return $r;
}

function Restore() {
	if (false !== DB::q('UPDATE ?t SET isDeleted = 0 WHERE id = ?i', $this->name, $this->ID)) {
		$r = ['ok'=>1,'callback'=>'wreload()','close'=>1];
	}

	return $r;
}

function Save() {
	$data = [];

	$fields = ['title','brief','txt','photos'];

	foreach ($fields as $f) {
		array_key_exists($f, $this->query) && ($data[$f] = $this->query[$f]);
	}

	$data['title'] || ($data['title'] = 'без названия');
	is_array($data['photos']) && ($data['photos'] = implode(',',$data['photos']));

	if ($this->ID) {
		$res = DB::q('UPDATE ?t SET ?u WHERE id = ?i', $this->name, $data, $this->ID);
	} else {
		$data['dt'] = DT::$now;
		$res = DB::q('INSERT INTO ?t ?#', $this->name, $data);
	}

	if ($res === false) {
		$error = 'Ошибка при сохранении';
	}

	return [
		'ok' => $error ? 0 : 1,
		'error' => $error,
		'callback' => 'wreload()',
		'close' => 1,
	];
}

function Create() {
	if ($this->ID) {
		$info = DB::row('SELECT * FROM ?t WHERE ID = ?i', $this->name, $this->ID);
		$this::$title .= '. Редактировать';
	} else {
		$info = [
			'title'=>'',
			'photos'=>'',
			'brief'=>'',
			'txt'=>'',
			'isDeleted'=>0,
		];
		$this::$title .= '. Добавить';
	}

	$t = new Template($this->name.'-Create');
	$t->v = $info;
	$t->v['name'] = $this->name;
	$t->v['dir'] = $this->dir;
	$t->v['is_save'] = !$info['isDeleted'];
	$t->v['is_delete'] = $this->ID && !$info['isDeleted'];
	$t->v['is_restore'] = $info['isDeleted'];
	$t->v['ID'] = $this->ID;

	return $this->Output($t->html());
}

function Lists() {
	$T = new Table($this);

	$T->Options[] = 'Create';
	// $T->Options[] = 'search';

	$crit = $this->crit();

	// чужие новости не редактируются
	$sql = 'SELECT id, dt, title, brief, isDeleted, photos FROM ?t WHERE href IS NULL'.$crit.' ORDER BY dt DESC';

	$data = $T->GetPagedData($sql);

	foreach ($data as $d) {
		$mute = $d['isDeleted'] ? 'text-muted' : '';
		$T->Data[$d['id']] = [
			'_rowclass'=>$mute,
			'dt'=>$d['dt'],
			'photos'=>$d['photos'] ? '<img class="photo" src="'.$this->dir.$d['photos'].'">' : '',
			'title'=>'<a href="?c='.$this->name.'&m=Create&id='.$d['id'].'" class="overlay-load '.$mute.'">'.$d['title'].'</a>',
			'brief'=>$d['brief'],
		];
	}

	return $this->OutputLists($T->Output());
}

function getMore() {
	$res = DB::select('SELECT id, title, brief, photos, href
		FROM ?t WHERE isDeleted = 0
		ORDER BY dt DESC LIMIT ?i, ?i', $this->name, $this->query['from'], self::Q);

	foreach ($res as &$r) {
		$r['photos'] && ($r['photos'] = ($r['href'] ? '' : $this->dir).$r['photos']);
		unset($r['href']);
	}

	return $res;
}

function parse_drive2ru() {
	$site = 'https://www.drive2.ru/';

	$html = file_get_contents($site.'users/petrbakulov/');

	preg_match_all('/<div.*?class="[^"]*c-block--base(.*?)\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/ims', $html, $x);

	$ins = [];
	foreach ($x[1] as $post) {
		preg_match('/class="c-preview-pic.*?img src="(.*?)"/ims', $post, $y);
		$photos = $y[1];

		preg_match('/data-tt="(\d+)\s+(\w+)\s+(\d+).*?(\d\d:\d\d)"/u', $post, $y);
		$dt = $y[3].'-'.array_search($y[2], DT::$rus['f']).'-'.$y[1].' '.$y[4];

		preg_match('/class="c-post-preview__title".*?href="(.*?)".*?>(.*?)</ims', $post, $y);
		$title = trim($y[2]);
		$href = $y[1];

		preg_match('/class="c-post-preview__lead".*?>(.*?)</ims', $post, $y);
		$brief = trim(html_entity_decode($y[1]));

		$html = file_get_contents($site.$href);

		preg_match('/class="c-post__body.*?>.*?<div.*?class="c-post-meta/ims', $html, $p);

		$txt = $p[0];

		$txt = preg_replace('/<div\s+class="c-post__pic".*?img.*?src="(.*?)".*?<\/a>\s*<\/div>/ims', '<img src="$1">', $txt);
		$txt = preg_replace('/<\/?div.*?>/', '', $txt);
		$txt = substr($txt, strpos($txt, '<p'));
		$txt = trim(substr($txt, 0, strpos($txt, '<div')));

		$ins[] = [
			'dt' => $dt,
			'title' => $title,
			'href' => $href,
			'brief' => $brief,
			'photos' => $photos,
			'txt' => $txt,
		];
	}

	return DB::q('INSERT IGNORE INTO News ?#
		ON DUPLICATE KEY UPDATE
			dt = VALUES(dt), title = VALUES(title), brief = VALUES(brief), photos = VALUES(photos), txt = VALUES(txt)', $ins);
}

function page($t) {
	$data = DB::row('SELECT title, photos, txt, href FROM ?t WHERE ID = ?i', $this->name, $this->ID);
	$data['photos'] && ($data['photos'] = '<img src="'.($data['href'] ? '' : $this->dir).$data['photos'].'">');
	unset($data['href']);
	$t->v += $data;
}

}

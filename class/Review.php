<?php class Review extends Tab {

const
	Q = 7,
	EMAILS = 'frankbakulov@gmail.com,bakulov.petr@gmail.com,minasyan@larsonv.ru,agapov@larsonv.ru,alexeev@larsonv.ru';

static
	$title = 'Отзыв';

function process(&$vars) {
	$crit = $_SESSION['servis'] == 'mercedes-benz' ? ' AND id_klad <> 2' : '';
	$data = DB::col('SELECT YEAR(dt) AS ARRAY_KEY_1, id_klad AS ARRAY_KEY_2,
		ROUND(AVG(rating),2)
		FROM ClientReview
		WHERE rating'.$crit.'
		GROUP BY 1, 2');

	$vars['stats'] = json_encode($data);
	$vars['rating_total'] = number_format(DB::cell('SELECT ROUND(AVG(rating),2) FROM ClientReview WHERE rating'.$crit), 2);
}

function Feedback() {
	$txt = $this->query['txt'];
	$this->query['contact'] && ($txt .= '<br><br>От '.$this->query['contact']);
	return email(self::EMAILS, 'Сообщение с сайта larsonv.ru', $txt);
}

function Landing() {
	$txt = $this->query['comment'].'<br><br>— от '.$this->query['title'];
	$this->query['phone'] && ($txt .= ', тел. '
		.preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})$/',
			'8-$1-$2-$3-$4',
			preg_replace('/\D/', '', $this->query['phone'])));
	$this->query['email'] && ($txt .= ', '.$this->query['email']);

	return email(self::EMAILS, 'Сообщение с лэндинга Larson Volvo', $txt);
}

function getMore() {
	$this->query['is_txt'] && ($crit = ' AND r.review <> ""');

	$this->query['for'] === 'landing' && ($crit .= ' AND r.dt_send < "2020-07-12 07:00:00"');

	$res = DB::select('SELECT IFNULL(s.num, "") AS num, c.f, c.i, c.o, r.rating, r.review, r.id_klad, s.id,
			DATE_FORMAT(dt_send, "%d.%m.%Y") AS d
		FROM ClientReview AS r
		INNER JOIN Client AS c ON c.id = r.id_Client AND r.publish = 1'.$crit.'
		LEFT JOIN Service AS s ON r.id_Service = s.id AND r.id_klad = s.id_klad
		ORDER BY dt_send DESC LIMIT ?i, ?i', $this->query['from'], self::Q);

	foreach ($res as &$r) {
		if ($r['id'] === 14485 && $r['id_klad'] === 1) {
			$r['reply'] = 'Ларсон не предлагает ремонт замка багажника, так как из опыта известно, что симптомы проявляются снова: не закрывается с кнопки, не открывается с ручки, не работает вообще. Дверные замки — ремонтируем, потому что там причина поломки, как правило, механическая. А вот замок багажника представляет собой единую контактную группу, которую практически невозможно восстановить до заводского состояния с соответствующей гарантией.<br><br>
Мы принесли свои извинения клиенту за то, что мастера не объяснили это как следует при визите. Павел остался доволен предоставленными коментариями.';
		}
		unset($r['id']);
	}

	return $res;
}

static function FIO($info, $is_html = true, $is_nbsp = false) {
	$res = '';

	if ($info['F']!=='') {
		$res .= $info['F'].' ';
	}

	if ($info['sex'] == 2) {
		$info['I'] = $info['O'] = '';
	}

	if ($info['I']!=='') {
		$res .= $info['I'];
	}

	if ($res!=='' && $info['O']!=='') {
		$space = $is_nbsp ? '&nbsp;' : ' ';
		$res = $is_html
			? htmlspecialchars($res).$space.htmlspecialchars($info['O'])
			: $res.$space.$info['O'];
	} else {
		$res = $res.$info['O'];
		if ($is_html) {
			$res = htmlspecialchars($res);
		}
	}

	return $res;
}


}
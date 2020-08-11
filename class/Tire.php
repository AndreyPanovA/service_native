<?php class Tire extends Tab {

const Q = 7;

function getMore() {
	$cirt = '';

	($width = +$this->query['width']) && ($crit .= ' AND width = "'.$width.'"');
	($height = +$this->query['height']) && ($crit .= ' AND height = "'.$height.'"');
	($diameter = +$this->query['diameter']) && ($crit .= ' AND diameter LIKE "%'.$diameter.'%"');
	($season = $this->query['season']) && ($crit .= ' AND season IN ('.implode(',', $season).')');
	if ($brand = $this->query['brand']) {
		foreach ($brand as &$b) {
			$b = DB::escape($b);
		}

		$crit .= ' AND brand IN ("'.implode('","', $brand).'")';
	}

	$data = DB::select('SELECT img, title, price, q, width, height, diameter, brand, season, code
	FROM ?t WHERE 1'
	.$crit 
	.' ORDER BY IF(q <> "", price, 999999 + price) LIMIT ?i, ?i',
	$this->name, $this->query['from'], self::Q);

	return [
		'data' => $data,
		'brands' => $this->query['load_brands'] ? DB::col('SELECT DISTINCT brand FROM ?t WHERE price > 0 ORDER BY 1', $this->name) : [],
	];
}

}

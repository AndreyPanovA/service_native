<?php class Page extends Tab {

private function p() {
	return $_SESSION['servis'] === 'mercedes-benz' ? '' : '/volvo';
}

function Lists() {
	$page = $this->query['page'];
	$vars = [];
	switch ($page) {
		case 'about':
			$page = '/volvo/about/about';
			$title = 'О нас';
		break;
		case 'app':
			$page = '/app';
		break;
		case 'corporate':
			$page = '/clients/clients';
			$title = 'Корпоративным клиентам';
		break;
		case 'promo':
			$page = '/promo/promo';
			$title = 'Акции';
		break;
		case 'tires':
			$page = '/tires/tires';
			$title = 'Шины';
		break;
		case 'review':
			$page = '/review/review';
			$title = 'Отзывы';
			(new Review)->process($vars);
		break;
		case 'parts':
			$page = $this->p().'/parts/parts';
			$title = 'Запчасти и аксессуары';
		break;
		case 'contact':
			$page = $this->p().'/contacts/contacts';
			$title = 'Контакты';
		break;
		case 'uslugi/strahovanie-avto':
			$page = '/insurance/insurance';
			$title = 'Страхование авто';
		break;
		case 'uslugi/diagnosticheskaya-karta':
			$page = '/card/card';
			$title = 'Диагностическая карта';
		break;
		case 'uslugi/evakuator':
			$page = '/evacuation/tow';
			$title = 'Эвакуатор';
		break;
		case 'servis-volvo/contact':
			$page = '/volvo/contacts/contacts';
			$_SESSION['servis'] = 'volvo';
			$title = 'Контакты';
		break;
		case 'servis-mercedes-benz/contact':
			$page = '/contacts/contacts';
			$_SESSION['servis'] = 'mercedes-benz';
			$title = 'Контакты';
		break;
	}

	$vars += [
		'rand' => rand(0,100000),
		'title' => $title ? $title.' — Larson' : 'Автосервис Ларсон',
	];

	$t = new Template($page);
	$t->v = $vars;
	return $t->html();
}

}
function ny2019() {
	if (~document.cookie.indexOf("larson_ny_2019=2")) return

	$('body').append('<div class="is-opened" id="ny-2019">\
  	<article class="works">\
      <section>\
        <h2>Уважаемые Клиенты «Larson»!</h2>\
        <p>31 декабря (Новый год) «Larson» работает с 09:00 до 17:00.<br>1 и 2 января 2019 года – выходные дни.</p>\
        <p>С 3 января мы работаем с 09:00 до 20:00 ежедневно,<br>телефон для записи: <a href="tel:+74957811081" class="comagic">+7 (495) 781-10-81</a><br>– единый для всех сервисных центров.</p>\
  		</section>\
  	</article>\
    <link rel="stylesheet" href="http://larsonv.ru/assets/styles/ny-2019.css">\
  </div>')

	$('#ny-2019').on('click', function() {
		document.cookie = 'larson_ny_2019=2; expires="Sun, 20 Jan 2019 00:00:00 GMT";'
		$(this).removeClass('is-opened')
	})
}

ny2019();
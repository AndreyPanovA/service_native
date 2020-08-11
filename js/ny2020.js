if (setCookie === undefined) {
	function setCookie(c_name,value) {
		if (!c_name || value==undefined) return

		var exdate = new Date(),
			c_value = c_name + '=' + escape(value) + '; expires='

		exdate.setDate(exdate.getDate() - 1)
		document.cookie = c_value + exdate.toUTCString()

		if (value!=null) {
			exdate.setDate(exdate.getDate() + 2)
			document.cookie = c_value + exdate.toUTCString()
		}

		return true
	}

	function getCookie(c_name) {
		var c_value = document.cookie,
			c_start = c_value.indexOf(" " + c_name + "=")

		if (c_start == -1) {
			c_start = c_value.indexOf(c_name + "=")
		}

		if (c_start == -1) {
			c_value = ''
		} else {
			c_start = c_value.indexOf("=", c_start) + 1
			var c_end = c_value.indexOf(";", c_start)

			if (c_end == -1) {
				c_end = c_value.length
			}

			c_value = unescape(c_value.substring(c_start,c_end))
		}

		return c_value
	}
}


$(function() {
	return;
	// $('body').append('<div id="workon"><a href="/contact">Работаем для Вас как всегда ежедневно с 9 до 20.</a></div>');
	if (getCookie('covid1984') > 0) return;

	$('body').append('<div class="alert alert-rum">Сервис «Румянцево» возобновляет работу в штатном режиме с 15 мая! Обращаем внимание, что войти или въехать на территорию ТК «Автомастер» можно только в перчатках и маске.</div>');

	$('body').one('click', function(){
		setCookie('covid1984', 1);
		$('.alert').remove();
	});
});

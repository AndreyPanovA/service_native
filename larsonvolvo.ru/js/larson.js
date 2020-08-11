$(function() {
	var a_apple = '<a href="https://itunes.apple.com/us/app/larson-car/id1190680675" target="_blank" class="app-apple"></a>',
	a_google = '<a href="https://play.google.com/store/apps/details?id=com.larson.car" target="_blank" class="app-google"></a>',
	html = '<a href="https://lk.larsonv.ru" target="_blank" class="app-lk"></a>';

	if (~navigator.userAgent.toLowerCase().indexOf('android')) {
	html += a_google;
	} else if (~navigator.userAgent.toLowerCase().indexOf('iphone')) {
	html += a_apple;
	} else {
	html += a_apple + a_google;
	}

	html = '<div class="apps">\
	<div class="app-text">C «Larson Бонус» выгоднее! Наше приложение:</div>\
	<div class="app-store">' + html + '</div>\
	</div>';

	$('body').append(html);
});
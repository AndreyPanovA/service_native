$(function() {
	
	// Мобильное меню
	$('.nav-icon').on('click', function(e) {
		$('.header-nav').toggleClass('is-nav-opened');

		if ($('.header-nav').hasClass('is-nav-opened')) {
			e.stopPropagation();
			$(document).on('click.menu', function(e) {
				if (!$(e.target).closest('.mobile-nav')) {
					$('.header-nav').removeClass('is-nav-opened');
					$(document).off('click.menu');
				}
			});
		}
	});
});

function v(a) {
	console.log(a)
}

$(function(){
	var $tires = $('#tires'),
		seasons = ['', 'летняя', 'зимняя', 'всесезонная'],
		html = '',
		is_loading = false,
		loadItems = function(load_brands, load_items){
			load_items === undefined && (load_items = true)
			is_loading = true
			$.ajax({
				dataType: 'json',
				data: $('.filter').serialize()+'&c=Tire&m=getMore&load_brands='+(load_brands || 0)+'&from='+$('.tire').length,
				success: function(r) {
					r.data.length && (is_loading = false) //если ничего не вернулось, далее загружать нет смысла

					if (load_items) {
						html = ''
						r.data.forEach(function(item, i) {
							html += '<div class="item tire"><div class="tire-pic">'
								+(item.img ? '<img src="'+item.img+'" alt="" border="0">' : '')
								+'<div class="tire-code">'+item.code+'</div></div><div class="tire-info">\
									<div class="tire-title">'+item.title+'</div>\
									<div class="tire-price">'+item.price+' <span class="rouble">о</span></div>\
									<div class="tire-params">\
										Ширина <b>'+item.width+'</b><br>\
										Высота профиля <b>'+item.height+'</b><br>\
										Диаметр <b>'+item.diameter+'</b><br>\
										Тип <b>'+seasons[item.season]+'</b><br>'
										+(item.q ? 'В наличии <b>'+item.q+'</b>' : 'Под заказ')
									+'</div>\
								</div>\
							</div>'
						})
						$tires.append(html)
						$tires.html() || $tires.html('Ничего не найдено. Попробуйте изменить параметры поиска.');
					}


					if (load_brands) {
						html = ''
						r.brands.forEach(function(item){
							html += '<label class="brand-item-wrap"><input type="checkbox" class="brand-item" name="brand[]" value="'+item+'"> '+item+'</label>'
						})

						$('.brand-list').css({height: (20 * Math.ceil(r.brands.length) / 4)+'px'}).html(html)
					}
				}
			})
		},
		toggleItems = function() {
			$tires.html('')
			loadItems()
			$('html, body').animate({
				scrollTop: $('#scrollTo').offset().top
			}, 500);
		}

	html = 'Тип: '
	seasons.forEach(function(item, i){
		if (!i) return
		html += '<label><input type="checkbox" class="param-click" name="season[]" value="'+i+'" checked> '+item+'</label>'
	})
	$('.filter-season').html(html)

	loadItems(true, false)

	$('.filter :input').on('keypress', function(e){
		e.which == 13 && toggleItems()
	})

	// $('.param').on('keyup', toggleItems)
	// $('.param-click').on('click', toggleItems)

	//brands
	$('.brand-toggle-all').on('click', function(){
		$('.brand-item').prop('checked', false)
		// toggleItems()
	})

	$('.brand-list').on('click', '.brand-item', function(){
		$('.brand-toggle-all').prop('checked', $('.brand-item:checked').length ? false : true)
		// toggleItems()
	})

	$('#tire-find').on('click', toggleItems)

	$tires.scroll(function() {
		var s = $tires.scrollTop(),
			h = $tires.height(),
			sh = $tires[0].scrollHeight;

		$tires.html() && (s + h > sh - h) && !is_loading && loadItems();
	})

})

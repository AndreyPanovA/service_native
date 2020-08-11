$(function(){
	var $tires = $('#tires'),
		seasons = ['', 'летняя', 'зимняя', 'всесезонная'],
		html = '',
		is_loading = false,
		loadItems = function(load_brands, load_items){
			load_items === undefined && (load_items = true)
			is_loading = true
			$.ajax({
				dataType:'json',
				method: 'post',
				data: $('.form').serialize()
					+'&c=Tire&m=getMore&load_brands='+(load_brands || 0)+'&from='+$('.tire').length,
				success: function(r) {
					//если ничего не вернулось, далее загружать нет смысла
					r.data.length && (is_loading = false);

					if (load_items) {
						html = ''
						r.data.forEach(function(item, i) {
							html += '<div class="ajax_tires shad__form cnt"><div class="ajax_price">\
								<div class="flex__block"><div>\
								<img src="'+(item.img || '/i/tire-icon.png')+'" alt="" class="tireImg"><p>'
								+item.code+'</p></div><div class="tire_text"><p class="bold">'
								+item.title+'</p><p class="bold">'
								+item.price+'р.</p><p>Ширина '
								+item.width+'</p><p>Высота профиля '
								+item.height+'</p><p>Диаметр '
								+item.diameter+'</p><p>Тип '
								+seasons[item.season]+'</p><p>'
								+(item.q ? 'В наличии '+item.q : 'Под заказ')
								+'</p></div></div></div></div>';
						});
						$tires.append(html);
						$tires.html() || $tires.html('<div class="ajax_tires shad__form cnt">Ничего не найдено. Попробуйте изменить параметры поиска.</div>');
					}

					if (load_brands) {
						const col = 4,
							q_row = Math.ceil(r.brands.length / col);
						let c = 0, i = 0;
						html = '';
						while (c < col) {
							html += '<div class="column-'+c+' suppliers-col">';
							for (i = c * q_row; i < (c + 1) * q_row; i++) {
								const item = r.brands[i];
								item && (html += 
									'<label class="brand-item-wrap"><input type="checkbox" class="brand-item" name="brand[]" value="'+item+'" checked> '+item+'</label>'
								);
							}
							html += '</div>';
							c++;
						}

						$('.brand-list')
							// .css({height: (26 * q_row)+'px'})
							.html(html);
					}
				}
			})
		},
		toggleItems = function() {
			$tires.html('')
			loadItems()
			$('html, body').animate({
				scrollTop: $('.scroll').offset().top
			}, 500);
		}

	html = 'Тип: ';
	seasons.forEach(function(item, i) {
		if (!i) return;
		html += '<label><input type="checkbox" class="param-click" name="season[]" value="'+i+'" checked> '+item+'</label>';
	});
	$('.filter-season .filter_left').html(html);

	loadItems(true, false);

	$('.form :input').on('keypress', function(e){
		e.which == 13 && toggleItems()
	})

	// $('.param').on('keyup', toggleItems)
	// $('.param-click').on('click', toggleItems)

	//brands
	$('.brand-toggle-all').on('click', function(){
		$('.brand-item').prop('checked', this.checked);
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
});

/* jquery.chosen wrapper with ajax autocomplete support
data = {
	
}
*/

function Chosen(data) {
	var $select = '<select class="form-control '+(data.class || '')+'"',
		search_q = '', //current search string
		xhr_search = {readyState:4},
		is_typing,
		LineSet = function(items) {
			//empty first option activates search field and make first select as change event
			var html = data.is_multiple ? '' : (data.no_empty ? '' : '<option></option>'),
				html_selected = ''

			items[0] && items.forEach(function(item){
				var value = escapeHtml(item.value),
					attr
				
				line = '<option'

				item.id === undefined || (line += ' value="'+escapeHtml(item.id)+'"')
				item.is_selected && (line += ' selected')
				
				for (attr in item) {
					(attr != 'id' && attr != 'value' && attr != 'is_selected')
					&&
					(line += ' data-'+attr+'="'+escapeHtml(item[attr])+'"')
				}

				line += '>'+value+'</option>'
				item.is_selected && (!data.no_chosen || data.is_multiple) ? (html_selected += line) : (html += line)
			})
		
			return html_selected + html
		}	

	if (data.name) {
		$select += ' name="'+data.name+'"'
	}

	if (data.id) {
		$select += ' id="'+data.id+'"'
	}

	if (data.style) {
		$select += ' style="'+data.style+'"'
	}

	if (data.is_multiple) {
		$select += ' multiple'
	}

	if (data.is_readonly) {
		$select += ' readonly'
		data.no_chosen = true
		data.no_empty = true
	}

	if (data.is_disabled) {
		$select += ' disabled'
	}

	$select += '>' + LineSet(data.search ? [data.current] : data.data) + '</select>'

	$select = $($select)

	data.search && $select.find(':last').prop('selected',true)

	data.no_chosen || $select.chosen({
		allow_single_deselect: data.allow_single_deselect,
		no_results_text: 'Не найдено',
		placeholder_text_multiple: data.placeholder || ' ',
		placeholder_text_single: data.placeholder || ' '
	})

	// $select.find('.chosen-container').on('touchstart', function(e){
	// 	e.stopPropagation()
	// 	e.preventDefault()
	// 	$(this).trigger('mousedown')
	// })

	$chosen = $select.next()
	$chosen.css('width',data.width || '100%')

	if (data.onchange) {
		if (data.no_chosen) {
			$select.on('change',data.onchange)
		} else {
			$select.on('custom',data.onchange)
			
			$chosen.on('keyup',function(e){
				e.which === 13 && $select.trigger('custom')
			}).on('click','.active-result',function(){
				$select.trigger('custom')
			})
		}
	}

	data.search
	&& $chosen.find('.chosen-search input').on('keyup',function(){
		is_typing && clearTimeout(is_typing) //search only once

		var $input = $(this),
			q = $input.val().trim()

		is_typing = q && search_q !== q
		//search only when different and not empty
		&& setTimeout(function(){
			q = $input.val().trim() //input could've changed programmatically while timeout
			if (!(q && search_q !== q)) return

			search_q = q

			//cancel running ajax
			xhr_search.readyState !== 4 && xhr_search.abort()
			
			var data_search = typeof data.search === 'function' ? data.search() : data.search
			data_search.m = 'Search'
			data_search.q = q

			xhr_search = $.ajax({
				data: data_search,
				success: function(r) {
					data.data = r
					q = $input.val()
					$select.html( LineSet(r) ).trigger('chosen:updated')
					$input.val(q) //otherwise $input will be cleared
				}
			})
		},300)
	})

	return [$select,$chosen]
}

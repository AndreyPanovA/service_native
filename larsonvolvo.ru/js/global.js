$.ajaxSetup({
	type:'POST',
	dataType:'json',
	timeout:10000,
	url:'index.php',
	beforeSend: function(xhr,settings) {
		if (this.h!=undefined) {
			$(this.h).prop('disabled',true)
		}
	},
	complete:function(xhr,status) {
		if (this.h!=undefined) {
			$(this.h).prop('disabled',false)
		}
		if (status!='success') {
			$(this.h).after(xhr.responseText)
		}
	},
	success:function(m) {
		m === undefined && (m = {})

		if (m.alert) {
			alert(m.alert)
		}

		var func = ''
		if (m.callback!=undefined) {
			func = m.callback
		} else if (this.callback!=undefined) {
			func = this.callback
		}

		if (func) {
			if (func==1) {
				var x = this.data.m == undefined ? this.data.match(/\bm=(\w+)/) : this.data.m
				if (x) {
					func = x[1]
				}
			}
			eval('success_'+func+'(m,this)')
		}

		if (m.reload) {
			dreload()
		}

		if (m.message!=undefined) {
			$('#message').html(m.message)
		}

		if (m.close) {
			$('#modal').modal('close')
		}
	}
})

var	is_window_loading = false,
	search_regexp = /&search\[.*?\]=[^&]+/g,
	$D = $(document),
	modal = new Modal,
	_alert = {},
	_timer_name = '',
	r_Service_f1 = 0, //специальное персональное оповещение
	ids_not = [], //исключение из поиска
	windows = [] //открытые окна

function mt() {
	if (_timer_name) {
		console.timeEnd(_timer_name)
		_timer_name = ''
	} else {
		_timer_name = 'mt'
		console.time(_timer_name)
	}
}

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

$(function(){
	$D.on('click',':submit',function(e){
		var f = $(this).closest('form')

		if (!f.hasClass('ajax')) return

		e.preventDefault()
		$.ajax({
			data:f.serialize(),
			h:this
		})
	})

	$D.on('click','.overlay-load',function(e){
		e.preventDefault()
		e.stopPropagation()
		overlay_load($(this).attr('href'))
		var table = $(this).closest('table')
		if (table.attr('id')=='franktable-Orders' || table.attr('id')=='franktable-Service') {
			table.children('tbody').children().removeClass('s')
			$(this).closest('tr').addClass('s')
		}
	})

	$D.on('click','.object-switch',function(){
		if (!confirm('Вы уверены?')) return false

		var t = $(this)

		$.ajax({
			data:t.closest('form').attr('rel').replace(/&m=\w+/,'&m='+t.data('m')),
			success:function(m) {
				if (m.ok) {
					if (m.callback) {
						eval(m.callback)
					}
				} else {
					alert(m.error)
				}
			}
		})
	})
	
	$D.on('click','.form-submit',function(e){
		e.preventDefault()
		
		var _this = this,
			t = $(this),
			f = t.closest('form'),
			add = ''

		if (t.attr('name')) {
			add += '&'+t.attr('name')+'='+t.attr('value')
		}

		tinymce && tinymce.triggerSave()
		
		// t.prop('disabled',true)

		var ajax_call = function(add) {
			$.ajax({
				h: _this,
				data:f.attr('rel')+'&'+f.serialize()+add,
				success:function(m) {
					t.prop('disabled',false)
					if (m) {
						if (m.confirm) {
							if (confirm(m.confirm)) {
								ajax_call(add + '&confirm=1')
							} else {
								return
							}
						}

						if (m.ok) {
							if (m.ok!=1) {
								f.html(m.ok)
							}
							
							if (m.callback) {
								eval(m.callback)
							}
						} else if (m.check) {
							alert('Подозрительно высокая стоимость или количество деталей!\nПроверьте ещё раз!')
							m.check.forEach(function(i){
								$('#linesPart tbody tr:eq('+i+')').addClass('bg-danger')
							})
						} else if (m.error) {
							alert(m.error)
						} else if (m.auth) {
							overlay_load('?c=User&m=LoginWindow')
						}
					}
				}
			})
		}

		ajax_call(add)
	})
	

	$D.on('keydown','.tabstop',function(e){
		var direction = 0

		switch (e.keyCode) {
			case 13: case 39:
				direction = 1
			break
			case 37:
				direction = -1
			break
		}

		if (direction) {
			var multiple = 0,
				$input

			do {
				multiple++
				$input = $('.tabstop:eq('+(+$('.tabstop').index(this)+direction*multiple)+')')
			} while ($input.length && !$input.is(':visible'))

			$input.select()
		}
	})
	
	$D.on('click','#dreload',dreload)

	InitDatetimepicker()

	$('.to-hidden').each(function(){
		var t = $(this)
		t.after('<input type="hidden" name="'+t.prop('name')+'" value="'+(this.checked ? 1 : 0)+'">')
		.removeAttr('name value')
		t.change(function(){
			$(this).next().val(this.checked ? 1 : 0)
		})

	})

	$D.on('click','.search-toggle',function(){
		var t = $(this),
			f = t.data('field'),
			replace = new RegExp('&search\\['+f+'\\]=\\w+')
		location.href = location.href.replace(/&pg=\d+/,'').replace(replace,'') + '&search['+f+']='+t.data('value')
	})

	$D.on('change','.handle-checkbox',function(){
		$(this).prev().val(+this.checked)
	})

	$D.on('keypress',function(e){
		if (e.keyCode !== 13 || !e.ctrlKey) return

		var $submit = $('.form-submit:visible')
		
		$submit.length === 1 && $submit.click()
	})

	if ($('#object-restore').length) {
		$(':input').prop('disabled',true)
		$('#object-restore').prop('disabled',false)
	}

	$('.confirm-edit[readonly]').on('dblclick',function(){
		$(this).prop('readonly')
		&& confirm('Вы уверены, что хотите изменить это поле?')
		&& $(this).prop('readonly',false)
	})

	/* при создании/редактировании item возможно проверять по полю, входящему в уникальный ключ, существующие item
		data-name: название поля
		data-value: текущее значение поля
	*/
	$('.-chosen-jump').each(function(){
		var $t = $(this),
			d = $t.data()

		$t.html(Chosen({
			name: d.name,
			search: {c: tab, field: d.name, is_jump: true},
			current: {id: d.value, value: d.value},
			onchange: function() {
				this.value.match(/^\d+$/) //если вернулисть только цифры...
				//...то переводим на карточку item
				&& (document.location = location.href.replace(/&id=\d*/,'') + '&id='+this.value)
			}
		})).removeClass('-chosen-jump')
	})

	$D.on('click', '.ajax-page', function(){
		location.href = location.href.replace(/&pg=\d+/, '') + '&pg=' + $(this).data('page')
	})

	InitLive()
})

function search_reset() {
	if (window.location.href.indexOf('&w=1')==-1) {
		window.location = window.location.href.replace(search_regexp,'')
	} else {
		window.opener.location = window.opener.location.href.replace(search_regexp,'')
		wclose()
	}
}

//фокусировать дочернее окно может только opener
function focusWindow(w) {
	w.focus()
}

function overlay_load(h) {
	if (is_window_loading) {
		return
	} else {
		is_window_loading = true
	}

	var wh = '',
		win = {},
		i=0,
		whv = {
			News:[1000,555],
		},
		val = h.match(/c=(\w+)/),
		_w = window,
		is = false,
		is_open = true

	while (_w.opener) { //все окна записываются к корневому
		_w = _w.opener
	}

	if (h.indexOf('c=Client&m=History') > -1) { // !!! окультурить
		wh = [900,800]
	} else if (val) {
		wh = whv[val[1]]
	}

	wh = wh ? 'width='+wh[0]+',height='+wh[1] : ''

	//предотвращение открытия одинаковых окон
	_w.windows.forEach(function(w,i){
		if (is || w.url != h) return
		if (w.w.closed) { //окно закрыто
			_w.windows.splice(i,1) //удаляем из массива
			is = true
		} else { //окно открыто
			//фокусируем
			w.opener.focusWindow(w.w)
			is_window_loading = false
			is_open = false
		}
	})

	if (!is_open) return
		
	//такого окна нет. создаём
	win = window.open(h+'&w=1','_blank','location=0,menubar=0,status=0,toolbar=0,titlebar=0,scrollbars=1,fullscreen=0,'+wh)
	//записываем в открытые
	_w.windows.push({
		url: h,
		w: win,
		opener: window,
	})
	is_window_loading = false
}

function InitLive() {
	InitDatetimepicker()

	TextareaHeight()

	$('.true-checkbox').each(function(){
		var $t = $(this),
			$n = $('<input type="checkbox" class="handle-checkbox"'
					+(this.value==0 ? '' : ' checked')
					+(this.disabled ? ' disabled' : '')
					+'>')

		$.event.copy($t,$n)

		$t.after($n).removeClass('true-checkbox')
	})
}

function TextareaHeight() {
	$('textarea:not(.-sh)').addClass('-sh').on('keyup',function(){
		var height = $(this).data('height') || 28
		this.style.height = 0
		this.style.height = (height + this.scrollHeight) + "px"
	}).keyup()
}

function InitDatetimepicker() {
	$('.datepicker:not(.defer)').each(function(){
		var t = $(this),
			timeFormat = t.attr('format-time'),
			dateFormat = t.attr('format-date')

		t.removeClass('datepicker')

		if (timeFormat) {
			t.datetimepicker({
				timeFormat:timeFormat,
				dateFormat:dateFormat,
				onSelect:function(datetimeText,inst){
					var p = $(this).next().val().match(/^(\d+)-(\d+)-(\d+)(\s(\d+):(\d+):(\d+))?$/),
						dt = new Date(p[1],p[2]-1,p[3],p[5],p[6],p[7])

					if (inst.inst) { //changed time
						var t = $.datepicker.parseTime(inst.inst.settings.timeFormat, inst.formattedTime)
						
						dt.setHours(t.hour)
						dt.setMinutes(t.minute)
						dt.setSeconds(t.second)
						dt.setMilliseconds(t.millisec)
					} else { //changed date
						dt.setDate(inst.currentDay)
						dt.setMonth(inst.currentMonth)
						dt.setFullYear(inst.currentYear)
					}

					$.datepicker.set_ts([
						dt.getFullYear(),
						dt.getMonth(),
						dt.getDate(),
						dt.getHours(),
						dt.getMinutes(),
						dt.getSeconds()],this)
				}			
			}).on('change',function(){
				var dt = t.datetimepicker('getDate')
				$.datepicker.set_ts(dt ? [dt.getFullYear(),dt.getMonth(),dt.getDate(),dt.getHours(),dt.getMinutes(),dt.getSeconds()] : [],this)
			})
		} else {
			t.removeClass('datepicker').datepicker({
				dateFormat:dateFormat,
				onSelect:function(dateText,inst){
					$.datepicker.set_ts([inst.selectedYear,inst.selectedMonth,inst.selectedDay],this)
				}
			}).on('change',function(){
				var d = t.datepicker('getDate')
				$.datepicker.set_ts(d ? [d.getFullYear(),d.getMonth(),d.getDate()] : [],this)
			})
		}
	})
}

function dreload() {
	document.location.reload()
}

function intval(val) {
	return parseInt(val.replace(/[^\d]/g,''))
}

function showPost(t) {
	$(t).toggleClass('post')
}

function wclose() {
	window.close()
}

function wreload() {
	if (window.opener) {
		try {
			window.opener.location.reload()
			window.close()
		} catch(err) {
			document.location.reload()
		}
	} else {
		document.location.reload()
	}
}

function escapeHtml(string) {
	var entityMap = {
		'<':'&lt;',
		'>':'&gt;',
		'"':'&quot;'
		// '\'':'&#39;',
		// '/':'&#x2F;'
	}

	return String(string).replace(/["<>]/g, function (s) {
		return entityMap[s]
	})
}

function v(val) {
	console.log(val)
}

function nf(value, decimal, space, dot) {
	space === undefined && (space = ' ')
	var frac = '',
		zerocut = decimal < 0

	if (decimal) { //есть дробная часть, нужно округлять c заданной точностью
		decimal = Math.abs(decimal)
		value = String(Math.round(+value * Math.pow(10, decimal)))
		frac = value.slice(value.length - decimal)
		if (zerocut) {
			frac = +('0.'+frac)
			frac = frac ? (dot || '.') + String(frac).slice(2) : ''
		} else {
			frac = (dot || '.') + frac
		}
		value = value.slice(0, value.length - decimal)
	} else {
		value = String( Math.round(+value) )
	}

	if (space !== '') {
		var a = value.split(''),
			i = value.length

		while ((i -= 3) > 0) {
			a.splice(i, 0, space)
		}

		value = a.join('')
	}

	return (value || 0) + frac
}

function Modal() {
	this.buttons = []
	this.text = ''
	this.data = {} //arbitrary data useful to transport button event handlers
}

Modal.prototype.reset = function() {
	this.buttons = []
}

Modal.prototype.hide = function() {
	$('#modal').modal('hide')
}

Modal.prototype.button = function(id,title,clas) {
	this.buttons.push({id:id,title:title,clas:clas})
}

Modal.prototype.show = function() {
	$('#alert-text').html(this.text)
	var title = '', clas = '', id = '', html = ''
	for (var i=0;i<this.buttons.length;i++) {
		var b = this.buttons[i]

		html += '<button type="button"'

		clas = b.clas==undefined ? 'default' : b.clas

		if (b.id=='cancel') {
			title = b.title==undefined ? 'Отмена' : b.title
			id = ''
			html += ' data-dismiss="modal"'
		} else {
			title = b.title
			id = ' id="'+b.id+'"'
		}

		html += ' class="btn btn-'+clas+'"'+id+'>'+title+'</button>'
	}
	$('#alert-action').html(html)

	$('#modal').modal({backdrop:true})
};


jQuery.event.copy = function (from, to) {
    from = from.jquery ? from : jQuery(from);
    to = to.jquery ? to : jQuery(to);

    var events = from[0].events || jQuery.data(from[0], "events") || jQuery._data(from[0], "events");

    if (!from.length || !to.length || !events) return;

    return to.each(function () {
        for (var type in events)
        for (var handler in events[type])
        jQuery.event.add(this, type, events[type][handler], events[type][handler].data);
    });
};

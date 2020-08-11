$(function(){
let $reviews = $('.scrollview'),
	is_loading = false,
	html = '',
	html_stats = '',
	klads_stats = (y, id_klad) => {
		let r = ['','Коломенская','Можайка','Кузовной'][id_klad];
		y < 2020 && id_klad == 2 && (r = 'Румянцево');
		return r;
	},
	stats_title = '<p>Средняя оценка</p>',
	htmlReview = function(d) {
		html += '<div class="shad__reviw">';
		d.rating && (html += 'Оценка: '+d.rating);
		html += '— '+(d.i + ' ' + d.o + ' ' + d.f.slice(0,1)+'.');
		d.review && (html += '<div class="rev_text">' + d.review.replace(/\n/g,'<br>') + '</div>');
		html += '</div>';
	},
	loadReview = function() {
		is_loading = true
		$.ajax({
			dataType:'json',
			method: 'post',
			data: {
				c: 'Review',
				m: 'getMore',
				from: $('.review').length,
			},
			success: function(r){
				html = ''
				r.forEach(htmlReview)
				$reviews.append(html)
				r.length && (is_loading = false) //если ничего не вернулось, далее загружать нет смысла
			}
		})
	}

Object.keys(stats).reverse().forEach(y => {
	console.log(y);
	html_stats += '<div class="avg">' + stats_title + '<p>' + y + '</p><ul>';
	stats_title = '';
	for (id_klad in stats[y]) {
		html_stats += '<li>'+klads_stats(y, id_klad)+' — '+stats[y][id_klad].toFixed(2)+'</li>';
	}
	html_stats += '</ul></div>';
});

$('.avg__count').html(html_stats);

loadReview()

$reviews.scroll(function() {
	var s = $reviews.scrollTop(),
		h = $reviews.height(),
		sh = $reviews[0].scrollHeight

	;(s + h > sh - h) && !is_loading && loadReview()
})

});

/* 
let review_body = document.querySelector(".scrollview");
const url = "https://larsonv.ru/mobile.php";

const data = { c: "Review", m: "getMore", from: "0" };
async function getReviews() {
  const res = await fetch("https://larsonv.ru/mobile.php", {
    method: "POST",
    body: JSON.stringify({ c: "Review", m: "getMore", from: 0 }),
    headers: {
      "Content-type": "application/json; charset=UTF-8",
    },
  });
  return await res.json();
}

getReviews().then((res) => {
  let fioArr = [];
  let componentAll = ``;
  res.forEach((elem, idx) => {
    let fio = `${elem.f} ${elem.i} ${elem.o}`;
    console.log(res);
    // arr.push(fio);
    // if (elem.review == "") {
    let component = "";
    if (elem.review) {
      component = `
      <div class="shad__reviw">
      Оценка: ${elem.rating}
      — ${elem.f} ${elem.i} ${elem.o}
       <div class="rev_text">${elem.review}</div>
      </div>
      `;
    } else {
      component = `
      <div class="shad__reviw">
      Оценка: ${elem.rating}
      — ${elem.f} ${elem.i} ${elem.o}
      </div>
      `;
    }

    // <div class="little">Отзыв: ${elem.review}<div/>
    // }
    // else {
    // let component = `
    //       <div class="shad__reviw">
    //       Оценка: ${elem.rating}
    //       — ${elem.f} ${elem.i} ${elem.o}
    //       </div>

    //       `;

    //   }

    componentAll += component;
    review_body.innerHTML = componentAll;
  });
});
 */
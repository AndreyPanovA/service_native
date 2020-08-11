window.addEventListener("load", function () {
  document.querySelector("body").classList.add("loaded");
});
setTimeout(() => {
  document.querySelector("body").classList.add("loaded");
}, 8000);
const burg = document.querySelector(".burger");
const menue = document.querySelector("nav");
const closer = document.querySelector(".modalClose");
const body = document.querySelector(".blur_container");
burg.addEventListener("click", () => {
  menue.classList.toggle("menuActive");
  burg.classList.toggle("move");
  burg.classList.toggle("xer");
  body.classList.toggle("blur");
  body.style.border = "20px solid red;";
  closer.style.display = "block";
});
closer.addEventListener("click", () => {
  closer.style.display = "none";
  burg.classList.remove("move");
  burg.classList.remove("xer");
  menue.classList.remove("menuActive");
  body.classList.remove("blur");
});

function yandexNavi(id) {
	const now = new Date().valueOf(),
		list = [null, {
			url: 'https://yandex.ru/maps/213/moscow/?ll=37.661024%2C55.683092&mode=search&ol=geo&ouri=ymapsbm1%3A%2F%2Forg%3Foid%3D1247111237&z=13',
			app: 'yandexnavi://show_point_on_map?lat=55.683092&lon=37.661024&zoom=12&no-balloon=0&desc=Larson Коломенская'
		}, {
			url: 'https://yandex.ru/maps/1/moscow-and-moscow-oblast/house/mozhayskoye_shosse_vl167/Z04YdQ9kSUcPQFtvfXtxeXRrZQ==/?ll=37.384028%2C55.708881&z=16.56',
			app: 'yandexnavi://show_point_on_map?lat=55.708881&lon=37.384028&zoom=12&no-balloon=0&desc=Larson Можайка'
		}];

	let u = list[id];
	if (!u) return false;
	
	const agent = navigator.userAgent.toLowerCase();
	if (agent.indexOf('safari') > 0 && agent.indexOf('chrome') === -1) {
		window.location = list[id].url;
		return;
	}

	setTimeout(() => {
		if (new Date().valueOf() - now > 100) return;
		window.location = list[id].url;
	}, 95);
	window.location = list[id].app;

	return false;
}

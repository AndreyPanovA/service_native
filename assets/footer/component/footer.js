let fot = document.querySelector("footer");
const skew = document.querySelectorAll(".video");
skew.forEach((elem) => {
  elem.addEventListener("click", (event) => {
    elem.classList.toggle("skew");
    if (event.target.className.indexOf("finOpen") !== -1) {
      elem.classList.toggle("bigVideo");
    }
  });
});
Footer = ` 
<div class="fot_wrap">
  <p>C «Larson Бонус» выгоднее! Наше приложение:</p>
  <div class="app-store">
  	<a href="https://lk.larsonv.ru" target="_blank">
      <img src="../assets/img/larson-white.svg" alt="Larson" />
    </a>
    <a
      href="https://itunes.apple.com/us/app/larson-car/id1190680675"
      target="_blank"
      class="app-apple"
    >
      <img
        src="../assets/img/apple.svg"
        alt="AppleStore"
        href="https://itunes.apple.com/us/app/larson-car/id1190680675" /></a
    ><a
      href="https://play.google.com/store/apps/details?id=com.larson.car"
      target="_blank"
      class="app-google"
    >
      <img
        src="../assets/img/google.svg"
        alt="GooglePlay"
        href="https://itunes.apple.com/us/app/larson-car/id1190680675"
    /></a>
  </div>
</div>
`;
fot.innerHTML = Footer;

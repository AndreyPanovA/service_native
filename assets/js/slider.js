// const left = document.querySelector(".btn_left");
// const right = document.querySelector(".btn_right");
// const slides = document.querySelectorAll(".slide");
// const slidesBg = document.querySelectorAll(".slide_bg");
// const slider = document.querySelector(".slider");
// let slide_idx = 0;

// function changeSlide(sign, elem) {
//   elem.forEach((element, index) => {
//     element.classList.remove("show");
//   });
//   slide_idx = slide_idx + sign;
//   if (slide_idx < 0) {
//     slide_idx = elem.length - 1;
//   } else if (slide_idx > elem.length - 1) {
//     slide_idx = 0;
//   }
//   elem[slide_idx].classList.add("show");
// }

// left.addEventListener("click", () => {
//   slides.forEach((element) => {
//     element.style.animation = "appearLeft 0.5s ease-in-out";
//   });

//   changeSlide(-1, slides);
//   changeSlide(-1, slidesBg);
// });

// right.addEventListener("click", () => {
//   slides.forEach((element, index) => {
//     element.style.animation = "appearRight 0.5s ease-in-out";
//   });
//   changeSlide(1, slides);
//   changeSlide(1, slidesBg);
// });

//
// const audi = document.querySelector(".audi");
// const skoda = document.querySelector(".skoda");
// function tabOpen(elem) {
//   let i = 0;
//   const element = document.querySelector(`.${elem}`);
//   element.addEventListener("click", () => {
//     const content = document.querySelector(`.${elem} .content`);
//     const all_content = document.querySelector(`.${elem} .all_content`);
//     console.log(content);
//     console.log(i);
//     if (i > 0) {
//       i--;
//       content.style.height = 10 + "px";
//     } else {
//       i++;
//       content.style.height = all_content.offsetHeight + "px";
//     }
//   });
// }
// tabOpen("audi");
// tabOpen("skoda");
// tabOpen("range_white");
// tabOpen("volvo_xc70");
// tabOpen("porsche_macan");
// tabOpen("toyota_RAV4");

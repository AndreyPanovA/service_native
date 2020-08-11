//

// const left = document.querySelector(".left-arrow");
// const right = document.querySelector(".right-arrow");

const sl = document.querySelector(".csslider");
const sliderAria = document.querySelector(".slider__aria");
const plSeven = document.querySelector("em");
let allSlides = 0;
const dotContainer = document.querySelector(".dots");
let currentValue = 0;
let counter = 0;
// Форма
//
{
  // userText-------------------------------------------------------------------------------------------------------------// Панов Андрей
  // ---------------------------------------------------------------------------------------------------------------------//panov3107@mail.ru
  // ---------------------------------------------------------------------------------------------------------------------//https://github.com/AndreyPanovA
}
// форма
let name = document.querySelector(".name");
let email = document.querySelector(".email");
let phone = document.querySelector(".phone");
let textAria = document.querySelector(".userText");

// let autentific = document.querySelectorAll(".autentific"); // аутентификация
// autentific.forEach((el, i) => {
//   el.addEventListener("keyup", (e) => {

//   });
// });

// let emailL = document.querySelector(".emailL");
// emailL.style.backgroundColor = "pink";

phone.addEventListener("focus", () => {
  plSeven.style.opacity = "1";
});
phone.addEventListener("blur", () => {
  if (phone.value.length > 0) {
    plSeven.style.opacity = "1";
  } else {
    plSeven.style.opacity = "0";
  }
});

let formObj = {
  // title: name.value,
  // email: email.value,
  // phone: phone.value,
  // comment: textAria.value
};
function del(elem, label, type) {
  //
  elem = document.querySelector(elem);
  label = document.querySelector(label);
  elem.addEventListener("keyup", () => {
    if (elem.value.trim().length > 0) {
      label.style.opacity = "0";
      console.log(elem.value);
    } else {
      label.style.opacity = "1";
    }
    // Проверка на длину
    if (elem.value.trim().length < 2 && elem.value.trim().length !== 0) {
      elem.style.backgroundColor = "#ff545c";
    } else if (elem.value.trim().length == 0) {
      elem.style.backgroundColor = "#f0f0f0";
    } else {
      elem.style.backgroundColor = "#f0f0f0";
      elem.style.borderLeft = "2px solid green";
    }

    // Проверка на email
    if (type == "email") {
      elem.style.borderLeft = "2px solid transparent";
      if (
        /[@]/i.test(elem.value) &&
        elem.value.trim().length > 3 &&
        /[.]/i.test(elem.value)
      ) {
        elem.style.borderLeft = "2px solid green";
        emel.style.color = "white";
      } else if (elem.value.trim().length == 0) {
        elem.style.backgroundColor = "#f0f0f0";
      } else {
        elem.style.backgroundColor = "#ff545c";
      }
    }
    // Проверка на телефон
    if (type == "phone") {
      if (phone.value.length > 0) {
        plSeven.style.color = "white";
      } else {
        plSeven.style.color = "blue";
      }
      elem.style.borderLeft = "2px solid transparent";
      if (
        /[^0-9+]/i.test(elem.value) ||
        (elem.value.trim().length < 10 && elem.value.trim().length > 1)
      ) {
        elem.style.backgroundColor = "#ff545c";
      } else if (elem.value.trim().length == 0) {
        elem.style.backgroundColor = "#f0f0f0";
      } else {
        elem.style.borderLeft = "2px solid green";
        plSeven.style.color = "blue";
      }
    }
  });
  // elem.addEventListener("keyup", e => {

  // });
}
// function EmailValid(elem, num) {
//   elem.addEventListener("keyup", () => {
//     if (elem == email) {
//       alert("cool");
//     }
//   });
// }

del(".email", ".emailL", "email");
del(".name", ".nameL", "name");
del(".phone", ".phoneL", "phone");

{
  // ---------------------------------------------------------------------------------------------------------------------// Панов Андрей
  // Сервер форма-----------------------------------------------------------------------------------------------------------//panov3107@mail.ru
  // ---------------------------------------------------------------------------------------------------------------------//https://github.com/AndreyPanovA
}
//
const sender = document.querySelector(".sender");

function sendForm(body, cb) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "https://larsonv.ru/mobile.php");
  xhr.addEventListener("load", () => {
    const response = JSON.parse(xhr.responseText);
    cb(response);
  });
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  xhr.addEventListener("error", () => {
    console.log("error");
  });
  xhr.send(JSON.stringify(body));
}

sender.addEventListener("click", (e) => {
  const newPost = {
    c: "Client",
    m: "Landing",
    title: name.value,
    email: email.value,
    phone: phone.value,
    comment: textAria.value,
  };
  if (
    newPost.title.trim() < 2 ||
    newPost.email.trim() < 2 ||
    newPost.phone.trim() < 2
  ) {
  } else {
    sendForm(newPost, (response) => {
      console.log(response);
      console.log(newPost);
    });
  }
});

//
{
  // ---------------------------------------------------------------------------------------------------------------------//Панов Андрей
  // Отзывы---------------------------------------------------------------------------------------------------------------//panov3107@mail.ru
  // ---------------------------------------------------------------------------------------------------------------------//https://github.com/AndreyPanovA
}
//
const userReview = document.querySelectorAll(".userReview");

const reviews = {
  c: "Review",
  m: "getMore",
  is_txt: true,
  from: 0,
};

sendForm(reviews, (response) => {
  // -----------
  console.log(response[0]);
  let jsx = "";
  for (let i = 0; i < response.length; i++) {
    let o = response[i].o[0] + ".";
    let f = response[i].f[0] + ".";
    console.log(f);
    // if (response[i].review.length < 120 && response[i].review.length > 30) {
    if (response[i].num.trim() < 2) {
      jsx += ` 
        <div class="slider__content">
        <div class="inner_wrapper">
          <p class="bold"> Оценка: ${response[i].rating}</p>
          <p class="userReview">
            ${response[i].review} 
          </p>
          <h1> ${response[i].i} ${response[i].o} ${f}</h1>
          <p class="date">Дата ${response[i].d}</p>
          <div class="left-arrow">&#8249;</div>
          <div class="right-arrow">&#8250;</div>
          <div class="dots"></div>
        </div>
      </div>`;
    } else {
      jsx += ` 
        <div class="slider__content">
        <div class="inner_wrapper">
          <p class="userReview">
            ${response[i].review} 
          </p>
          <h1> ${response[i].i} ${response[i].o} ${f}</h1>
          <p class="date">Заказ-наряд: ${response[i].num}, дата ${response[i].d}</p>
          <div class="left-arrow">&#8249;</div>
          <div class="right-arrow">&#8250;</div>
          <div class="dots"></div>
        </div>
      </div>`;
    }

    // }
  }
  sliderAria.innerHTML = jsx;
  const left = document.querySelectorAll(".left-arrow");
  const right = document.querySelectorAll(".right-arrow");
  // const dotContainer = document.querySelector(".dots");
  allSlides = document.querySelectorAll(".slider__content").length;

  // Точки

  for (let i = 0; i < allSlides; i++) {
    let div = document.createElement("div");
    div.classList.add("dot");
    dotContainer.appendChild(div);
  }
  const dotsMas = document.querySelectorAll(".dot");
  right.forEach((el, i) => {
    el.addEventListener("click", () => {
      currentValue -= 100;
      counter += 1;
      if (counter > allSlides - 1) {
        counter = 0;
        currentValue = 0;
      }
      sliderAria.style.transform = `translateX(${currentValue}vw)`;
      for (let i = 0; i < dotsMas.length; i++) {
        if (counter == i) dotsMas[i].style.backgroundColor = "black";
        else dotsMas[i].style.backgroundColor = "rgb(122, 122, 122)";
      }
    });
  });
  // right.addEventListener("click", () => {
  //   currentValue -= 100;
  //   counter += 1;
  //   if (counter > allSlides - 1) {
  //     counter = 0;
  //     currentValue = 0;
  //   }
  //   sliderAria.style.transform = `translateX(${currentValue}vw)`;
  //   for (let i = 0; i < dotsMas.length; i++) {
  //     if (counter == i) dotsMas[i].style.backgroundColor = "black";
  //     else dotsMas[i].style.backgroundColor = "rgb(122, 122, 122)";
  //   }
  // });
  left.forEach((el, i) => {
    el.addEventListener("click", () => {
      currentValue += 100;
      counter -= 1;
      if (counter < 0) {
        counter = allSlides - 1;
        currentValue *= -(allSlides - 1);
      }
      sliderAria.style.transform = `translateX(${currentValue}vw)`;
      for (let i = 0; i < dotsMas.length; i++) {
        if (counter == i) dotsMas[i].style.backgroundColor = "black";
        else dotsMas[i].style.backgroundColor = "rgb(122, 122, 122)";
      }
    });
  });
  // left.addEventListener("click", () => {
  //   currentValue += 100;
  //   counter -= 1;
  //   if (counter < 0) {
  //     counter = allSlides - 1;
  //     currentValue *= -(allSlides - 1);
  //   }
  //   sliderAria.style.transform = `translateX(${currentValue}vw)`;
  //   for (let i = 0; i < dotsMas.length; i++) {
  //     if (counter == i) dotsMas[i].style.backgroundColor = "black";
  //     else dotsMas[i].style.backgroundColor = "rgb(122, 122, 122)";
  //   }
  // });
  for (let i = 0; i < dotsMas.length; i++) {
    dotsMas[i].addEventListener("click", (event) => {
      dotsMas.forEach((el) => {
        el.style.backgroundColor = "rgb(122, 122, 122)";
      });
      dotsMas[i].style.backgroundColor = "black";
      counter = i;
      currentValue = -counter * 100;
      sliderAria.style.transform = `translateX(${currentValue}vw)`;
    });
  }
});

// Картинки
const pictures = document.querySelectorAll(".picture");
console.log(pictures);
// for (let i = 0; i < pictures.length; i++) {
//   el.style.cursur = "pointer";
//   el.style.position = "fixed";
//   el.style.top = "0";
//   el.style.left = "0";
//   el.style.width = "100vw";
//   el.style.height = "100vh";
// }

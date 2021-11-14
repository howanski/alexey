function eradicate(elem) {
  elem.remove();
}

function anim(elem){
  let anim = elem.animate(
    [
      { transform: "translateY(0px)" },
      { transform: "translateY(-300px)" },
    ],
    {
      duration: 1500,
      iterations: Infinity,
    }
  );
  // anim.onfinish = function (elem) {
  //   elem.remove();
  // };
  setTimeout(() => {
    eradicate(elem);
  }, 1300);
}

function markOfDeath(elem) {
  setTimeout(() => {
    anim(elem);
  }, 3000);
}
let flashes = document.querySelectorAll(".flash-destruct");
Array.from(flashes).map(markOfDeath);

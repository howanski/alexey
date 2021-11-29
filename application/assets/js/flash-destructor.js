function anim(elem) {
  elem.classList.add("alert-toast-out");

  setTimeout(() => {
    elem.remove();
  }, 2000);
}

function markOfDeath(elem) {
  setTimeout(() => {
    anim(elem);
  }, 5000);
}

let flashes = document.querySelectorAll(".flash-destruct");
Array.from(flashes).map(markOfDeath);

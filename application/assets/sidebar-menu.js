import { createPopper } from "@popperjs/core";

function toggleNavbar(event) {
  let elem = event.currentTarget;
  let collapseID = elem.dataset.navbarId;
  document.getElementById(collapseID).classList.toggle("hidden");
  document.getElementById(collapseID).classList.toggle("bg-nord3");
  document.getElementById(collapseID).classList.toggle("m-2");
  document.getElementById(collapseID).classList.toggle("py-3");
  document.getElementById(collapseID).classList.toggle("px-6");
}
function openDropdown(event) {
  let element = event.currentTarget;
  while (element.nodeName !== "A") {
    element = element.parentNode;
  }
  let dropdownID = element.dataset.dropdownId;
  var popper = createPopper(element, document.getElementById(dropdownID), {
    placement: "bottom-end",
  });
  document.getElementById(dropdownID).classList.toggle("hidden");
  document.getElementById(dropdownID).classList.toggle("block");
}

function enableToggler(elem) {
  elem.addEventListener("click", toggleNavbar);
}
let togglers = document.querySelectorAll(".navbar-toggler");
Array.from(togglers).map(enableToggler);

function enableDropper(elem) {
  elem.addEventListener("click", openDropdown);
}
let dropdowners = document.querySelectorAll(".dropdown-toggler");
Array.from(dropdowners).map(enableDropper);

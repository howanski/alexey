function alternateViews(event) {
    const classToHide = "alternative-view";
    const boundaryClass = "alternative-view-boundary";

    const element = event.currentTarget;
    const targetViewId = element.dataset.alternativeViewId;
    const classToShow = "alternative-view-" + targetViewId;

    const boundary = element.closest("." + boundaryClass);
    const elementsToHide = boundary.querySelectorAll("." + classToHide);
    const elementsToShow = boundary.querySelectorAll("." + classToShow);

    Array.from(elementsToHide).forEach(el => el.classList.add("hidden"));
    Array.from(elementsToShow).forEach(el => el.classList.remove("hidden"));
}

function bindAlternationEvent(elem) {
    elem.removeEventListener("click", alternateViews);
    elem.addEventListener("click", alternateViews);
}

window.addEventListener("load", (event) => {
    const alternatorBtns = document.querySelectorAll(".view-alternator");
    Array.from(alternatorBtns).forEach(bindAlternationEvent);
});

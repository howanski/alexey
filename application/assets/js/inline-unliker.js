function clickUnlinkBtn(event) {
    event.preventDefault();
    let elem = event.currentTarget;
    let data = elem.dataset;

    let parentSelector = data.unlinkParent;
    let parent = elem.closest(parentSelector);
    parent.classList.add("alert-toast-out");

    setTimeout(() => {
        parent.remove();
    }, 900);

    let url = data.unlinkPath;
 
    var fetchOptions = {
        method: "POST",
    };

    fetch(url, fetchOptions)
        .then((response) => {})
        .catch((error) => {});
}

function addListener(elem) {
    elem.addEventListener("click", clickUnlinkBtn);
}

let secureButtons = document.querySelectorAll(".inline-unlinker");
Array.from(secureButtons).map(addListener);

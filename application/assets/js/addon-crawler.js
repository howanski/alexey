function clickMassOpenBtn(event) {
    event.preventDefault();
    let elem = event.currentTarget;
    let parentTable = elem.closest(".table-parent-node");
    let externalLinks = parentTable.querySelectorAll(".crawler-external-link");
    Array.from(externalLinks).map(function (elem) {
        elem.click();
    });
}

function manageMassLoaders(elem) {
    elem.addEventListener("click", clickMassOpenBtn);
}

window.addEventListener("load", (event) => {
    let massLoaders = document.querySelectorAll(".crawler-open-all-in-new");
    Array.from(massLoaders).map(manageMassLoaders);
});

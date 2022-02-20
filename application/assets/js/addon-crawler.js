function clickMassOpenBtn(event) {
    event.preventDefault();
    let elem = event.currentTarget;
    let parentTable = elem.closest(".table-parent-node");
    let externalLinks = parentTable.querySelectorAll(".crawler-external-link");
    externalLinks = Array.from(externalLinks);
    externalLinks = externalLinks.slice(0, 10);
    externalLinks.map(function (elem) {
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

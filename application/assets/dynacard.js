import axios from "axios";

function manageDynacard(elem) {
    refreshCard(elem);
    let dynacardRefreshRate = elem.dataset.dynacardRefreshRate * 1000;
    if (dynacardRefreshRate > 0) {
        setTimeout(() => {
            manageDynacard(elem);
        }, dynacardRefreshRate);
    }
}

function refreshCard(elem) {
    let dynacardSource = elem.dataset.dynacardSrc;
    axios
        .get(dynacardSource)
        .then(function (response) {
            // handle success
            let responseData = response.data;

            let headerTextElem = elem.querySelector(".dynacard-header-text");
            let headerValueElem = elem.querySelector(".dynacard-header-value");
            let footerValueElem = elem.querySelector(".dynacard-footer-value");

            headerTextElem.innerHTML = responseData.headerText;
            headerValueElem.innerHTML = responseData.headerValue;
            footerValueElem.innerHTML = responseData.footerValue;
        })
        .catch(function (error) {
            // handle error
            console.log(error);
        })
        .then(function () {
            // always executed
        });
}

let dynacards = document.querySelectorAll(".dynacard");
Array.from(dynacards).map(manageDynacard);

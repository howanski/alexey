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
    const instance = axios.create({
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get(dynacardSource)
        .then(function (response) {
            // handle success
            let responseData = response.data;
            if (responseData.isRaw) {
                let decoded = decodeURIComponent(
                    escape(window.atob(responseData.rawContent))
                );
                elem.innerHTML = decoded;
            } else {
                let headerTextElem = elem.querySelector(
                    ".dynacard-header-text"
                );
                let headerValueElem = elem.querySelector(
                    ".dynacard-header-value"
                );
                let footerValueElem = elem.querySelector(
                    ".dynacard-footer-value"
                );

                headerTextElem.innerHTML = responseData.headerText;
                headerValueElem.innerHTML = responseData.headerValue;
                footerValueElem.innerHTML = responseData.footerValue;
            }
        })
        .catch(function (error) {
            // handle error
            console.log(error);
        })
        .then(function () {
            // always executed
        });
}

window.addEventListener("load", (event) => {
    let dynacards = document.querySelectorAll(".dynacard");
    Array.from(dynacards).map(manageDynacard);
});

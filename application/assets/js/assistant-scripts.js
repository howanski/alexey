const refreshRate = 3000;

function refreshCheck(elem) {
    const checkPath = elem.dataset.checkPath;
    const fetchOptions = {
        method: "GET",
    };

    fetch(checkPath, fetchOptions)
        .then((response) => response.json())
        .then((data) => {
            if (data.result == true) {
                window.location.reload(true);
            }
        })
        .catch((error) => { });
    setTimeout(() => {
        refreshCheck(elem);
    }, refreshRate);
}

window.addEventListener("load", (event) => {
    setTimeout(() => {
        const refreshPageMarkers = document.querySelectorAll(".refresh-page-on-check");
        Array.from(refreshPageMarkers).map(refreshCheck);
    }, refreshRate);
});

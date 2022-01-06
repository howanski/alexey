import axios from "axios";
let currentToken = 0;
let tokenCheckUrl = "";
let qrBar = null;

function checkCount() {
    const instance = axios.create({
        timeout: 2000,
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get(tokenCheckUrl)
        .then(function (response) {
            if (response.status == 200) {
                let responseData = response.data;
                if (responseData != currentToken) {
                    window.location.reload(true);
                }
            }
        })
        .catch(function (error) {})
        .then(function () {});
}

function manageTimeOut() {
    let now = new Date();
    let secondsMarker = now.getSeconds();
    let percent = 100 - (secondsMarker / 60) * 100;
    qrBar.style = "width: " + percent + "%;";
    if (percent > 99) {
        window.location.reload(true);
    }
}

window.addEventListener("load", (event) => {
    let qr = document.querySelector("#qrToken");
    qrBar = document.querySelector("#qrBar");
    currentToken = qr.dataset.currentToken;
    tokenCheckUrl = qr.dataset.tokenCheckUrl;
    setInterval(checkCount, 3000);
    setInterval(manageTimeOut, 1000);
});

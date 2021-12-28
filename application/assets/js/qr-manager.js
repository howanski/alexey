import axios from "axios";
let deviceCount = 0;
let deviceCountUrl = "";

function checkCount() {
    const instance = axios.create({
        timeout: 2000,
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get(deviceCountUrl)
        .then(function (response) {
            if (response.status == 200) {
                let responseData = response.data;
                if (responseData != deviceCount) {
                    window.location.reload(true);
                }
            }
        })
        .catch(function (error) {})
        .then(function () {});
}

window.addEventListener("load", (event) => {
    let qr = document.querySelector("#qrToken");
    deviceCount = qr.dataset.deviceCount;
    deviceCountUrl = qr.dataset.deviceCountUrl;
    setInterval(checkCount, 3000);
});

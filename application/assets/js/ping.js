import axios from "axios";

const goodHeart = document.querySelector(".app-heartrate-ok");
const badHeart = document.querySelector(".app-heartrate-bad");

let heartState = "good";

function goodPing() {
    heartState = "good";
    goodHeart.style.display = "";
    badHeart.style.display = "none";
}

function badPing() {
    heartState = "bad";
    goodHeart.style.display = "none";
    badHeart.style.display = "";
}

function ping() {
    const instance = axios.create({
        timeout: 2000,
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get("/ping")
        .then(function (response) {
            if (response.status == 200) {
                let responseData = response.data;
                if (responseData == "pong") {
                    goodPing();
                } else {
                    window.location.reload(true);
                }
            } else {
                badPing();
            }
        })
        .catch(function (error) {
            badPing();
        })
        .then(function () {
            let timeout = 0;
            if ("good" == heartState) {
                timeout = 29000;
            } else {
                timeout = 1000;
            }
            setTimeout(() => {
                ping();
            }, timeout);
        });
}
if (goodHeart && badHeart) {
    setTimeout(() => {
        ping();
    }, 55000);
}

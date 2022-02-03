import axios from "axios";

function fetchBody(elem) {
    let tableBodyUrl = elem.dataset.tableBodyUrl;
    const instance = axios.create({
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get(tableBodyUrl)
        .then(function (response) {
            // handle success
            let responseData = response.data;
            if (responseData === "autoclose") {
                elem.innerHTML = '';
                let parent = elem.closest(".table-parent-node");
                parent.classList.add("alert-toast-out-inplace-slow");
                setTimeout(() => {
                    parent.remove();
                }, 3000);
            } else {
                elem.innerHTML = responseData;
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
    let dynaTables = document.querySelectorAll("[data-table-body-url]");
    Array.from(dynaTables).map(fetchBody);
});

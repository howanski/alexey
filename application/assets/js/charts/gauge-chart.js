import Chart from "chart.js/auto";
import axios from "axios";
import chartjsGauge from "chartjs-gauge";

var chartStorage = [];

function createChart(elem, labels, datasets) {
    let myLineChart = new chartjsGauge(elem, {
        type: "gauge",
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            valueLabel: {
                display: false,
            },
            needle: {
                color: "#4c566a",
            },
            responsive: true,
        },
    });
    return myLineChart;
}

function prepareDatasets(datasetsObject) {
    var datasetsArray = [];
    for (const prop in datasetsObject) {
        datasetsArray.push(datasetsObject[prop]);
    }
    return datasetsArray;
}

function updateChartData(elem, src) {
    const instance = axios.create({
        headers: { "X-Requested-With": "XMLHttpRequest" },
    });
    instance
        .get(src)
        .then(function (response) {
            // handle success
            let responseData = response.data;
            let chartId = elem.getAttribute("data-chart-id");
            if (null === chartId) {
                chartId = getUnusedRandomChatId();
                elem.setAttribute("data-chart-id", chartId);
            }
            if (typeof chartStorage[chartId] === "undefined") {
                // fresh chart instance
                chartStorage[chartId] = createChart(
                    elem,
                    responseData.labels,
                    prepareDatasets(responseData.datasets)
                );
            } else {
                // refresh already built chart
                let storedChart = chartStorage[chartId];
                storedChart.data.datasets = prepareDatasets(
                    responseData.datasets
                );

                storedChart.update(false);
            }
            updateBonusPayload(responseData.bonusPayload);
        })
        .catch(function (error) {
            // handle error
            console.log(error);
        })
        .then(function () {
            // always executed
        });
}

function updateBonusPayload(payload) {
    if (typeof payload !== "undefined") {
        for (const prop in payload) {
            let elem = document.getElementById(prop);
            if (elem) {
                elem.innerHTML = payload[prop];
            }
        }
    }
}

function getUnusedRandomChatId() {
    let randomId = Math.ceil(Math.random() * 1000).toString();
    if (typeof chartStorage[randomId] === "undefined") {
        return randomId;
    } else {
        return getUnusedRandomChatId();
    }
}

function createChartOnElem(elem) {
    let dataSource = elem.getAttribute("data-chart-src");
    let refreshTime = elem.getAttribute("data-chart-refresh");
    if (refreshTime !== null && refreshTime > 0) {
        let miliseconds = 1000 * refreshTime;
        setTimeout(() => {
            createChartOnElem(elem);
        }, miliseconds);
    }
    updateChartData(elem, dataSource);
}

window.addEventListener("load", (event) => {
    let charts = document.querySelectorAll(".chart-gauge");
    Array.from(charts).map(createChartOnElem);
});

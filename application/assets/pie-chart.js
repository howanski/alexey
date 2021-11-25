import Chart from "chart.js/auto";
import axios from "axios";

var chartStorage = [];

function createChart(elem, labels, datasets) {
    let myLineChart = new Chart(elem, {
        type: "pie",
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: "bottom",
                },
                title: {
                    display: false,
                    text: "Chart title",
                },
            },
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

                let datasetKeysArray = Object.keys(responseData.datasets);
                let index = 0;
                let visibilityIndexes = [];
                for (const datasetKey of datasetKeysArray) {
                    let isDatasetHidden =
                        storedChart.getDatasetMeta(index).hidden;
                    if (isDatasetHidden) {
                        visibilityIndexes[index] = false;
                    } else {
                        visibilityIndexes[index] = true;
                    }
                    index = index + 1;
                }

                storedChart.data.labels = responseData.labels;
                storedChart.data.datasets = prepareDatasets(
                    responseData.datasets
                );

                index = 0;
                for (const visibility of visibilityIndexes) {
                    storedChart.setDatasetVisibility(index, visibility);
                    index = index + 1;
                }
                storedChart.update("none");
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
    if (refreshTime !== null) {
        let miliseconds = 1000 * refreshTime;
        setTimeout(() => {
            createChartOnElem(elem);
        }, miliseconds);
    }
    updateChartData(elem, dataSource);
}

let charts = document.querySelectorAll(".chart-pie");
Array.from(charts).map(createChartOnElem);

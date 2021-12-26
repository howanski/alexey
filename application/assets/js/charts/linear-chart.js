import Chart from "chart.js/auto";
import axios from "axios";

var chartStorage = [];

function createChart(elem, labels, datasets) {
    let myLineChart = new Chart(elem, {
        type: "line",
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 25,
                    bottom: 0,
                },
            },
            scales: {
                xAxes: [
                    {
                        gridLines: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            maxTicksLimit: 7,
                        },
                    },
                ],
                yAxes: [
                    {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2],
                        },
                    },
                ],
            },
            legend: {
                display: false,
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: "#6e707e",
                titleFontSize: 14,
                borderColor: "#dddfeb",
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: "index",
                caretPadding: 10,
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
    let isPaused = elem.dataset.chartIsPaused;
    if (isPaused === "true") {
        return;
    }
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

window.addEventListener("load", (event) => {
    let charts = document.querySelectorAll(".chart-linear");
    Array.from(charts).map(createChartOnElem);
});

function clickPlay(event) {
    event.preventDefault();
    var elem = event.currentTarget;
    var chartContainer = elem.closest(".linear-chart-container");

    var chart = chartContainer.querySelector("canvas");
    chart.dataset.chartIsPaused = "false";

    var pause = chartContainer.querySelector(".linear-chart-pause-button");
    pause.style.display = "";

    var play = chartContainer.querySelector(".linear-chart-play-button");
    play.style.display = "none";

    var refresh = chartContainer.querySelector(
        ".linear-chart-force-refresh-button"
    );
    refresh.style.display = "";
}

function clickPause(event) {
    event.preventDefault();
    var elem = event.currentTarget;
    var chartContainer = elem.closest(".linear-chart-container");

    var chart = chartContainer.querySelector("canvas");
    chart.dataset.chartIsPaused = "true";

    var pause = chartContainer.querySelector(".linear-chart-pause-button");
    pause.style.display = "none";

    var play = chartContainer.querySelector(".linear-chart-play-button");
    play.style.display = "";

    var refresh = chartContainer.querySelector(
        ".linear-chart-force-refresh-button"
    );
    refresh.style.display = "none";
}

function clickRefresh(event) {
    event.preventDefault();
    var elem = event.currentTarget;
    elem.classList.add("animate-spin-once");
    setTimeout(() => {
        elem.classList.remove("animate-spin-once");
    }, 1000);

    var chartContainer = elem.closest(".linear-chart-container");
    var chart = chartContainer.querySelector("canvas");

    let dataSource = chart.getAttribute("data-chart-src");
    updateChartData(chart, dataSource);
}

function makePlayBtnClickable(elem) {
    elem.addEventListener("click", clickPlay);
}

function makePauseBtnClickable(elem) {
    elem.addEventListener("click", clickPause);
}

function makeRefreshBtnClickable(elem) {
    elem.addEventListener("click", clickRefresh);
}

window.addEventListener("load", (event) => {
    let chartPlayBtns = document.querySelectorAll(".linear-chart-play-button");
    let chartPauseBtns = document.querySelectorAll(
        ".linear-chart-pause-button"
    );
    let chartRefreshBtns = document.querySelectorAll(
        ".linear-chart-force-refresh-button"
    );

    Array.from(chartPlayBtns).map(makePlayBtnClickable);
    Array.from(chartPauseBtns).map(makePauseBtnClickable);
    Array.from(chartRefreshBtns).map(makeRefreshBtnClickable);
});

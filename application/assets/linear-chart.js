import Chart from "chart.js/auto";
import axios from "axios";

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
}

function prepareDatasets(datasetsObject) {
  var datasetsArray = [];
  for (const prop in datasetsObject) {
    datasetsArray.push(datasetsObject[prop]);
  }
  return datasetsArray;
}

function updateChartData(elem, src) {
  axios
    .get(src)
    .then(function (response) {
      // handle success
      let responseData = response.data;
      createChart(
        elem,
        responseData.labels,
        prepareDatasets(responseData.datasets)
      );
    })
    .catch(function (error) {
      // handle error
      alert(error);
    })
    .then(function () {
      // always executed
    });
}

function createChartOnElem(elem) {
  let dataSource = elem.getAttribute("data-chart-src");
  updateChartData(elem, dataSource);
}

let charts = document.querySelectorAll(".chart-linear");
Array.from(charts).map(createChartOnElem);

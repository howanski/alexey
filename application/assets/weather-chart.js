import Chart from "chart.js/auto";
import axios from "axios";

function createChart(chartConfig, elem) {
  console.log(chartConfig);
  let myLineChart = new Chart(elem, {
    type: "line",
    data: {
      labels: chartConfig.labels,
      datasets: [
        chartConfig.datasets["temperature"],
        chartConfig.datasets["feels_like"],
        chartConfig.datasets["rain"],
        chartConfig.datasets["wind_speed"],
      ],
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
            time: {
              unit: "time",
            },
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

function updateChartData() {
  axios
    .get(window.chart_data_src)
    .then(function (response) {
      // handle success
      let responseData = response.data;

      let chartConfigHourly = {
        labels: responseData.hourly.labels,
        datasets: responseData.hourly.datasets,
      };
      let chartHourly = document.getElementById("weatherHourly");
      createChart(chartConfigHourly, chartHourly);

      let chartConfigDaily = {
        labels: responseData.daily.labels,
        datasets: responseData.daily.datasets,
      };
      let chartDaily = document.getElementById("weatherDaily");
      createChart(chartConfigDaily, chartDaily);

    })
    .catch(function (error) {
      // handle error
      alert(error);
    })
    .then(function () {
      // always executed
    });
}

updateChartData();

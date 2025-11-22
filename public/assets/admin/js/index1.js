"use strict";
/* basic column chart */
var options = {
  series: [
    {
      name: "Online",
      data: [44, 55, 57, 56, 61, 58, 63, 60, 66],
    },
    {
      name: "Offline",
      data: [76, 85, 101, 98, 87, 105, 91, 114, 94],
    },
    {
      name: "Marketing",
      data: [35, 41, 36, 26, 45, 48, 52, 53, 41],
    },
  ],
  chart: {
    type: "bar",
    height: 350,
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "55%",
      endingShape: "rounded",
    },
  },
  grid: {
    show: true,
    borderColor: "rgba(119, 119, 142, 0.1)",
  },
  dataLabels: {
    enabled: false,
  },
  colors: ["rgb(21, 58, 84)", "rgb(0, 165, 162)", "rgb(166, 142, 94)"],
  stroke: {
    show: true,
    width: 2,
    colors: ["transparent"],
  },
  xaxis: {
    categories: ["Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar", "Apr"],
    labels: {
      show: true,
      style: {
        colors: "#8c9097",
        fontSize: "11px",
        fontWeight: 600,
        cssClass: "apexcharts-xaxis-label",
      },
    },
    axisBorder: {
      show: true,
      color: "rgba(119, 119, 142, 0.05)",
      offsetX: 0,
      offsetY: 0,
    },
    axisTicks: {
      show: true,
      borderType: "solid",
      color: "rgba(119, 119, 142, 0.05)",
      width: 6,
      offsetX: 0,
      offsetY: 0,
    },
  },
  yaxis: {
    title: {
      text: "",
      style: {
        color: "#8c9097",
      },
    },
    labels: {
      show: true,
      style: {
        colors: "#8c9097",
        fontSize: "11px",
        fontWeight: 600,
        cssClass: "apexcharts-xaxis-label",
      },
    },
  },
  fill: {
    opacity: 1,
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return "$ " + val + " thousands";
      },
    },
  },
};
document.getElementById("column-basic").innerHTML = "";
var chartMain = new ApexCharts(
  document.querySelector("#column-basic"),
  options
);
chartMain.render();

function columnbasic() {
  chartMain.updateOptions({
    colors: ["rgb(21, 58, 84)", "rgb(" + myVarVal + ")", "rgb(166, 142, 94)"],
  });
}

/* Ethereum Chart */
var options = {
  series: [
    {
      data: [34, 55, 41, 47, 32, 43, 31],
    },
  ],
  chart: {
    sparkline: {
      enabled: true,
    },
    dropShadow: {
      enabled: true,
      enabledOnSeries: undefined,
      top: 3,
      right: 6,
      blur: 3,
      color: ["#00a5a2"],
      opacity: 0.3,
    },
    type: "line",
    height: 60,
    width: 80,
  },
  tooltip: {
    x: {
      show: false,
    },
    y: {
      title: {
        formatter: function (seriesName) {
          return "";
        },
      },
    },
    marker: {
      show: false,
    },
  },
  colors: ["#00a5a2"],
  stroke: {
    width: [1.5],
    curve: ["smooth"],
  },
  xaxis: {
    crosshairs: {
      show: false,
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      opacityFrom: 0.9,
      opacityTo: 0.9,
      stops: [0, 98],
    },
  },
};
document.getElementById("ethCoin").innerHTML = "";
var chartethCoin = new ApexCharts(document.querySelector("#ethCoin"), options);
chartethCoin.render();

function ethCoin() {
  function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1);
  }
  chartethCoin.updateOptions({
    colors: [
      rgbToHex(
        myVarVal.split(",")[0],
        myVarVal.split(",")[1],
        myVarVal.split(",")[2]
      ),
    ],
  });
}

/* Ethereum Chart 1*/
var options1 = {
  series: [
    {
      data: [25, 39, 36, 47, 32, 43, 31],
    },
  ],
  chart: {
    sparkline: {
      enabled: true,
    },
    dropShadow: {
      enabled: true,
      enabledOnSeries: undefined,
      top: 3,
      right: 6,
      blur: 3,
      color: ["#db555d"],
      opacity: 0.3,
    },
    type: "line",
    height: 60,
    width: 80,
  },
  tooltip: {
    x: {
      show: false,
    },
    y: {
      title: {
        formatter: function (seriesName) {
          return "";
        },
      },
    },
    marker: {
      show: false,
    },
  },
  colors: ["#db555d"],
  stroke: {
    width: [1.5],
    curve: ["smooth"],
  },
  xaxis: {
    crosshairs: {
      show: false,
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      opacityFrom: 0.9,
      opacityTo: 0.9,
      stops: [0, 98],
    },
  },
};
var chart1 = new ApexCharts(document.querySelector("#ethCoin1"), options1);
chart1.render();

var options = {
  series: [
    {
      name: "Revenue",
      data: [55, 52, 55, 52, 55, 55, 58, 58, 53, 55, 54, 55],
    },
  ],
  chart: {
    height: 123,
    type: "area",
    sparkline: {
      enabled: true,
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: [1.4],
    show: true,
    curve: ["smooth"],
  },
  xaxis: {
    crosshairs: {
      show: false,
    },
  },
  legend: {
    show: false,
  },
  labels: [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ],
  markers: {
    size: 0,
  },
  colors: ["#00a5a2"],
  fill: {
    type: "gradient",
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.7,
      stops: [0, 100],
    },
  },
};
document.getElementById("totalRevenue").innerHTML = "";
var charttotalRevenue = new ApexCharts(
  document.querySelector("#totalRevenue"),
  options
);
charttotalRevenue.render();

function totalRevenue() {
  function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1);
  }
  charttotalRevenue.updateOptions({
    colors: [
      rgbToHex(
        myVarVal.split(",")[0],
        myVarVal.split(",")[1],
        myVarVal.split(",")[2]
      ),
    ],
  });
}

/* updating donut chart */
var options = {
  series: [44, 55, 33, 55, 33],
  labels: ["Accepted", "Rejected", "Dispatched", "Delivered", "Cancelled"],
  chart: {
    height: 230,
    type: "donut",
  },
  dataLabels: {
    enabled: false,
  },
  responsive: [
    {
      breakpoint: 480,
      options: {
        chart: {
          width: 200,
        },
        legend: {
          show: false,
        },
      },
    },
  ],
  colors: ["#00a5a2c9", "#17b1dfb0", "#f7b725bf", "#44b86cbf", "#be495fbd"],
  legend: {
    show: true,
    position: "right",
    markers: {
      width: 10,
      height: 10,
    },
  },
  stroke: {
    show: true,
    width: 0,
    colors: ["transparent"],
  },
};
document.getElementById("donut-update").innerHTML = "";
var chartdonutupdate = new ApexCharts(
  document.querySelector("#donut-update"),
  options
);
chartdonutupdate.render();

function donutupdate() {
  chartdonutupdate.updateOptions({
    colors: [
      "rgb(" + myVarVal + ")",
      "#17b1dfb0",
      "#f7b725bf",
      "#44b86cbf",
      "#e82646c2",
    ],
  });
}

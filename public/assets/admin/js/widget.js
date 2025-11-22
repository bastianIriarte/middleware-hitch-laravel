"use strict";

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
document.getElementById("widgetEtcoin").innerHTML = "";
var chartethCoin = new ApexCharts(
  document.querySelector("#widgetEtcoin"),
  options
);
chartethCoin.render();

function widgetEtcoin() {
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
var chart1 = new ApexCharts(document.querySelector("#total-orders"), options1);
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
document.getElementById("totalRevenue2").innerHTML = "";
var charttotalRevenue = new ApexCharts(
  document.querySelector("#totalRevenue2"),
  options
);
charttotalRevenue.render();


function totalRevenue2() {
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

// Target report chart
var options = {
  series: [
    {
      data: [17, 22, 28, 23, 15, 40, 14, 22, 37, 47, 39],
      name: "Revenue",
    },
  ],
  chart: {
    type: "bar",
    height: 235,
    toolbar: {
      show: false,
    },
    dropShadow: {
      enabled: true,
      enabledOnSeries: undefined,
      top: 6,
      left: 6,
      blur: 3,
      color: "#000",
      opacity: 0.05,
    },
  },
  plotOptions: {
    bar: {
      columnWidth: "20%",
      borderRadius: 4,
      horizontal: false,
      colors: {
        ranges: [
          {
            from: 41,
            to: Infinity,
            color: "#6dd6d4",
          },
          {
            from: 0,
            to: 40,
            color: "#6dd6d4",
          },
        ],
      },
    },
  },
  dataLabels: {
    enabled: false,
  },
  grid: {
    show: false,
    borderColor: "transparent",
    padding: {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
    },
    yaxis: {
      lines: {
        show: false,
      },
    },
  },
  xaxis: {
    categories: [
      "Jan",
      "Feb",
      "Mar",
      "Apl",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
    ],
    colors: "#fff",
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
    labels: {
      rotate: -90,
      style: {
        fontFamily: "Inter, sans-serif",
      },
    },
  },
  yaxis: {
    colors: "#fff",
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
    labels: {
      show: false,
    },
  },
};
document.querySelector("#reports").innerHTML = "";
var chart = new ApexCharts(document.querySelector("#reports"), options);
chart.render();

/* maps with lines */
var markers = [
  { name: "Russia", coords: [61.524, 105.3188] },
  { name: "Egypt", coords: [26.8206, 30.8025] },
  { name: "Greenland", coords: [71.7069, -42.6043], offsets: [2, 10] },
  { name: "Canada", coords: [56, -106], offsets: [-7, 12] },
  { name: "Brazil", coords: [-14.235, -51.9253], offsets: [-7, 12] },
];

var lines = [
  {
    from: "Russia",
    to: "Egypt",
    style: { stroke: "#abb0b7", strokeWidth: 1.5 },
  },
  {
    from: "Canada",
    to: "Egypt",
    style: { stroke: "#abb0b7", strokeWidth: 1.5 },
  },
  {
    from: "Greenland",
    to: "Egypt",
    style: { stroke: "#abb0b7", strokeWidth: 1.5 },
  },
  {
    from: "Brazil",
    to: "Egypt",
    style: { stroke: "#abb0b7", strokeWidth: 1.5 },
  },
];
new jsVectorMap({
  map: "world_merc",
  selector: document.querySelector("#lines-map"),
  // -------- Labels --------
  labels: {
    markers: {
      render: function (marker) {
        return marker.name;
      },
      offsets: function (index) {
        return markers[index].offsets || [0, 0];
      },
    },
  },
  // -------- Marker and label style --------
  markers: markers,
  lines: lines,
  lineStyle: {
    animation: true,
    strokeDasharray: "6 3 6",
  },
  markerStyle: {
    initial: {
      r: 6,
      fill: "#00a5a2",
      stroke: "#fff",
      strokeWidth: 3,
    },
  },
  markerLabelStyle: {
    initial: {
      fontSize: 13,
      fontWeight: 500,
      fill: "#35373e",
    },
  },
});

$(function() {
	"use strict";

    /*LIne-Chart */
    var ctx = document.getElementById("chartLine").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Sun", "Mon", "Tus", "Wed", "Thu", "Fri", "Sat"],
            datasets: [{
                label: 'Profits',
                data: [100, 420, 210, 420, 210, 320, 350],
                borderWidth: 2,
                backgroundColor: 'transparent',
                borderColor: '#00a5a2',
                borderWidth: 3,
                lineTension:0.3,
                pointBackgroundColor: '#ffffff',
                pointRadius: 2
            }, {
                label: 'Expenses',
                data: [450, 200, 350, 250, 480, 200, 400],
                borderWidth: 2,
                backgroundColor: 'transparent',
                borderColor: '#dbb255',
                borderWidth: 3,
                lineTension:0.3,
                pointBackgroundColor: '#ffffff',
                pointRadius: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                x: {
                    ticks: {
                        color: "#9ba6b5",
                    },
                    display: true,
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    }
                },
                y: {
                    ticks: {
                        color: "#9ba6b5",
                    },
                    display: true,
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    },
                    scaleLabel: {
                        display: false,
                        labelString: 'Thousands',
                        fontColor: 'rgba(119, 119, 142, 0.2)'
                    }
                }
            },
            legend: {
                labels: {
                    fontColor: "#9ba6b5"
                },
            },
        }
    });

    /* Bar-Chart1 */
    var ctx = document.getElementById("chartBar1").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"],
            datasets: [{
                label: 'Sales',
                data: [200, 450, 290, 367, 256, 543, 345, 290, 367],
                backgroundColor: [
                    'rgba(0, 165, 162, 0.2)',
                    'rgba(219, 85, 93, 0.2)',
                    'rgba(105, 183, 71, 0.2)',
                    'rgba(252, 115, 3, 0.2)',
                    'rgba(23, 177, 223, 0.2)',
                    'rgba(219, 178, 85, 0.2)'
                ],
                borderColor: [
                    'rgb(0, 165, 162)',
                    'rgb(219, 85, 93)',
                    'rgb(105, 183, 71)',
                    'rgb(252, 115, 3)',
                    'rgb(23, 177, 223)',
                    'rgb(219, 178, 85)'
                ],
                borderWidth: 1,
                pointBackgroundColor: '#ffffff',

            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true
            },
            scales: {
                y: {
                    ticks: {
                        beginAtZero: true,
                        stepSize: 150,
                        color: "#9ba6b5",
                    },
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    }
                },
                x: {
                    barPercentage: 0.4,
                    barValueSpacing: 0,
                    barDatasetSpacing: 0,
                    barRadius: 0,
                    ticks: {
                        display: true,
                        color: "#9ba6b5",
                    },
                    grid: {
                        display: false,
                        color: 'rgba(119, 119, 142, 0.2)'
                    }
                }
            },
            legend: {
                labels: {
                    fontColor: "#9ba6b5"
                },
            },
        }
    });

    /* Bar-Chart2*/
    var ctx = document.getElementById("chartBar2");
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
            datasets: [{
                label: "Data1",
                data: [65, 59, 80, 81, 56, 55, 40],
                borderColor: "#00a5a2",
                borderWidth: "0",
                backgroundColor: "#00a5a2"
            }, {
                label: "Data2",
                data: [28, 48, 40, 19, 86, 27, 90],
                borderColor: "#dbb255",
                borderWidth: "0",
                backgroundColor: "#dbb255"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    barPercentage: 0.4,
                    barValueSpacing: 0,
                    barDatasetSpacing: 0,
                    barRadius: 0,
                    ticks: {
                        color: "#9ba6b5",
                    },
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    }
                },
                y: {
                    ticks: {
                        beginAtZero: true,
                        color: "#9ba6b5",
                    },
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    },
                }
            },
            legend: {
                labels: {
                    color: "#9ba6b5"
                },
            },
        }
    });

    /* Area Chart*/
    var ctx = document.getElementById("chartArea");
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
            datasets: [{
                label: "Data1",
                borderColor: "#00a5a2",
                borderWidth: "3",
                lineTension:0.3,
                backgroundColor: "rgba(0, 165, 162, .1)",
                fill: true,
                data: [22, 44, 67, 43, 76, 45, 12]
            }, {
                label: "Data2",
                borderColor: "rgba(219, 178, 85 ,0.9)",
                borderWidth: "3",
                lineTension:0.3,
                backgroundColor: "rgba(219, 178, 85, 0.7)",
                pointHighlightStroke: "rgba(219, 178, 85 ,1)",
                fill: true,
                data: [16, 32, 18, 26, 42, 33, 44]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
                mode: 'index',
                intersect: false
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                x: {
                    ticks: {
                        color: "#9ba6b5",
                    },
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    }
                },
                yAxes: {
                    ticks: {
                        beginAtZero: true,
                        color: "#9ba6b5",
                    },
                    grid: {
                        color: 'rgba(119, 119, 142, 0.2)'
                    },
                }
            },
            legend: {
                labels: {
                    color: "#9ba6b5"
                },
            },
        }
    });

    /* Pie Chart*/
    var datapie = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
        datasets: [{
            data: [20, 20, 30, 5, 25],
            backgroundColor: ['#00a5a2', '#dbb255', '#09ad95', '#1170e4', '#e82646']
        }]
    };
    var optionpie = {
        maintainAspectRatio: false,
        responsive: true,
        legend: {
            display: false,
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    };

    /* Doughbut Chart*/
    var ctx6 = document.getElementById('chartPie');
    var myPieChart6 = new Chart(ctx6, {
        type: 'doughnut',
        data: datapie,
        options: optionpie
    });

    /* Pie Chart*/
    var ctx7 = document.getElementById('chartDonut');
    var myPieChart7 = new Chart(ctx7, {
        type: 'pie',
        data: datapie,
        options: optionpie
    });

    /* Radar chart*/
    var ctx = document.getElementById("chartRadar");
    var myChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: [

                ["Eating", "Dinner"],
                ["Drinking", "Water"], "Sleeping", ["Designing", "Graphics"], "Coding", "Cycling", "Running",

            ],
            datasets: [{

                label: "Data1",
                data: [65, 59, 66, 45, 56, 55, 40],
                borderColor: "rgba(0, 165, 162, .8)",
                borderWidth: "1",
                backgroundColor: "rgba(0, 165, 162, .4)"
            }, {
                label: "Data2",
                data: [28, 12, 40, 19, 63, 27, 87],
                borderColor: "rgba(219, 85, 93,0.8)",
                borderWidth: "1",
                backgroundColor: "rgba(219, 85, 93,0.4)"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scale: {
                angleLines: { color: '#9ba6b5' },
                grid: {
                    color: 'rgba(119, 119, 142, 0.2)'
                },
                ticks: {
                    beginAtZero: true,
                    color: 'rgba(119, 119, 142, 0.2)'
                },
                pointLabels: {
                    color: '#9ba6b5',
                },
            },

        }
    });

    /* polar chart */
    var ctx = document.getElementById("chartPolar");
    var myChart = new Chart(ctx, {
        type: 'polarArea',
        data: {
            datasets: [{
                data: [18, 15, 9, 6, 19],
                backgroundColor: ['#00a5a2', '#dbb255', '#09ad95', '#1170e4', '#e82646'],
                hoverBackgroundColor: ['#00a5a2', '#dbb255', '#09ad95', '#1170e4', '#e82646'],
                borderColor: 'transparent',
            }],
            labels: ["Data1", "Data2", "Data3", "Data4"]
        },
        options: {
            scale: {
                grid: {
                    color: 'rgba(119, 119, 142, 0.2)'
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                labels: {
                    color: "#9ba6b5"
                },
            },
        }
    });

});
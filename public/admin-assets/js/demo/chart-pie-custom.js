// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Pie Chart Example
var ctx = document.getElementById("myPieChart");

// Use real category data if available, otherwise use default data
var categoryData = window.categoryData || {
  labels: ["Clothing", "Accessories", "Footwear", "Other"],
  data: [45, 25, 15, 15]
};

var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: categoryData.labels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),    datasets: [{
      data: categoryData.data,
      backgroundColor: ['#F28123', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997', '#6610f2', '#dc3545'],
      hoverBackgroundColor: ['#d16a0d', '#17a673', '#2c9faf', '#dda20a', '#c0392b', '#5a32a3', '#e8590c', '#1ba085', '#520dc2', '#b02a37'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.labels[tooltipItem.index] || '';
          var value = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
          return datasetLabel + ': ' + value + '%';
        }
      }
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});

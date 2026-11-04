
// SIMULATED WATER LEVEL READINGS

const analyticsData = {
  daily: {
    labels: [
      "6 AM", "8 AM", "10 AM", "12 PM",
      "2 PM", "4 PM", "6 PM", "8 PM"
    ],
    levels: [35, 48, 63, 72, 58, 76, 84, 72]
  },

  weekly: {
    labels: [
      "Mon", "Tue", "Wed", "Thu",
      "Fri", "Sat", "Sun"
    ],
    levels: [62, 75, 68, 82, 70, 88, 72]
  },

  monthly: {
    labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
    levels: [60, 73, 81, 72]
  }
};

let currentPeriod = "daily";

const chartContext = document
  .getElementById("waterLevelChart")
  .getContext("2d");

// INITIALIZE CHART

const waterLevelChart = new Chart(chartContext, {
  type: "line",

  data: {
    labels: analyticsData.daily.labels,

    datasets: [{
      label: "Water Level (%)",
      data: analyticsData.daily.levels,
      borderColor: "#258bd7",
      backgroundColor: "rgba(37, 139, 215, 0.12)",
      borderWidth: 3,
      pointRadius: 5,
      pointBackgroundColor: "#258bd7",
      tension: 0.35,
      fill: true
    }]
  },

  options: {
    responsive: true,
    maintainAspectRatio: false,

    plugins: {
      legend: {
        display: true,
        position: "top"
      },
      tooltip: {
        callbacks: {
          label: context => `Water Level: ${context.parsed.y}%`
        }
      }
    },

    scales: {
      y: {
        min: 0,
        max: 100,
        title: {
          display: true,
          text: "Water Level (%)"
        }
      },

      x: {
        title: {
          display: true,
          text: "Time Period"
        }
      }
    }
  }
});

// UPDATE SUMMARY CARDS

function updateStatistics(period) {
  const levels = analyticsData[period].levels;

  const total = levels.reduce((sum, level) => sum + level, 0);
  const average = total / levels.length;
  const highest = Math.max(...levels);
  const lowest = Math.min(...levels);
  const latest = levels[levels.length - 1];

  document.getElementById("averageLevel").textContent =
    average.toFixed(1) + "%";

  document.getElementById("highestLevel").textContent =
    highest + "%";

  document.getElementById("lowestLevel").textContent =
    lowest + "%";

  document.getElementById("latestLevel").textContent =
    latest + "%";

  document.getElementById("readingCount").textContent =
    levels.length;

  document.getElementById("selectedPeriod").textContent =
    period.charAt(0).toUpperCase() + period.slice(1);
}

// CHANGE CHART PERIOD

function updateChart(period) {
  currentPeriod = period;

  waterLevelChart.data.labels = analyticsData[period].labels;
  waterLevelChart.data.datasets[0].data =
    analyticsData[period].levels;

  waterLevelChart.update();
  updateStatistics(period);
}

// FILTER BUTTONS

document.querySelectorAll(".period-btn").forEach(button => {
  button.addEventListener("click", () => {
    document.querySelectorAll(".period-btn").forEach(btn => {
      btn.classList.remove("active");
    });

    button.classList.add("active");
    updateChart(button.dataset.period);
  });
});

// LOGOUT MODAL

const logoutDialog = document.getElementById("logoutDialog");

document.getElementById("logoutBtn").addEventListener("click", () => {
  logoutDialog.showModal();
});

document.getElementById("cancelLogout").addEventListener("click", () => {
  logoutDialog.close();
});

document.getElementById("confirmLogout").addEventListener("click", () => {
  // Prototype only; backend session handling comes later.
  window.location.href = "/watermonitoring/index.html";
});

// LOAD INITIAL STATISTICS

updateStatistics(currentPeriod);


/* ==========================================
   DEMO WATER LEVEL DATA
========================================== */

const historyData = [
  { id: 1008, date: "2026-03-11", time: "20:00", level: 72 },
  { id: 1007, date: "2026-03-11", time: "18:00", level: 84 },
  { id: 1006, date: "2026-03-11", time: "16:00", level: 76 },
  { id: 1005, date: "2026-03-11", time: "14:00", level: 58 },
  { id: 1004, date: "2026-03-11", time: "12:00", level: 72 },
  { id: 1003, date: "2026-03-11", time: "10:00", level: 63 },
  { id: 1002, date: "2026-03-11", time: "08:00", level: 48 },
  { id: 1001, date: "2026-03-11", time: "06:00", level: 35 }
];


/* ==========================================
   GET ELEMENTS
========================================== */

const tableBody = document.getElementById("historyTableBody");

const searchInput = document.getElementById("searchInput");
const dateFilter = document.getElementById("dateFilter");
const statusFilter = document.getElementById("statusFilter");

const totalRecords = document.getElementById("totalRecords");
const highestLevel = document.getElementById("highestLevel");
const lowestLevel = document.getElementById("lowestLevel");
const latestLevel = document.getElementById("latestLevel");

const recordCount = document.getElementById("recordCount");

let filteredData = [];


/* ==========================================
   GET WATER LEVEL STATUS
========================================== */

// Demo thresholds only.
// Replace with actual project thresholds later.

function getStatus(level) {

  if (level >= 80) {
    return "High";
  }

  if (level <= 20) {
    return "Low";
  }

  return "Normal";

}


/* ==========================================
   FORMAT TIME
========================================== */

function formatTime(time) {

  const [hours, minutes] = time.split(":");

  const hour = Number(hours);
  const displayHour = hour % 12 || 12;
  const period = hour >= 12 ? "PM" : "AM";

  return `${displayHour}:${minutes} ${period}`;

}


/* ==========================================
   DISPLAY HISTORY TABLE
========================================== */

function displayHistory() {

  const searchValue = searchInput.value.toLowerCase().trim();
  const selectedDate = dateFilter.value;
  const selectedStatus = statusFilter.value;

  filteredData = historyData.filter(function(record) {

    const status = getStatus(record.level);

    const searchableText = [
      record.id,
      record.date,
      record.time,
      formatTime(record.time),
      record.level + "%",
      status
    ].join(" ").toLowerCase();

    const matchesSearch = searchableText.includes(searchValue);

    const matchesDate =
      selectedDate === "" ||
      record.date === selectedDate;

    const matchesStatus =
      selectedStatus === "all" ||
      status === selectedStatus;

    return matchesSearch && matchesDate && matchesStatus;

  });

  tableBody.innerHTML = "";

  if (filteredData.length === 0) {

    const row = document.createElement("tr");
    const cell = document.createElement("td");

    cell.colSpan = 5;
    cell.className = "empty-state";
    cell.textContent = "No records found.";

    row.appendChild(cell);
    tableBody.appendChild(row);

  } else {

    filteredData.forEach(function(record) {

      const status = getStatus(record.level);
      const row = document.createElement("tr");

      const values = [
        "#" + record.id,
        record.date,
        formatTime(record.time),
        record.level + "%"
      ];

      values.forEach(function(value) {

        const cell = document.createElement("td");
        cell.textContent = value;
        row.appendChild(cell);

      });

      const statusCell = document.createElement("td");
      const badge = document.createElement("span");

      badge.className =
        "history-status " + status.toLowerCase();

      badge.textContent =
        status === "Normal" ? "Normal" : status + " Level";

      statusCell.appendChild(badge);
      row.appendChild(statusCell);

      tableBody.appendChild(row);

    });

  }

  updateSummary();

}


/* ==========================================
   UPDATE SUMMARY CARDS
========================================== */

function updateSummary() {

  const count = filteredData.length;

  totalRecords.textContent = count;
  recordCount.textContent = `Showing ${count} record(s)`;

  if (count === 0) {

    highestLevel.textContent = "--";
    lowestLevel.textContent = "--";
    latestLevel.textContent = "--";

    return;

  }

  const levels = filteredData.map(function(record) {
    return record.level;
  });

  const latestRecord = [...filteredData].sort(function(a, b) {
    return (b.date + b.time).localeCompare(a.date + a.time);
  })[0];

  highestLevel.textContent = Math.max(...levels) + "%";
  lowestLevel.textContent = Math.min(...levels) + "%";
  latestLevel.textContent = latestRecord.level + "%";

}


/* ==========================================
   SEARCH AND FILTER EVENTS
========================================== */

searchInput.addEventListener("input", displayHistory);

dateFilter.addEventListener("change", displayHistory);

statusFilter.addEventListener("change", displayHistory);


/* ==========================================
   CLEAR FILTERS
========================================== */

document.getElementById("clearFilters").addEventListener(
  "click",
  function() {

    searchInput.value = "";
    dateFilter.value = "";
    statusFilter.value = "all";

    displayHistory();

  }
);


/* ==========================================
   EXPORT CSV
========================================== */

document.getElementById("exportBtn").addEventListener(
  "click",
  function() {

    const rows = [
      ["Record ID", "Date", "Time", "Water Level", "Status"]
    ];

    filteredData.forEach(function(record) {

      rows.push([
        record.id,
        record.date,
        formatTime(record.time),
        record.level + "%",
        getStatus(record.level)
      ]);

    });

    const csvContent = rows.map(function(row) {
      return row.join(",");
    }).join("\r\n");

    const blob = new Blob([csvContent], {
      type: "text/csv;charset=utf-8;"
    });

    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");

    link.href = url;
    link.download = "water-level-history.csv";

    document.body.appendChild(link);
    link.click();
    link.remove();

    setTimeout(function() {
      URL.revokeObjectURL(url);
    }, 1000);

  }
);


/* ==========================================
   LOGOUT CONFIRMATION
========================================== */

const logoutBtn = document.getElementById("logoutBtn");
const logoutDialog = document.getElementById("logoutDialog");

const cancelLogout = document.getElementById("cancelLogout");
const confirmLogout = document.getElementById("confirmLogout");

logoutBtn.addEventListener("click", function() {

  logoutDialog.showModal();

});

cancelLogout.addEventListener("click", function() {

  logoutDialog.close();

});

confirmLogout.addEventListener("click", function() {

  // Frontend demo only.
  // Real logout requires backend session handling.

  window.location.href = "/watermonitoring/index.html";

});


/* ==========================================
   INITIALIZE PAGE
========================================== */

displayHistory();

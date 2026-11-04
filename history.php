
<?php
require_once __DIR__ . "/API/auth.php";
require_once __DIR__ . "/API/db.php";
requireLogin();

$stmt = $pdo->prepare(
    "SELECT full_name, email, role, status
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION["user_id"]]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $currentUser["status"] !== "Active") {
    http_response_code(403);
    exit("Account access denied.");
}

$displayName = $currentUser["full_name"];
$displayEmail = $currentUser["email"];
$displayRole = $currentUser["role"];
$displayStatus = $currentUser["status"];

$words = preg_split('/\s+/', trim($displayName));
$initials = "";

foreach (array_slice($words, 0, 2) as $word) {
    $initials .= strtoupper(substr($word, 0, 1));
}

function escapeHtml($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>History Logs | El Royale</title>

  <!-- SAME SHARED CSS AS ANALYTICS -->
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="history.css">

  <script src="history.js" defer></script>
</head>

<body>

  <div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <div class="brand">
        <div class="brand-icon">💧</div>
        <div>
          <h3>El Royale</h3>
          <small>WATER MONITOR</small>
        </div>
      </div>

      <p class="menu-label">MAIN MENU</p>

      <nav>
        <a href="/watermonitoring/dashboard.php">
          <span class="nav-icon">◫</span>
          <span class="nav-label">Monitoring</span>
        </a>

        <a href="/watermonitoring/notifications.php">
          <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 2.75a5.75 5.75 0 0 0-5.75 5.75v4.12L4.5 15.3a1 1 0 0 0 .8 1.62h13.4a1 1 0 0 0 .8-1.62l-1.75-2.68V8.5A5.75 5.75 0 0 0 12 2.75zm0 18.5a2.8 2.8 0 0 1-2.76-2.25h5.52A2.8 2.8 0 0 1 12 21.25z"/>
          </svg>
          <span class="nav-label">Notifications</span>
        </a>

        <a href="/watermonitoring/analytics.php">
          <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 19h16v2H4zm1-2h2V9H5zm5 0h2V5h-2zm5 0h2V11h-2zm5 0h2V7h-2z"/>
          </svg>
          <span class="nav-label">Water Level Analytics</span>
        </a>

        <a href="/watermonitoring/history.php" class="active">
          <span class="nav-icon">◷</span>
          <span class="nav-label">History Logs</span>
        </a>

        
        <?php if (($_SESSION["role"] ?? "") === "Administrator"): ?>
          <a href="/watermonitoring/users.php">
            <span class="nav-icon">♙</span>
            <span class="nav-label">User Management</span>
          </a>
        <?php endif; ?>


        <a href="/watermonitoring/settings.php">
          <span class="nav-icon">⚙</span>
          <span class="nav-label">Account Settings</span>
        </a>
      </nav>

      <!-- SIDEBAR FOOTER -->
      <div class="sidebar-footer">

        <div class="avatar"><?= escapeHtml($initials) ?></div>

        <div class="user-info">
          <strong><?= escapeHtml($displayName) ?></strong>
          <small><?= escapeHtml($displayRole) ?> · Active</small>
        </div>

        <button id="logoutBtn" aria-label="Log out">
          ↪
        </button>

      </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

      <!-- PAGE HEADER -->
      <header class="topbar">

        <div>
          <p class="breadcrumb">
            Dashboard / History Logs
          </p>

          <h1>History Logs</h1>

          <p class="subtitle">
            View and manage historical water level readings.
          </p>
        </div>

        <span class="demo-label">
          Demo Data
        </span>

      </header>

      <!-- SUMMARY CARDS -->
      <section class="history-stats">

        <div class="stat-card">
          <span>Total Records</span>
          <h2 id="totalRecords">--</h2>
          <p>Number of recorded readings</p>
        </div>

        <div class="stat-card">
          <span>Highest Water Level</span>
          <h2 id="highestLevel">--</h2>
          <p>Maximum recorded level</p>
        </div>

        <div class="stat-card">
          <span>Lowest Water Level</span>
          <h2 id="lowestLevel">--</h2>
          <p>Minimum recorded level</p>
        </div>

        <div class="stat-card">
          <span>Latest Water Level</span>
          <h2 id="latestLevel">--</h2>
          <p>Most recent recorded reading</p>
        </div>

      </section>

      <!-- HISTORY LOGS TABLE -->
      <section class="card history-table-card">

        <div class="history-table-header">

          <div>
            <h3>Water Level Records</h3>
            <p>
              Browse, search, and filter recorded
              water level data.
            </p>
          </div>

          <button id="exportBtn" class="export-btn">
            ↓ Export CSV
          </button>

        </div>

        <!-- FILTERS -->
        <div class="history-filters">

          <input
            type="search"
            id="searchInput"
            placeholder="Search records..."
            aria-label="Search records"
          >

          <input
            type="date"
            id="dateFilter"
            aria-label="Filter by date"
          >

          <select id="statusFilter"
                  aria-label="Filter by status">
            <option value="all">All Status</option>
            <option value="Normal">Normal</option>
            <option value="High">High Level</option>
            <option value="Low">Low Level</option>
          </select>

          <button id="clearFilters" class="clear-btn">
            Clear Filters
          </button>

        </div>

        <!-- TABLE -->
        <div class="table-container">

          <table class="history-table">

            <thead>
              <tr>
                <th>Record ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Water Level</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody id="historyTableBody">
              <!-- Data generated by JavaScript -->
            </tbody>

          </table>

        </div>

        <!-- TABLE FOOTER -->
        <div class="history-table-footer">
          <span id="recordCount">
            Showing 0 records
          </span>

          <span>
            Simulated monitoring data
          </span>
        </div>

      </section>

      <!-- INFORMATION CARD -->
      <section class="card history-info-card">

        <h3>History Logs Information</h3>

        <p>
          This page displays historical water level
          measurements recorded by the monitoring system.
          Authorized personnel can review past readings,
          filter records, and export monitoring data.
        </p>

        <div class="info-note">
          The displayed records are simulated for
          frontend testing. Actual ESP32 sensor readings
          will be stored and retrieved from the database
          after backend integration.
        </div>

      </section>

    </main>

  </div>

  <!-- SAME LOGOUT DIALOG AS ANALYTICS -->
  <dialog id="logoutDialog" class="logout-dialog">

    <h2>Log Out?</h2>

    <p>
      Are you sure you want to log out of your account?
    </p>

    <div class="dialog-actions">

      <button id="cancelLogout">
        Cancel
      </button>

      <button id="confirmLogout">
        Log Out
      </button>

    </div>

  </dialog>

</body>
</html>


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
  <title>Water Level Analytics | El Royale</title>

  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="analytics.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
  <script src="analytics.js" defer></script>
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
        <a href="/watermonitoring/analytics.php" class="active">
          <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M4 19h16v2H4zm1-2h2V9H5zm5 0h2V5h-2zm5 0h2V11h-2zm5 0h2V7h-2z"/>
          </svg>
          <span class="nav-label">Water Level Analytics</span>
        </a>
        <a href="/watermonitoring/history.php">
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

      <div class="sidebar-footer">
        <div class="avatar">
          <?= escapeHtml($initials) ?>
        </div>

        <div class="user-info">
          <strong><?= escapeHtml($displayName) ?></strong>
          <small><?= escapeHtml($displayRole) ?></small>
        </div>
        <button id="logoutBtn" aria-label="Log out">↪</button>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

      <header class="topbar">
        <div>
          <p class="breadcrumb">Dashboard / Analytics</p>
          <h1>Water Level Analytics</h1>
          <p class="subtitle">
            View water level trends and monitoring statistics.
          </p>
        </div>
        <span class="demo-label">Demo Data</span>
      </header>

      <!-- SUMMARY CARDS -->
      <section class="analytics-stats">

        <div class="stat-card">
          <span>Average Water Level</span>
          <h2 id="averageLevel">--</h2>
          <p>Based on selected period</p>
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
          <p>Last reading in selected period</p>
        </div>

      </section>

      <!-- WATER LEVEL CHART -->
      <section class="card analytics-chart-card">

        <div class="analytics-chart-header">
          <div>
            <h3>Water Level Trends</h3>
            <p>Water level percentage over time</p>
          </div>

          <div class="period-buttons">
            <button class="period-btn active" data-period="daily">
              Daily
            </button>
            <button class="period-btn" data-period="weekly">
              Weekly
            </button>
            <button class="period-btn" data-period="monthly">
              Monthly
            </button>
          </div>
        </div>

        <div class="chart-container">
          <canvas id="waterLevelChart"></canvas>
        </div>

        <p class="chart-note">
          This chart uses simulated readings for frontend testing.
          Actual sensor data will be connected later.
        </p>

      </section>

      <!-- ANALYTICS INFO -->
      <section class="analytics-bottom">

        <div class="card">
          <h3>Water Level Summary</h3>

          <div class="system-row">
            <span>Monitoring Period</span>
            <strong id="selectedPeriod">Daily</strong>
          </div>

          <div class="system-row">
            <span>Number of Readings</span>
            <strong id="readingCount">--</strong>
          </div>

          <div class="system-row">
            <span>Water Level Unit</span>
            <strong>Percentage (%)</strong>
          </div>
        </div>

        <div class="card">
          <h3>Monitoring Information</h3>

          <p class="analytics-description">
            Water level analytics helps authorized personnel
            observe changes in tank levels and review
            historical monitoring patterns.
          </p>

          <div class="info-note">
            The chart will use stored ESP32 readings
            after backend and database integration.
          </div>
        </div>

      </section>

    </main>
  </div>

  <!-- LOGOUT CONFIRMATION -->
  <dialog id="logoutDialog" class="logout-dialog">
    <h2>Log Out?</h2>
    <p>Are you sure you want to log out of your account?</p>

    <div class="dialog-actions">
      <button id="cancelLogout">Cancel</button>
      <button id="confirmLogout">Log Out</button>
    </div>
  </dialog>

</body>
</html>

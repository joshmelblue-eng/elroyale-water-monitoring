
<?php
require_once __DIR__ . "/API/auth.php";
require_once __DIR__ . "/API/db.php";

requireLogin();

$stmt = $pdo->prepare(
    "SELECT full_name, role, status
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
$displayRole = $currentUser["role"];
$isAdmin = ($displayRole === "Administrator");

$words = preg_split('/\s+/', trim($displayName));
$initials = "";

foreach (array_slice($words, 0, 2) as $word) {
    $initials .= strtoupper(substr($word, 0, 1));
}

function escapeHtml($value) {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitoring Dashboard | El Royale</title>
  <link rel="stylesheet" href="dashboard.css">
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
        <a href="/watermonitoring/dashboard.php" class="active">
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
        <a href="/watermonitoring/history.php">
          <span class="nav-icon">◷</span>
          <span class="nav-label">History Logs</span>
        </a>
        <?php if ($isAdmin): ?>
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
          <p class="breadcrumb">Dashboard / Monitoring</p>
          <h1>Monitoring Dashboard</h1>
          <p class="subtitle">
            Real-time water tank monitoring and system overview.
          </p>
        </div>
        <span class="demo-label">
          <?= escapeHtml($displayRole) ?>
        </span>
      </header>

      <section class="stats">
        <div class="stat-card">
          <span>Current Water Level</span>
          <h2>72%</h2>
          <p>Normal water level</p>
        </div>

        <div class="stat-card">
          <span>Tank Status</span>
          <h2 class="normal">Normal</h2>
          <p>Within safe operating range</p>
        </div>

        <div class="stat-card">
          <span>Automatic Shut-Off</span>
          <h2 class="normal">Enabled</h2>
          <p>Controller protection active</p>
        </div>

        <div class="stat-card">
          <span>Connection Status</span>
          <h2 class="normal">Online</h2>
          <p>Simulated device connection</p>
        </div>
      </section>

      <section class="dashboard-grid">

        <div class="card tank-monitor">
          <div class="card-heading">
            <h3>Water Tank Monitoring</h3>
            <span class="status-badge">Normal</span>
          </div>

          <div class="tank-display">
            <div class="tank">
              <div class="water"></div>
            </div>

            <div class="level-details">
              <small>CURRENT WATER LEVEL</small>
              <h2>72%</h2>
              <p>Water level is within normal range.</p>

              <div class="progress">
                <div class="progress-fill"></div>
              </div>
              <div class="progress-labels">
                <span>Empty</span>
                <span>Full</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card system-card">
          <h3>System Overview</h3>

          <div class="system-row">
            <span>Water Level Sensor</span>
            <strong>Active</strong>
          </div>

          <div class="system-row">
            <span>ESP32 Controller</span>
            <strong>Connected</strong>
          </div>

          <div class="system-row">
            <span>Automatic Shut-Off</span>
            <strong>Enabled</strong>
          </div>

          <div class="system-row">
            <span>Last Update</span>
            <strong>Demo Reading</strong>
          </div>

          <div class="info-note">
            Automatic shut-off is handled by the
            controller when the tank reaches full capacity.
          </div>
        </div>

      </section>

      <section class="card activity-card">
        <h3>Recent Activity</h3>
        <p>
          No actual activity records yet.
          System events will appear here after backend integration.
        </p>
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

  
<script>
document.addEventListener("DOMContentLoaded", () => {
  const logoutDialog = document.getElementById("logoutDialog");
  const logoutBtn = document.getElementById("logoutBtn");
  const cancelLogout = document.getElementById("cancelLogout");
  const confirmLogout = document.getElementById("confirmLogout");

  logoutBtn.addEventListener("click", () => {
    logoutDialog.showModal();
  });

  cancelLogout.addEventListener("click", () => {
    logoutDialog.close();
  });

  confirmLogout.addEventListener("click", async () => {
    confirmLogout.disabled = true;
    confirmLogout.textContent = "Logging out...";

    try {
      const tokenResponse = await fetch("/watermonitoring/API/csrf.php", {
        credentials: "same-origin",
        cache: "no-store"
      });

      const tokenData = await tokenResponse.json();

      if (!tokenResponse.ok || !tokenData.csrf_token) {
        throw new Error("Could not get security token.");
      }

      const response = await fetch("/watermonitoring/API/logout.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "X-CSRF-Token": tokenData.csrf_token
        }
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || "Logout failed.");
      }

      window.location.replace("/watermonitoring/index.html");

    } catch (error) {
      alert(error.message);
      confirmLogout.disabled = false;
      confirmLogout.textContent = "Log Out";
    }
  });
});
</script>


</body>
</html>

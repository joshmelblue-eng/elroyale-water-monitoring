
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
  <title>Notifications | El Royale</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="notifications.css">
  <script src="notifications.js" defer></script>
</head>
<body>
  <div class="app">
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
        <a href="/watermonitoring/notifications.php" class="active">
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
        <div class="avatar"><?= escapeHtml($initials) ?></div>
        <div class="user-info">
          <strong><?= escapeHtml($displayName) ?></strong>
          <small><?= escapeHtml($displayRole) ?> · Active</small>
        </div>
        <button id="logoutBtn" aria-label="Log out">↪</button>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <div>
          <p class="breadcrumb">Dashboard / Notifications</p>
          <h1>Notifications & Alerts</h1>
          <p class="subtitle">Monitor important water tank and system events.</p>
        </div>
        <span class="demo-label">Demo Data</span>
      </header>

      <section class="notification-stats">
        <div class="stat-card">
          <span>Total Notifications</span>
          <h2 id="totalCount">0</h2>
          <p>All demo notifications</p>
        </div>
        <div class="stat-card">
          <span>Unread Notifications</span>
          <h2 id="unreadCount">0</h2>
          <p>Require your attention</p>
        </div>
        <div class="stat-card">
          <span>Critical Alerts</span>
          <h2 id="criticalCount">0</h2>
          <p>High-priority events</p>
        </div>
      </section>

      <section class="card notifications-panel">
        <div class="notifications-heading">
          <div>
            <h3>Recent Notifications</h3>
            <p>System-generated alerts and updates</p>
          </div>
          <button id="markAllBtn" class="mark-all-btn">
            Mark All as Read
          </button>
        </div>

        <div class="notification-filters">
          <button class="filter-btn active" data-filter="all">All</button>
          <button class="filter-btn" data-filter="unread">Unread</button>
          <button class="filter-btn" data-filter="critical">Critical</button>
        </div>

        <div id="notificationList"></div>
      </section>
    </main>
  </div>

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


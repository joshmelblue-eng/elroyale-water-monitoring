
<?php
require_once __DIR__ . "/API/auth.php";
require_once __DIR__ . "/API/db.php";

requireAdmin();

$stmt = $pdo->prepare(
    "SELECT full_name, role, status
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION["user_id"]]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$currentUser ||
    $currentUser["role"] !== "Administrator" ||
    $currentUser["status"] !== "Active"
) {
    http_response_code(403);
    exit("Administrator access required.");
}

$displayName = $currentUser["full_name"];
$displayRole = $currentUser["role"];

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
  <title>User Management | El Royale</title>

  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="users.css">

  <script src="users.js" defer></script>
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
      <a href="/watermonitoring/history.php">
        <span class="nav-icon">◷</span>
        <span class="nav-label">History Logs</span>
      </a>
      <a href="/watermonitoring/users.php" class="active">
        <span class="nav-icon">♙</span>
        <span class="nav-label">User Management</span>
      </a>
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
        <p class="breadcrumb">Dashboard / User Management</p>
        <h1>User Management</h1>
        <p class="subtitle">
          Manage system users and review account requests.
        </p>
      </div>
      <span class="demo-label">Administrator</span>
    </header>

    <!-- SUMMARY CARDS -->
    <section class="user-stats">
      <div class="stat-card">
        <span>Total Users</span>
        <h2 id="totalUsers">--</h2>
        <p>Registered system accounts</p>
      </div>

      <div class="stat-card">
        <span>Active Users</span>
        <h2 id="activeUsers">--</h2>
        <p>Currently active accounts</p>
      </div>

      <div class="stat-card">
        <span>Inactive Users</span>
        <h2 id="inactiveUsers">--</h2>
        <p>Deactivated accounts</p>
      </div>

      <div class="stat-card">
        <span>Pending Requests</span>
        <h2 id="pendingRequests">--</h2>
        <p>Awaiting administrator review</p>
      </div>
    </section>

    <!-- REGISTERED USERS -->
    <section class="card users-card">

      <div class="section-header">
        <div>
          <h3>Registered Users</h3>
          <p>View and manage authorized user accounts.</p>
        </div>
        <button class="primary-btn" id="addUserBtn">
          + Add User
        </button>
      </div>

      <div class="user-filters">
        <input
          type="search"
          id="searchUser"
          placeholder="Search name or email..."
          aria-label="Search users"
        >

        <select id="roleFilter" aria-label="Filter by role">
          <option value="all">All Roles</option>
          <option value="Administrator">Administrator</option>
          <option value="Maintenance Personnel">
            Maintenance Personnel
          </option>
        </select>

        <select id="statusFilter" aria-label="Filter by status">
          <option value="all">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        <button class="secondary-btn" id="clearFilters">
          Clear Filters
        </button>
      </div>

      <div class="table-container">
        <table class="users-table">
          <thead>
            <tr>
              <th>User ID</th>
              <th>Full Name</th>
              <th>Email Address</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersTableBody"></tbody>
        </table>
      </div>

      <p class="table-footer" id="userCount">
        Showing 0 users
      </p>
    </section>

    <!-- PENDING REQUESTS -->
    <section class="card requests-card">

      <div class="section-header">
        <div>
          <h3>Pending Account Requests</h3>
          <p>
            Review registration requests before granting access.
          </p>
        </div>
        <span class="pending-count" id="pendingCountLabel">
          0 Pending
        </span>
      </div>

      <div class="table-container">
        <table class="users-table">
          <thead>
            <tr>
              <th>Request ID</th>
              <th>Full Name</th>
              <th>Email Address</th>
              <th>Date Requested</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="requestsTableBody"></tbody>
        </table>
      </div>
    </section>

    <section class="card users-info-card">
      <h3>User Management Information</h3>
      <p>
        Only administrators should be allowed to manage
        user accounts, assign roles, and approve or reject
        registration requests.
      </p>
      <div class="info-note">
        This page currently uses demonstration data stored
        in your browser. Real account management and
        permissions will require backend integration.
      </div>
    </section>

  </main>
</div>

<!-- ADD / EDIT USER DIALOG -->
<dialog id="userDialog" class="user-dialog">
  <h2 id="userDialogTitle">Add User</h2>
  <p class="dialog-description">
    Enter the account information below.
  </p>

  <form id="userForm">
    <input type="hidden" id="editingUserId">

    <label for="fullName">Full Name</label>
    <input type="text" id="fullName" required
           placeholder="Enter full name">

    <label for="email">Email Address</label>
    <input type="email" id="email" required
           placeholder="Enter email address">

    <label for="userRole">User Role</label>
    <select id="userRole" required>
      <option value="Maintenance Personnel">
        Maintenance Personnel
      </option>
      <option value="Administrator">Administrator</option>
    </select>

    <label for="userStatus">Account Status</label>
    <select id="userStatus" required>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select>

    <p id="formError" class="form-error" role="alert"></p>

    <div class="dialog-actions">
      <button type="button" id="cancelUser">Cancel</button>
      <button type="submit" class="save-btn">Save User</button>
    </div>
  </form>
</dialog>

<!-- CONFIRMATION DIALOG -->
<dialog id="actionDialog" class="action-dialog">
  <h2 id="actionTitle">Confirm Action</h2>
  <p id="actionMessage"></p>

  <div class="dialog-actions">
    <button id="cancelAction">Cancel</button>
    <button id="confirmAction" class="save-btn">
      Confirm
    </button>
  </div>
</dialog>

<!-- LOGOUT DIALOG -->
<dialog id="logoutDialog" class="logout-dialog">
  <h2>Log Out?</h2>
  <p>Are you sure you want to log out of your account?</p>

  <div class="dialog-actions">
    <button id="cancelLogout">Cancel</button>
    <button id="confirmLogout">Log Out</button>
  </div>
</dialog>

<script src="users-enhancements.js" defer></script>

</body>
</html>

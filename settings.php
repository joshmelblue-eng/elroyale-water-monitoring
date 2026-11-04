
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

  <title>Account Settings | El Royale</title>

  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="settings.css">

  <script>
    window.currentUser = {
      name: <?= json_encode($displayName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      email: <?= json_encode($displayEmail, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      role: <?= json_encode($displayRole, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
      status: <?= json_encode($displayStatus, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
  </script>
  <script src="settings.js" defer></script>
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
      
      <?php if (($_SESSION["role"] ?? "") === "Administrator"): ?>
        <a href="/watermonitoring/users.php">
          <span class="nav-icon">♙</span>
          <span class="nav-label">User Management</span>
        </a>
      <?php endif; ?>

      <a href="/watermonitoring/settings.php" class="active">
        <span class="nav-icon">⚙</span>
        <span class="nav-label">Account Settings</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="avatar" id="sidebarAvatar"><?= escapeHtml($initials) ?></div>

      <div class="user-info">
        <strong id="sidebarName"><?= escapeHtml($displayName) ?></strong>
        <small id="sidebarRole"><?= escapeHtml($displayRole) ?> · Active</small>
      </div>

      <button id="logoutBtn" aria-label="Log out">↪</button>
    </div>

  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <header class="topbar">
      <div>
        <p class="breadcrumb">
          Dashboard / Account Settings
        </p>

        <h1>Account Settings</h1>

        <p class="subtitle">
          Manage your profile information and account security.
        </p>
      </div>

      <span class="demo-label"><?= escapeHtml($displayRole) ?></span>
    </header>

    <!-- PROFILE OVERVIEW -->
    <section class="card profile-overview">

      <div class="profile-avatar" id="profileAvatar">
        <?= escapeHtml($initials) ?>
      </div>

      <div class="profile-overview-info">
        <h2 id="profileName"><?= escapeHtml($displayName) ?></h2>
        <p id="profileEmail"><?= escapeHtml($displayEmail) ?></p>
        <span class="account-role" id="profileRole">
          <?= escapeHtml($displayRole) ?>
        </span>
      </div>

      <button class="primary-btn" id="editProfileBtn">
        Edit Profile
      </button>

    </section>

    <!-- SETTINGS GRID -->
    <section class="settings-grid">

      <!-- PERSONAL INFORMATION -->
      <div class="card settings-card">

        <div class="settings-card-header">
          <div>
            <h3>Personal Information</h3>
            <p>View your registered account details.</p>
          </div>
        </div>

        <div class="settings-detail">
          <span>Full Name</span>
          <strong id="displayName"><?= escapeHtml($displayName) ?></strong>
        </div>

        <div class="settings-detail">
          <span>Email Address</span>
          <strong id="displayEmail"><?= escapeHtml($displayEmail) ?></strong>
        </div>

        <div class="settings-detail">
          <span>Account Role</span>
          <strong id="displayRole"><?= escapeHtml($displayRole) ?></strong>
        </div>

        <div class="settings-detail">
          <span>Account Status</span>
          <span class="account-status" id="displayStatus">
            <?= escapeHtml($displayStatus) ?>
          </span>
        </div>

      </div>

      <!-- ACCOUNT SECURITY -->
      <div class="card settings-card">

        <div class="settings-card-header">
          <div>
            <h3>Account Security</h3>
            <p>Review your account access and security.</p>
          </div>
        </div>

        <div class="settings-detail">
          <span>Authentication</span>
          <strong>Session-based</strong>
        </div>

        <div class="settings-detail">
          <span>Password</span>
          <strong>••••••••</strong>
        </div>

        <div class="settings-detail">
          <span>Account Access</span>
          <strong id="securityRole"><?= escapeHtml($displayRole) ?></strong>
        </div>

        <div class="security-note">
          Your password and account permissions will be
          managed securely after backend integration.
        </div>

        <button class="outline-btn" id="changePasswordBtn">
          Change Password
        </button>

      </div>

    </section>

    <!-- SYSTEM INFORMATION -->
    <section class="card settings-info-card">

      <h3>Account Information</h3>

      <p>
        Account Settings allows authorized users to
        manage their personal profile and review their
        system access. Administrators and maintenance
        personnel can update their own profile information,
        while role assignment is managed by administrators.
      </p>

      <div class="info-note">
        This page reflects the active session for the
        logged-in user. Password and security updates
        will require backend integration when fully implemented.
      </div>

    </section>

  </main>
</div>

<!-- EDIT PROFILE DIALOG -->
<dialog id="editProfileDialog" class="settings-dialog">

  <h2>Edit Profile</h2>

  <p class="dialog-description">
    Update your personal account information.
  </p>

  <form id="editProfileForm">

    <label for="editFullName">Full Name</label>
    <input
      type="text"
      id="editFullName"
      placeholder="Enter full name"
      required
      maxlength="100"
    >

    <label for="editEmail">Email Address</label>
    <input
      type="email"
      id="editEmail"
      placeholder="Enter email address"
      required
    >

    <p id="profileError" class="form-error" role="alert"></p>

    <div class="dialog-actions">
      <button type="button" id="cancelEditProfile">
        Cancel
      </button>

      <button type="submit" class="primary-btn">
        Save Changes
      </button>
    </div>

  </form>

</dialog>

<!-- CHANGE PASSWORD DIALOG -->
<dialog id="passwordDialog" class="settings-dialog">

  <h2>Change Password</h2>

  <p class="dialog-description">
    Password changes require a secure backend.
    You can preview the form validation here.
  </p>

  <form id="passwordForm">

    <label for="currentPassword">Current Password</label>

    <div class="password-field">
      <input
        type="password"
        id="currentPassword"
        placeholder="Enter current password"
        autocomplete="current-password"
        required
      >
      <button type="button"
              class="toggle-password"
              data-target="currentPassword">
        Show
      </button>
    </div>

    <label for="newPassword">New Password</label>

    <div class="password-field">
      <input
        type="password"
        id="newPassword"
        placeholder="Enter new password"
        autocomplete="new-password"
        minlength="8"
        required
      >
      <button type="button"
              class="toggle-password"
              data-target="newPassword">
        Show
      </button>
    </div>

    <label for="confirmPassword">Confirm New Password</label>

    <div class="password-field">
      <input
        type="password"
        id="confirmPassword"
        placeholder="Confirm new password"
        autocomplete="new-password"
        required
      >
      <button type="button"
              class="toggle-password"
              data-target="confirmPassword">
        Show
      </button>
    </div>

    <p id="passwordError" class="form-error" role="alert"></p>

    <div class="dialog-actions">
      <button type="button" id="cancelPassword">
        Cancel
      </button>

      <button type="submit" class="primary-btn">
        Validate Password
      </button>
    </div>

  </form>

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

<!-- FEEDBACK MESSAGE -->
<div id="toast" class="settings-toast" role="status"
     aria-live="polite"></div>

</body>
</html>

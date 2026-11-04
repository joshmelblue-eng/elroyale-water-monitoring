
<?php
header("Cache-Control: no-store");
header("Referrer-Policy: no-referrer");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <meta name="referrer" content="no-referrer">

  <title>Reset Password | El Royale</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="signup-page">

  <main class="signup-card">

    <div class="signup-heading">
      <div class="signup-logo">🔑</div>

      <h1>Create New Password</h1>

      <p>
        Enter a new password with
        at least 12 characters.
      </p>
    </div>

    <form id="resetForm">

      <label for="password">
        New Password
      </label>

      <input
        type="password"
        id="password"
        minlength="12"
        autocomplete="new-password"
        required
      >

      <label for="confirmPassword">
        Confirm New Password
      </label>

      <input
        type="password"
        id="confirmPassword"
        minlength="12"
        autocomplete="new-password"
        required
      >

      <button
        type="submit"
        id="resetBtn"
        class="signup-button">
        Reset Password
      </button>

      <p id="message"
         role="status"
         style="margin-top:15px;font-size:13px;">
      </p>

    </form>

    <p class="back-login">
      <a href="index.html">Back to Login</a>
    </p>

  </main>

<script>
const params = new URLSearchParams(
  window.location.search
);

const token = params.get("token");

history.replaceState(
  null,
  "",
  window.location.pathname
);

const form = document.getElementById("resetForm");
const message = document.getElementById("message");
const button = document.getElementById("resetBtn");

if (!token || !/^[a-f0-9]{64}$/.test(token)) {
  message.textContent = "Invalid reset link.";
  button.disabled = true;
}

form.addEventListener("submit", async event => {
  event.preventDefault();

  if (!token || button.disabled) return;

  const password =
    document.getElementById("password").value;

  const confirm_password =
    document.getElementById("confirmPassword").value;

  if (password !== confirm_password) {
    message.textContent = "Passwords do not match.";
    return;
  }

  button.disabled = true;
  button.textContent = "Updating...";
  message.textContent = "";

  try {
    const response = await fetch(
      "API/reset_password.php",
      {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          token,
          password,
          confirm_password
        })
      }
    );

    const result = await response.json();

    message.textContent =
      result.message || "Unable to reset password.";

    if (response.ok && result.success) {
      form.reset();
      button.textContent = "Password Updated";
      return;
    }

  } catch (error) {
    message.textContent =
      "Cannot connect to the server.";
  }

  button.disabled = false;
  button.textContent = "Reset Password";
});
</script>

</body>
</html>

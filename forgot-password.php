
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>Forgot Password | El Royale</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="signup-page">

  <main class="signup-card">

    <div class="signup-heading">
      <div class="signup-logo">🔐</div>

      <h1>Forgot Password?</h1>

      <p>
        Enter your registered email address
        to request a password reset.
      </p>
    </div>

    <form id="forgotForm">

      <label for="email">Email Address</label>

      <input
        type="email"
        id="email"
        placeholder="Enter your registered email"
        autocomplete="email"
        required
      >

      <button
        type="submit"
        id="submitBtn"
        class="signup-button">
        Request Reset Link
      </button>

      <p id="message"
         role="status"
         style="margin-top:15px;font-size:13px;">
      </p>

    </form>

    <div class="approval-note">
      Local Development Mode:
      Password reset links are written to
      a private log on the XAMPP computer.
      No email is sent.
    </div>

    <p class="back-login">
      <a href="index.html">Back to Login</a>
    </p>

  </main>

<script>
document.getElementById("forgotForm")
  .addEventListener("submit", async event => {

    event.preventDefault();

    const button = document.getElementById("submitBtn");
    const message = document.getElementById("message");

    const email =
      document.getElementById("email").value.trim();

    button.disabled = true;
    button.textContent = "Processing...";
    message.textContent = "";

    try {
      const response = await fetch(
        "API/request_password_reset.php",
        {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ email })
        }
      );

      const result = await response.json();

      message.textContent =
        result.message || "Unable to process request.";

    } catch (error) {
      message.textContent =
        "Cannot connect to the server.";
    } finally {
      button.disabled = false;
      button.textContent = "Request Reset Link";
    }
  });
</script>

</body>
</html>

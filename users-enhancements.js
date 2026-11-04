
document.addEventListener("DOMContentLoaded", () => {

  const logoutBtn =
    document.getElementById("logoutBtn");

  const logoutDialog =
    document.getElementById("logoutDialog");

  const cancelLogout =
    document.getElementById("cancelLogout");

  const confirmLogout =
    document.getElementById("confirmLogout");

  if (
    !logoutBtn ||
    !logoutDialog ||
    !cancelLogout ||
    !confirmLogout
  ) {
    console.error("Logout elements not found.");
    return;
  }

  // Open confirmation dialog
  logoutBtn.addEventListener("click", () => {
    logoutDialog.showModal();
  });

  // Cancel logout
  cancelLogout.addEventListener("click", () => {
    logoutDialog.close();
  });

  // Confirm logout
  confirmLogout.addEventListener("click", async () => {

    confirmLogout.disabled = true;
    confirmLogout.textContent = "Logging out...";

    try {

      // Get CSRF token from PHP session
      const tokenResponse = await fetch(
        "/watermonitoring/API/csrf.php",
        {
          credentials: "same-origin",
          cache: "no-store"
        }
      );

      const tokenData = await tokenResponse.json();

      if (!tokenResponse.ok) {
        throw new Error(
          tokenData.message || "Unable to get security token."
        );
      }

      // End the PHP session
      const response = await fetch(
        "/watermonitoring/API/logout.php",
        {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "X-CSRF-Token": tokenData.csrf_token
          }
        }
      );

      const result = await response.json();

      if (!response.ok) {
        throw new Error(
          result.message || "Logout failed."
        );
      }

      // Redirect to Login
      window.location.replace("index.html");

    } catch (error) {

      console.error("Logout error:", error);
      alert(error.message);

      confirmLogout.disabled = false;
      confirmLogout.textContent = "Log Out";

    }

  });

});

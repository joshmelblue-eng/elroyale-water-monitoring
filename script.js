
const loginForm = document.getElementById("loginForm");
const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const formMessage = document.getElementById("formMessage");

togglePassword.addEventListener("click", function () {
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    togglePassword.textContent = "Hide";
  } else {
    passwordInput.type = "password";
    togglePassword.textContent = "Show";
  }
});

loginForm.addEventListener("submit", async function (event) {
  event.preventDefault();

  formMessage.textContent = "";

  if (!loginForm.checkValidity()) {
    loginForm.reportValidity();
    return;
  }

  const email = document.getElementById("email").value.trim();
  const password = passwordInput.value;
  const loginButton = loginForm.querySelector(
    'button[type="submit"]'
  );

  loginButton.disabled = true;
  loginButton.textContent = "Signing In...";

  try {
    const response = await fetch("API/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      credentials: "same-origin",
      body: JSON.stringify({ email, password })
    });

    const result = await response.json();

    if (!response.ok) {
      formMessage.textContent =
        result.message || "Unable to sign in.";
      return;
    }

    window.location.href = "/watermonitoring/dashboard.php";

  } catch (error) {
    console.error("Login error:", error);

    formMessage.textContent =
      "Cannot connect to the server.";

  } finally {
    loginButton.disabled = false;
    loginButton.textContent = "Sign In";
  }
});


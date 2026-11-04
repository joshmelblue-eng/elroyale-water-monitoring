
const signupForm = document.getElementById("signupForm");
const signupError = document.getElementById("signupError");

signupForm.addEventListener("submit", async function(event) {
  event.preventDefault();

  signupError.textContent = "";

  const firstName = document.getElementById("firstName").value.trim();
  const lastName = document.getElementById("lastName").value.trim();
  const personnelId = document.getElementById("personnelId").value.trim();
  const email = document.getElementById("signupEmail").value.trim();
  const password = document.getElementById("signupPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (!signupForm.checkValidity()) {
    signupForm.reportValidity();
    return;
  }

  if (password !== confirmPassword) {
    signupError.textContent = "Passwords do not match.";
    return;
  }

  const submitButton = signupForm.querySelector(
    'button[type="submit"]'
  );

  submitButton.disabled = true;
  submitButton.textContent = "Submitting...";

  try {
    const response = await fetch("API/signup.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        firstName,
        lastName,
        personnelId,
        email,
        password
      })
    });

    const result = await response.json();

    if (!response.ok) {
      signupError.textContent =
        result.message || "Unable to submit request.";
      return;
    }

    window.location.href = "/watermonitoring/request-submitted.html";

  } catch (error) {
    console.error("Signup error:", error);

    signupError.textContent =
      "Cannot connect to the server. Please try again.";

  } finally {
    submitButton.disabled = false;
    submitButton.textContent = "Submit Request";
  }
});

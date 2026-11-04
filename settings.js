
/* ========================================
   ACCOUNT SETTINGS - FRONTEND DEMO
======================================== */

const PROFILE_KEY = "elroyale_demo_profile_v1";

const currentUser = window.currentUser || {};

const defaultProfile = {
  name: currentUser.name || "User",
  email: currentUser.email || "",
  role: currentUser.role || "User",
  status: currentUser.status || "Active"
};

/* ========================================
   LOAD PROFILE
======================================== */

function loadProfile() {
  if (currentUser.name || currentUser.email || currentUser.role) {
    return {
      ...defaultProfile,
      name: currentUser.name || defaultProfile.name,
      email: currentUser.email || defaultProfile.email,
      role: currentUser.role || defaultProfile.role,
      status: currentUser.status || defaultProfile.status
    };
  }

  try {
    const saved = localStorage.getItem(PROFILE_KEY);

    if (saved) {
      const parsed = JSON.parse(saved);

      if (
        parsed &&
        typeof parsed.name === "string" &&
        typeof parsed.email === "string"
      ) {
        return {
          ...defaultProfile,
          name: parsed.name,
          email: parsed.email,
          role: parsed.role || defaultProfile.role,
          status: parsed.status || defaultProfile.status
        };
      }
    }
  } catch (error) {
    console.error("Could not load profile:", error);
  }

  return { ...defaultProfile };
}

let profile = loadProfile();

/* ========================================
   GET ELEMENTS
======================================== */

const editProfileDialog =
  document.getElementById("editProfileDialog");

const editProfileForm =
  document.getElementById("editProfileForm");

const passwordDialog =
  document.getElementById("passwordDialog");

const passwordForm =
  document.getElementById("passwordForm");

const logoutDialog =
  document.getElementById("logoutDialog");

const toast =
  document.getElementById("toast");

let toastTimeout;

/* ========================================
   GENERATE INITIALS
======================================== */

function getInitials(name) {
  const parts = name.trim().split(/\s+/).filter(Boolean);

  if (parts.length === 0) return "U";

  if (parts.length === 1) {
    return parts[0].charAt(0).toUpperCase();
  }

  return (
    parts[0].charAt(0) +
    parts[parts.length - 1].charAt(0)
  ).toUpperCase();
}

/* ========================================
   DISPLAY PROFILE
======================================== */

function displayProfile() {
  const initials = getInitials(profile.name);

  document.getElementById("sidebarAvatar").textContent =
    initials;

  document.getElementById("sidebarName").textContent =
    profile.name;

  document.getElementById("sidebarRole").textContent =
    profile.role + " · Active";

  document.getElementById("profileAvatar").textContent =
    initials;

  document.getElementById("profileName").textContent =
    profile.name;

  document.getElementById("profileEmail").textContent =
    profile.email;

  document.getElementById("profileRole").textContent =
    profile.role;

  document.getElementById("displayName").textContent =
    profile.name;

  document.getElementById("displayEmail").textContent =
    profile.email;

  document.getElementById("displayRole").textContent =
    profile.role;

  document.getElementById("securityRole").textContent =
    profile.role;

  const statusElement =
    document.getElementById("displayStatus");

  statusElement.textContent = profile.status;

  statusElement.classList.toggle(
    "inactive",
    profile.status === "Inactive"
  );
}

/* ========================================
   TOAST MESSAGE
======================================== */

function showToast(message) {
  clearTimeout(toastTimeout);

  toast.textContent = message;
  toast.classList.add("show");

  toastTimeout = setTimeout(function() {
    toast.classList.remove("show");
  }, 3000);
}

/* ========================================
   OPEN EDIT PROFILE
======================================== */

document.getElementById("editProfileBtn").addEventListener(
  "click",
  function() {
    document.getElementById("editFullName").value =
      profile.name;

    document.getElementById("editEmail").value =
      profile.email;

    document.getElementById("profileError").textContent = "";

    editProfileDialog.showModal();
  }
);

/* ========================================
   SAVE PROFILE
======================================== */

editProfileForm.addEventListener("submit", function(event) {
  event.preventDefault();

  const name = document.getElementById(
    "editFullName"
  ).value.trim();

  const email = document.getElementById(
    "editEmail"
  ).value.trim();

  const errorElement =
    document.getElementById("profileError");

  if (!name || !email) {
    errorElement.textContent =
      "Please complete all required fields.";
    return;
  }

  if (name.length < 2) {
    errorElement.textContent =
      "Full name must contain at least 2 characters.";
    return;
  }

  const updatedProfile = {
    ...profile,
    name,
    email
  };

  if (currentUser.name || currentUser.email || currentUser.role) {
    currentUser.name = name;
    currentUser.email = email;
    window.currentUser = currentUser;
  }

  try {
    localStorage.setItem(
      PROFILE_KEY,
      JSON.stringify(updatedProfile)
    );
  } catch (error) {
    errorElement.textContent =
      "Unable to save your profile in this browser.";
    return;
  }

  profile = updatedProfile;

  displayProfile();
  editProfileDialog.close();

  showToast("Profile changes saved successfully!");
});

/* ========================================
   CANCEL EDIT PROFILE
======================================== */

document.getElementById("cancelEditProfile").addEventListener(
  "click",
  function() {
    editProfileDialog.close();
  }
);

/* ========================================
   OPEN CHANGE PASSWORD
======================================== */

document.getElementById("changePasswordBtn").addEventListener(
  "click",
  function() {
    passwordForm.reset();

    document.getElementById("passwordError").textContent = "";

    document.querySelectorAll(".toggle-password").forEach(
      function(button) {
        const target = document.getElementById(
          button.dataset.target
        );

        target.type = "password";
        button.textContent = "Show";
      }
    );

    passwordDialog.showModal();
  }
);

/* ========================================
   SHOW / HIDE PASSWORD
======================================== */

document.querySelectorAll(".toggle-password").forEach(
  function(button) {
    button.addEventListener("click", function() {
      const input = document.getElementById(
        button.dataset.target
      );

      if (input.type === "password") {
        input.type = "text";
        button.textContent = "Hide";
      } else {
        input.type = "password";
        button.textContent = "Show";
      }
    });
  }
);

/* ========================================
   PASSWORD FORM VALIDATION
======================================== */

passwordForm.addEventListener("submit", function(event) {
  event.preventDefault();

  const currentPassword =
    document.getElementById("currentPassword").value;

  const newPassword =
    document.getElementById("newPassword").value;

  const confirmPassword =
    document.getElementById("confirmPassword").value;

  const errorElement =
    document.getElementById("passwordError");

  errorElement.textContent = "";

  if (!currentPassword || !newPassword || !confirmPassword) {
    errorElement.textContent =
      "Please complete all password fields.";
    return;
  }

  if (newPassword.length < 8) {
    errorElement.textContent =
      "New password must contain at least 8 characters.";
    return;
  }

  if (newPassword !== confirmPassword) {
    errorElement.textContent =
      "New passwords do not match.";
    return;
  }

  if (currentPassword === newPassword) {
    errorElement.textContent =
      "New password must be different from the current password.";
    return;
  }

  // Frontend cannot verify the current password.
  // No passwords are stored or updated here.
  // For admin accounts, this form is only a validation placeholder.

  passwordForm.reset();
  passwordDialog.close();

  showToast(
    "Validation passed. Actual password changes require backend integration."
  );
});

/* ========================================
   CANCEL PASSWORD CHANGE
======================================== */

document.getElementById("cancelPassword").addEventListener(
  "click",
  function() {
    passwordForm.reset();
    passwordDialog.close();
  }
);

/* ========================================
   LOGOUT CONFIRMATION
======================================== */

document.getElementById("logoutBtn").addEventListener(
  "click",
  function() {
    logoutDialog.showModal();
  }
);

document.getElementById("cancelLogout").addEventListener(
  "click",
  function() {
    logoutDialog.close();
  }
);

document.getElementById("confirmLogout").addEventListener(
  "click",
  function() {
    // Demo navigation only.
    // Backend must handle real session logout.

    window.location.href = "/watermonitoring/index.html";
  }
);

/* ========================================
   INITIALIZE
======================================== */

displayProfile();

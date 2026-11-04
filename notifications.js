
const notifications = [
  {
    id: 1,
    title: "Water Tank Reached Full Capacity",
    message: "The water tank reached 100%. Automatic shut-off was activated.",
    type: "critical",
    time: "Demo event · 10:45 AM",
    read: false
  },
  {
    id: 2,
    title: "High Water Level Detected",
    message: "Water level reached the configured warning threshold.",
    type: "warning",
    time: "Demo event · 10:40 AM",
    read: false
  },
  {
    id: 3,
    title: "ESP32 Connection Restored",
    message: "Communication with the monitoring controller was restored.",
    type: "info",
    time: "Demo event · 9:30 AM",
    read: true
  },
  {
    id: 4,
    title: "Water Level Returned to Normal",
    message: "The monitored water level is within the normal operating range.",
    type: "info",
    time: "Demo event · Yesterday",
    read: true
  }
];

let activeFilter = "all";

const notificationList = document.getElementById("notificationList");

function updateCounts() {
  document.getElementById("totalCount").textContent =
    notifications.length;

  document.getElementById("unreadCount").textContent =
    notifications.filter(n => !n.read).length;

  document.getElementById("criticalCount").textContent =
    notifications.filter(n => n.type === "critical").length;
}

function renderNotifications() {
  let filtered = notifications;

  if (activeFilter === "unread") {
    filtered = notifications.filter(n => !n.read);
  }

  if (activeFilter === "critical") {
    filtered = notifications.filter(n => n.type === "critical");
  }

  notificationList.replaceChildren();

  if (filtered.length === 0) {
    const empty = document.createElement("p");
    empty.className = "empty-state";
    empty.textContent = "No notifications found.";
    notificationList.appendChild(empty);
    updateCounts();
    return;
  }

  filtered.forEach(notification => {
    const item = document.createElement("article");
    item.className = "notification-item";
    if (!notification.read) item.classList.add("unread");

    const icon = document.createElement("div");
    icon.className = "notification-icon " + notification.type;
    icon.textContent =
      notification.type === "critical" ? "🚨" :
      notification.type === "warning" ? "⚠️" : "ℹ️";

    const content = document.createElement("div");
    content.className = "notification-content";

    const title = document.createElement("h4");
    title.textContent = notification.title;

    const message = document.createElement("p");
    message.textContent = notification.message;

    const time = document.createElement("span");
    time.className = "notification-time";
    time.textContent = notification.time;

    content.append(title, message, time);
    item.append(icon, content);

    if (!notification.read) {
      const button = document.createElement("button");
      button.className = "read-btn";
      button.textContent = "Mark as Read";

      button.addEventListener("click", () => {
        notification.read = true;
        renderNotifications();
      });

      item.appendChild(button);
    }

    notificationList.appendChild(item);
  });

  updateCounts();
}

document.querySelectorAll(".filter-btn").forEach(button => {
  button.addEventListener("click", () => {
    activeFilter = button.dataset.filter;

    document.querySelectorAll(".filter-btn").forEach(btn => {
      btn.classList.remove("active");
    });

    button.classList.add("active");
    renderNotifications();
  });
});

document.getElementById("markAllBtn").addEventListener("click", () => {
  notifications.forEach(notification => {
    notification.read = true;
  });
  renderNotifications();
});

const logoutDialog = document.getElementById("logoutDialog");

document.getElementById("logoutBtn").addEventListener("click", () => {
  logoutDialog.showModal();
});

document.getElementById("cancelLogout").addEventListener("click", () => {
  logoutDialog.close();
});

document.getElementById("confirmLogout").addEventListener("click", () => {
  window.location.href = "/watermonitoring/index.html";
});

renderNotifications();

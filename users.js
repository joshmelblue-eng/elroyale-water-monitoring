
document.addEventListener("DOMContentLoaded", () => {
  const $ = id => document.getElementById(id);

  let users = [];
  let requests = [];
  let csrfToken = "";

  const usersBody = $("usersTableBody");
  const requestsBody = $("requestsTableBody");

  function getElement(...ids) {
    for (const id of ids) {
      const element = $(id);
      if (element) return element;
    }
    return null;
  }

  function setText(ids, value) {
    const element = getElement(...ids);
    if (element) element.textContent = value;
  }

  function makeCell(value, className = "") {
    const cell = document.createElement("td");
    cell.textContent = value ?? "—";
    if (className) cell.className = className;
    return cell;
  }

  function emptyTable(body, columns, message) {
    const row = document.createElement("tr");
    const cell = makeCell(message);
    cell.colSpan = columns;
    row.appendChild(cell);
    body.appendChild(row);
  }

  async function api(file, options = {}) {
    const response = await fetch("/watermonitoring/API/" + file, {
      credentials: "same-origin",
      cache: "no-store",
      ...options
    });

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || "API request failed.");
    }

    return result;
  }

  function renderUsers() {
    if (!usersBody) return;

    usersBody.replaceChildren();

    const search = (
      getElement("searchUser", "searchInput", "userSearch")?.value || ""
    ).toLowerCase().trim();

    const role = (
      getElement("roleFilter", "filterRole")?.value || "all"
    );

    const status = (
      getElement("statusFilter", "filterStatus")?.value || "all"
    );

    const filtered = users.filter(user => {
      const matchesSearch =
        `${user.full_name} ${user.email} ${user.personnel_id || ""}`
          .toLowerCase()
          .includes(search);

      const matchesRole =
        role === "all" ||
        role === "" ||
        role === user.role;

      const matchesStatus =
        status === "all" ||
        status === "" ||
        status === user.status;

      return matchesSearch && matchesRole && matchesStatus;
    });

    filtered.forEach(user => {
      const row = document.createElement("tr");

      // Six columns matching the existing HTML
      row.appendChild(
        makeCell(user.personnel_id || user.user_id)
      );

      row.appendChild(makeCell(user.full_name));
      row.appendChild(makeCell(user.email));

      const roleCell = makeCell(user.role);
      roleCell.className = "role-cell";
      row.appendChild(roleCell);

      const statusCell = makeCell(user.status);
      statusCell.className = "status-cell";
      row.appendChild(statusCell);

      // Editing will be connected to PHP later
      row.appendChild(makeCell("—"));

      usersBody.appendChild(row);
    });

    if (filtered.length === 0) {
      emptyTable(usersBody, 6, "No users found.");
    }

    setText(
      ["userCount", "usersCount"],
      `Showing ${filtered.length} users`
    );
  }

  function renderRequests() {
    if (!requestsBody) return;

    requestsBody.replaceChildren();

    requests.forEach(request => {
      const row = document.createElement("tr");

      // Five columns matching the existing HTML
      row.appendChild(makeCell(request.request_id));
      row.appendChild(makeCell(request.full_name));
      row.appendChild(makeCell(request.email));

      row.appendChild(
        makeCell(request.requested_at)
      );

      const actionsCell = document.createElement("td");

      const approveBtn = document.createElement("button");
      approveBtn.type = "button";
      approveBtn.textContent = "Approve";
      approveBtn.className = "action-btn approve-btn";

      approveBtn.addEventListener("click", () => {
        if (confirm(`Approve ${request.full_name}?`)) {
          processRequest(
            "approve_user.php",
            request.request_id
          );
        }
      });

      const rejectBtn = document.createElement("button");
      rejectBtn.type = "button";
      rejectBtn.textContent = "Reject";
      rejectBtn.className = "action-btn reject-btn";

      rejectBtn.addEventListener("click", () => {
        if (confirm(`Reject ${request.full_name}?`)) {
          processRequest(
            "reject_user.php",
            request.request_id
          );
        }
      });

      actionsCell.appendChild(approveBtn);
      actionsCell.appendChild(
        document.createTextNode(" ")
      );
      actionsCell.appendChild(rejectBtn);

      row.appendChild(actionsCell);
      requestsBody.appendChild(row);
    });

    if (requests.length === 0) {
      emptyTable(
        requestsBody,
        5,
        "No pending account requests."
      );
    }

    setText(
       ["pendingCountLabel"],
      `${requests.length} Pending`
    );
  }

  function renderAll() {
    setText(["totalUsers"], users.length);

    setText(
      ["activeUsers"],
      users.filter(u => u.status === "Active").length
    );

    setText(
      ["inactiveUsers"],
      users.filter(u => u.status === "Inactive").length
    );

    setText(["pendingRequests"], requests.length);

    renderUsers();
    renderRequests();
  }

  async function loadData() {
    try {
      const [usersResult, requestsResult] =
        await Promise.all([
          api("get_users.php"),
          api("admin_requests.php")
        ]);

      users = usersResult.users || [];
      requests = requestsResult.requests || [];

      console.log("Loaded users:", users);
      console.log("Loaded requests:", requests);

      renderAll();

    } catch (error) {
      console.error("Data loading error:", error);
      alert(error.message);
    }
  }

  async function processRequest(file, requestId) {
    try {
      const result = await api(file, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": csrfToken
        },
        body: JSON.stringify({
          request_id: Number(requestId)
        })
      });

      alert(result.message);
      await loadData();

    } catch (error) {
      alert(error.message);
    }
  }

  async function initialize() {
    if (!usersBody || !requestsBody) {
      console.error(
        "Missing table IDs:",
        { usersBody, requestsBody }
      );
      return;
    }

    try {
      const tokenResult = await api("csrf.php");
      csrfToken = tokenResult.csrf_token;

      await loadData();

    } catch (error) {
      console.error("Initialization error:", error);
      alert(error.message);
    }
  }

  for (const id of ["searchInput", "userSearch"]) {
    $(id)?.addEventListener("input", renderUsers);
  }

  for (const id of [
    "roleFilter",
    "filterRole",
    "statusFilter",
    "filterStatus"
  ]) {
    $(id)?.addEventListener("change", renderUsers);
  }

  getElement("clearFilters")?.addEventListener(
    "click",
    () => {
      const search = getElement("searchInput", "userSearch");
      const role = getElement("roleFilter", "filterRole");
      const status = getElement("statusFilter", "filterStatus");

      if (search) search.value = "";
      if (role) role.selectedIndex = 0;
      if (status) status.selectedIndex = 0;

      renderUsers();
    }
  );

  const addUserBtn = $("addUserBtn");

  if (addUserBtn) {
    addUserBtn.disabled = true;
    addUserBtn.title =
      "Add User will be connected to MySQL later.";
  }

  initialize();
});

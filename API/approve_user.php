
<?php
require_once __DIR__ . "/admin_guard.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    apiRespond(405, "Method not allowed.");
}

checkPostOrigin();
checkCsrf();

$data = json_decode(file_get_contents("php://input"), true);

$id = filter_var(
    $data["request_id"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    apiRespond(400, "Invalid request ID.");
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT * FROM account_requests
         WHERE request_id = ? FOR UPDATE"
    );
    $stmt->execute([$id]);
    $request = $stmt->fetch();

    if (!$request || $request["status"] !== "Pending") {
        $pdo->rollBack();
        apiRespond(404, "Pending request not found.");
    }

    $stmt = $pdo->prepare(
        "SELECT user_id FROM users
         WHERE email = ? OR personnel_id = ?
         LIMIT 1"
    );
    $stmt->execute([
        $request["email"],
        $request["personnel_id"]
    ]);

    if ($stmt->fetch()) {
        $pdo->rollBack();
        apiRespond(409, "Account already exists.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO users
         (personnel_id, full_name, email,
          password_hash, role, status)
         VALUES (?, ?, ?, ?, 'Maintenance Personnel', 'Active')"
    );
    $stmt->execute([
        $request["personnel_id"],
        $request["full_name"],
        $request["email"],
        $request["password_hash"]
    ]);

    $stmt = $pdo->prepare(
        "UPDATE account_requests
         SET status = 'Approved',
             reviewed_by = ?,
             reviewed_at = NOW()
         WHERE request_id = ?"
    );
    $stmt->execute([$_SESSION["user_id"], $id]);

    $pdo->commit();

    apiRespond(200, "Account approved successfully.");
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    apiRespond(500, "Unable to approve account.");
}

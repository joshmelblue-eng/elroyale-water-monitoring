
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
    $stmt = $pdo->prepare(
        "UPDATE account_requests
         SET status = 'Rejected',
             reviewed_by = ?,
             reviewed_at = NOW()
         WHERE request_id = ?
           AND status = 'Pending'"
    );

    $stmt->execute([$_SESSION["user_id"], $id]);

    if ($stmt->rowCount() === 0) {
        apiRespond(404, "Pending request not found.");
    }

    apiRespond(200, "Request rejected successfully.");
} catch (PDOException $e) {
    error_log($e->getMessage());
    apiRespond(500, "Unable to reject request.");
}

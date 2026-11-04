
<?php
require_once __DIR__ . "/admin_guard.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    apiRespond(405, "Method not allowed.");
}

try {
    $stmt = $pdo->query(
        "SELECT request_id, personnel_id,
                full_name, email, requested_at
         FROM account_requests
         WHERE status = 'Pending'
         ORDER BY requested_at DESC"
    );

    apiRespond(200, "Requests loaded.", [
        "requests" => $stmt->fetchAll()
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    apiRespond(500, "Unable to load requests.");
}

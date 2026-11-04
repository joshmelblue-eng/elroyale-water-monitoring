
<?php
require_once __DIR__ . "/admin_guard.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    apiRespond(405, "Method not allowed.");
}

try {
    $stmt = $pdo->query(
        "SELECT user_id, personnel_id, full_name,
                email, role, status
         FROM users
         ORDER BY user_id DESC"
    );

    apiRespond(200, "Users loaded.", [
        "users" => $stmt->fetchAll()
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    apiRespond(500, "Unable to load users.");
}

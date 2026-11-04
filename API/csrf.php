
<?php
require_once __DIR__ . "/admin_guard.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    apiRespond(405, "Method not allowed.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

apiRespond(200, "Security token ready.", [
    "csrf_token" => $_SESSION["csrf_token"]
]);

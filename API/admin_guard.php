
<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function apiRespond($code, $message, $extra = []) {
    http_response_code($code);
    echo json_encode(array_merge([
        "status" => $code < 400 ? "success" : "error",
        "message" => $message
    ], $extra));
    exit;
}

function adminOnly() {
    if (empty($_SESSION["user_id"])) {
        apiRespond(401, "Please sign in first.");
    }

    $stmt = $GLOBALS["pdo"]->prepare(
        "SELECT user_id, role, status
         FROM users WHERE user_id = ? LIMIT 1"
    );
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch();

    if (!$user ||
        $user["role"] !== "Administrator" ||
        $user["status"] !== "Active") {
        apiRespond(403, "Administrator access required.");
    }
}

function checkPostOrigin() {
    $origin = $_SERVER["HTTP_ORIGIN"] ?? "";
    $host = $_SERVER["HTTP_HOST"] ?? "";

    $scheme = (!empty($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off") ? "https" : "http";

    if ($origin !== "" &&
        $origin !== "$scheme://$host") {
        apiRespond(403, "Invalid request origin.");
    }
}

function checkCsrf() {
    $provided = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";
    $expected = $_SESSION["csrf_token"] ?? "";

    if (!$expected || !hash_equals($expected, $provided)) {
        apiRespond(403, "Invalid security token.");
    }
}

adminOnly();

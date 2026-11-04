
<?php
require_once __DIR__ . "/auth.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed."
    ]);
    exit;
}

requireLogin();

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

if ($origin !== "") {
    $expectedOrigin =
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
            ? "https://"
            : "http://")
        . $_SERVER["HTTP_HOST"];

    if ($origin !== $expectedOrigin) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Invalid request origin."
        ]);
        exit;
    }
}

$token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Invalid security token."
    ]);
    exit;
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 3600,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => $params["samesite"] ?? "Lax"
    ]);
}

session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully."
]);

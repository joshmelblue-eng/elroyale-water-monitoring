
<?php
ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");
ini_set("session.cookie_httponly", "1");
ini_set("session.cookie_samesite", "Lax");

if (!empty($_SERVER["HTTPS"]) &&
    $_SERVER["HTTPS"] !== "off") {
    ini_set("session.cookie_secure", "1");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (empty($_SESSION["user_id"])) {
        http_response_code(401);
        exit("Login required.");
    }
}

function requireAdmin() {
    requireLogin();

    if (($_SESSION["role"] ?? "") !== "Administrator") {
        http_response_code(403);
        exit("Administrator access required.");
    }
}

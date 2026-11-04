
<?php
header("Content-Type: application/json; charset=utf-8");

ini_set("session.use_strict_mode", "1");
ini_set("session.use_only_cookies", "1");
ini_set("session.cookie_httponly", "1");
ini_set("session.cookie_samesite", "Lax");

if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
    ini_set("session.cookie_secure", "1");
}

session_start();

require_once __DIR__ . "/db.php";

function respond($code, $message, $user = null) {
    http_response_code($code);

    $response = ["message" => $message];

    if ($user !== null) {
        $response["user"] = $user;
    }

    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed.");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    respond(400, "Invalid request.");
}

$email = strtolower(trim($data["email"] ?? ""));
$password = $data["password"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !is_string($password) || $password === "") {
    respond(400, "Enter a valid email and password.");
}

try {
    $stmt = $pdo->prepare(
        "SELECT user_id, full_name, email,
                password_hash, role, status
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (
        !$user ||
        !password_verify($password, $user["password_hash"]) ||
        $user["status"] !== "Active"
    ) {
        respond(401, "Invalid email or password.");
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int) $user["user_id"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["name"] = $user["full_name"];

    respond(200, "Login successful.", [
        "id" => (int) $user["user_id"],
        "name" => $user["full_name"],
        "email" => $user["email"],
        "role" => $user["role"]
    ]);

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    respond(500, "Unable to sign in.");
}

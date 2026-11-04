
<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/db.php";

function respond($status, $message, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "status" => $status,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond("error", "Method not allowed.", 405);
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    respond("error", "Invalid request data.", 400);
}

$firstName = trim($data["firstName"] ?? "");
$lastName = trim($data["lastName"] ?? "");
$personnelId = trim($data["personnelId"] ?? "");
$email = strtolower(trim($data["email"] ?? ""));
$password = $data["password"] ?? "";

if (
    $firstName === "" ||
    $lastName === "" ||
    $personnelId === "" ||
    $email === "" ||
    $password === ""
) {
    respond("error", "All fields are required.", 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond("error", "Invalid email address.", 400);
}

if (strlen($password) < 8) {
    respond("error", "Password must be at least 8 characters.", 400);
}

if (
    strlen($firstName) > 75 ||
    strlen($lastName) > 75 ||
    strlen($personnelId) > 50 ||
    strlen($email) > 150
) {
    respond("error", "One or more fields are too long.", 400);
}

try {
    // Check existing registered users
    $stmt = $pdo->prepare(
        "SELECT user_id FROM users
         WHERE email = ? OR personnel_id = ?
         LIMIT 1"
    );
    $stmt->execute([$email, $personnelId]);

    if ($stmt->fetch()) {
        respond(
            "error",
            "An account or request already exists with these details.",
            409
        );
    }

    // Check existing pending requests
    $stmt = $pdo->prepare(
        "SELECT request_id FROM account_requests
         WHERE (email = ? OR personnel_id = ?)
         AND status = 'Pending'
         LIMIT 1"
    );
    $stmt->execute([$email, $personnelId]);

    if ($stmt->fetch()) {
        respond(
            "error",
            "An account or request already exists with these details.",
            409
        );
    }

    // Never store plaintext passwords
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $fullName = $firstName . " " . $lastName;

    $stmt = $pdo->prepare(
        "INSERT INTO account_requests
        (personnel_id, full_name, email, password_hash, status)
        VALUES (?, ?, ?, ?, 'Pending')"
    );

    $stmt->execute([
        $personnelId,
        $fullName,
        $email,
        $passwordHash
    ]);

    respond(
        "success",
        "Account request submitted successfully.",
        201
    );

} catch (PDOException $e) {
    error_log("Signup error: " . $e->getMessage());

    respond(
        "error",
        "Unable to process your request.",
        500
    );
}

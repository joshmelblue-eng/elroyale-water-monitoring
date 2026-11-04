
<?php
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function respond($status, $message, $success = false) {
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed.");
}

$remote = $_SERVER["REMOTE_ADDR"] ?? "";

if (!in_array($remote, ["127.0.0.1", "::1"], true)) {
    respond(403, "Localhost testing only.");
}

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
$host = $_SERVER["HTTP_HOST"] ?? "";

if (
    $origin !== "" &&
    $origin !== "http://" . $host
) {
    respond(403, "Invalid request origin.");
}

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$token = $data["token"] ?? "";
$password = $data["password"] ?? "";
$confirm = $data["confirm_password"] ?? "";

if (
    !is_string($token) ||
    !preg_match('/^[a-f0-9]{64}$/', $token)
) {
    respond(400, "Invalid reset link.");
}

if (
    !is_string($password) ||
    strlen($password) < 12 ||
    strlen($password) > 1024
) {
    respond(
        422,
        "Password must be at least 12 characters."
    );
}

if ($password !== $confirm) {
    respond(422, "Passwords do not match.");
}

try {
    $pdo->beginTransaction();

    $tokenHash = hash("sha256", $token);

    $stmt = $pdo->prepare(
        "SELECT pr.reset_id, pr.user_id
         FROM password_resets pr
         JOIN users u ON u.user_id = pr.user_id
         WHERE pr.token_hash = ?
           AND pr.used_at IS NULL
           AND pr.expires_at > NOW()
           AND u.status = 'Active'
         LIMIT 1
         FOR UPDATE"
    );

    $stmt->execute([$tokenHash]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $pdo->rollBack();
        respond(400, "Reset link is invalid or expired.");
    }

    $newHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $update = $pdo->prepare(
        "UPDATE users
         SET password_hash = ?
         WHERE user_id = ?"
    );

    $update->execute([
        $newHash,
        $reset["user_id"]
    ]);

    // Invalidate every outstanding reset link
    // for this user after a successful reset.
    $invalidate = $pdo->prepare(
        "UPDATE password_resets
         SET used_at = NOW()
         WHERE user_id = ?
           AND used_at IS NULL"
    );

    $invalidate->execute([$reset["user_id"]]);

    $pdo->commit();

    respond(
        200,
        "Password successfully updated. You can now log in.",
        true
    );

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Password reset error: " . $e->getMessage());

    respond(500, "Unable to reset password.");
}


<?php
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

function respond($status, $message) {
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(405, "Method not allowed.");
}

// Localhost-only development endpoint.
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

session_start();

// Basic per-session rate limit.
$now = time();
$attempts = $_SESSION["reset_attempts"] ?? [];

$attempts = array_values(array_filter(
    $attempts,
    fn($time) => $time > $now - 3600
));

if (count($attempts) >= 5) {
    respond(429, "Too many requests. Try again later.");
}

$attempts[] = $now;
$_SESSION["reset_attempts"] = $attempts;

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = strtolower(trim($data["email"] ?? ""));

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 254
) {
    respond(422, "Enter a valid email address.");
}

$genericMessage =
    "If this email belongs to an active account, " .
    "a reset link has been prepared for local testing.";

try {
    $stmt = $pdo->prepare(
        "SELECT user_id
         FROM users
         WHERE email = ?
           AND status = 'Active'
         LIMIT 1"
    );

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $recent = $pdo->prepare(
            "SELECT COUNT(*)
             FROM password_resets
             WHERE user_id = ?
               AND created_at > DATE_SUB(
                   NOW(), INTERVAL 1 HOUR
               )"
        );

        $recent->execute([$user["user_id"]]);

        if ((int) $recent->fetchColumn() < 3) {

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash("sha256", $token);

            $insert = $pdo->prepare(
                "INSERT INTO password_resets
                 (user_id, token_hash, expires_at)
                 VALUES (
                     ?, ?,
                     DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                 )"
            );

            $insert->execute([
                $user["user_id"],
                $tokenHash
            ]);

            $link =
                "http://localhost/watermonitoring/" .
                "reset-password.php?token=" .
                urlencode($token);

            $logFile =
                "C:/xampp/private/watermonitoring-reset-links.log";

            $entry =
                "[" . date("Y-m-d H:i:s") . "] " .
                "LOCAL TEST RESET LINK: " .
                $link . PHP_EOL;

            // Restrict the log to the local machine.
            if (file_put_contents(
                $logFile,
                $entry,
                FILE_APPEND | LOCK_EX
            ) === false) {
                throw new RuntimeException(
                    "Unable to write development log."
                );
            }
        }
    }

} catch (Throwable $e) {
    error_log("Reset request error: " . $e->getMessage());
}

respond(200, $genericMessage);

<?php
/**
 * Practical 10: Authentication Guard & Session Security
 * StudentHub Authentication Middleware
 */

// 1. Session Cookie Security Configuration (Compatible with local XAMPP & production)
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_set_cookie_params([
        'lifetime' => 0,          // Cookie expires when browser closes
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,  // False on local HTTP, true if HTTPS
        'httponly' => true,      // Prevent JavaScript access to session cookie
        'samesite' => 'Lax'       // CSRF protection while permitting navigation
    ]);

    session_start();
}

$timeout = 600; // 10 minutes inactivity timeout (600 seconds)

// 2. Remember-Me Token Validation (Automatic authentication if session expired but remember-me valid)
if (!isset($_SESSION["user_id"]) && !empty($_COOKIE["remember_me"])) {
    require_once __DIR__ . "/db.php";

    $tokenParts = explode(":", $_COOKIE["remember_me"], 2);
    if (count($tokenParts) === 2) {
        $cookieUserId = (int)$tokenParts[0];
        $cookieToken  = $tokenParts[1];

        $tokenStmt = $conn->prepare(
            "SELECT id, name, email, role, remember_token, remember_expires 
             FROM students 
             WHERE id = ? AND remember_expires > NOW() 
             LIMIT 1"
        );

        if ($tokenStmt) {
            $tokenStmt->bind_param("i", $cookieUserId);
            $tokenStmt->execute();
            $tokenResult = $tokenStmt->get_result();

            if ($tokenRow = $tokenResult->fetch_assoc()) {
                $candidateHash = hash("sha256", $cookieToken);
                if (!empty($tokenRow["remember_token"]) && hash_equals($tokenRow["remember_token"], $candidateHash)) {
                    // Valid Remember-Me token: regenerate session ID and authenticate
                    session_regenerate_id(true);

                    $_SESSION["user_id"]       = (int)$tokenRow["id"];
                    $_SESSION["name"]          = $tokenRow["name"];
                    $_SESSION["email"]         = $tokenRow["email"];
                    $_SESSION["role"]          = $tokenRow["role"];
                    $_SESSION["last_activity"] = time();

                    // Rotate token for enhanced security
                    $newRawToken    = bin2hex(random_bytes(32));
                    $newHashedToken = hash("sha256", $newRawToken);

                    $rotStmt = $conn->prepare(
                        "UPDATE students 
                         SET remember_token = ?, remember_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) 
                         WHERE id = ?"
                    );
                    if ($rotStmt) {
                        $rotStmt->bind_param("si", $newHashedToken, $cookieUserId);
                        $rotStmt->execute();
                        $rotStmt->close();
                    }

                    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                    setcookie(
                        "remember_me",
                        $cookieUserId . ":" . $newRawToken,
                        time() + (30 * 86400),
                        "/",
                        "",
                        $isSecure,
                        true
                    );
                }
            }
            $tokenStmt->close();
        }
    }
}

// 3. Verify Authentication: Redirect unauthenticated visitors to login.php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// 4. Inactivity Timeout Check (10 minutes)
if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"] > $timeout)) {
    // Clear session array
    $_SESSION = [];

    // Expire session cookie
    if (ini_get("session.use_cookies")) {
        $cookieParams = session_get_cookie_params();
        setcookie(
            session_name(),
            "",
            time() - 42000,
            $cookieParams["path"],
            $cookieParams["domain"],
            $cookieParams["secure"],
            $cookieParams["httponly"]
        );
    }

    // Expire Remember-Me cookie if present
    if (isset($_COOKIE["remember_me"])) {
        setcookie("remember_me", "", time() - 42000, "/", "", false, true);
    }

    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

// 5. Update last activity timestamp on every valid protected request
$_SESSION["last_activity"] = time();
?>
<?php
/**
 * Practical 10: Secure Logout Handler
 * Clears active session, invalidates remember-me tokens, expires cookies, and redirects to login.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Invalidate remember-me token in database if user is logged in
if (isset($_SESSION["user_id"])) {
    require_once __DIR__ . "/db.php";
    $revokeStmt = $conn->prepare(
        "UPDATE students 
         SET remember_token = NULL, remember_expires = NULL 
         WHERE id = ?"
    );
    if ($revokeStmt) {
        $revokeStmt->bind_param("i", $_SESSION["user_id"]);
        $revokeStmt->execute();
        $revokeStmt->close();
    }
}

// 2. Unset all session variables
$_SESSION = [];

// 3. Expire session cookie
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

// 4. Expire remember_me cookie
if (isset($_COOKIE["remember_me"])) {
    setcookie("remember_me", "", time() - 42000, "/", "", false, true);
}

// 5. Destroy the server-side session
session_destroy();

// 6. Redirect to login with logout confirmation flag
header("Location: login.php?logout=1");
exit;
?>
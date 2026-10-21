<?php
/**
 * Practical 10: Secure User Login
 * StudentHub Authentication Portal
 */

// 1. Session Cookie Security Configuration
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

require_once __DIR__ . "/db.php";

// 2. Check if already logged in via active session
if (isset($_SESSION["user_id"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] === "admin") {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit;
}

// 3. Check for valid Remember-Me cookie for auto-login
if (empty($_SESSION["user_id"]) && !empty($_COOKIE["remember_me"])) {
    $parts = explode(":", $_COOKIE["remember_me"], 2);
    if (count($parts) === 2) {
        $cUserId = (int)$parts[0];
        $cToken  = $parts[1];

        $stmt = $conn->prepare(
            "SELECT id, name, email, role, remember_token, remember_expires 
             FROM students 
             WHERE id = ? AND remember_expires > NOW() 
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param("i", $cUserId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($user = $res->fetch_assoc()) {
                if (!empty($user["remember_token"]) && hash_equals($user["remember_token"], hash("sha256", $cToken))) {
                    session_regenerate_id(true);
                    $_SESSION["user_id"]       = (int)$user["id"];
                    $_SESSION["name"]          = $user["name"];
                    $_SESSION["email"]         = $user["email"];
                    $_SESSION["role"]          = $user["role"];
                    $_SESSION["last_activity"] = time();

                    // Update last login
                    $uStmt = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = ?");
                    if ($uStmt) {
                        $uStmt->bind_param("i", $user["id"]);
                        $uStmt->execute();
                        $uStmt->close();
                    }

                    $target = ($user["role"] === "admin") ? "admin_dashboard.php" : "student_dashboard.php";
                    header("Location: " . $target);
                    exit;
                }
            }
            $stmt->close();
        }
    }
}

$error = "";
$successMessage = "";

// Check query alerts
if (isset($_GET["logout"])) {
    $successMessage = "You have been logged out successfully.";
}

if (isset($_GET["timeout"])) {
    $error = "Your session has expired due to 10 minutes of inactivity. Please log in again.";
}

// 4. Handle Login POST Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $remember = !empty($_POST["remember"]);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role 
             FROM students 
             WHERE email = ? 
             LIMIT 1"
        );

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();

            if ($user && !empty($user["password"]) && password_verify($password, $user["password"])) {
                // Prevent Session Fixation
                session_regenerate_id(true);

                // Populate Session Variables
                $_SESSION["user_id"]       = (int)$user["id"];
                $_SESSION["name"]          = $user["name"];
                $_SESSION["email"]         = $user["email"];
                $_SESSION["role"]          = $user["role"];
                $_SESSION["last_activity"] = time();

                // Update Last-Login Timestamp
                $update = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = ?");
                if ($update) {
                    $update->bind_param("i", $user["id"]);
                    $update->execute();
                    $update->close();
                }

                // Handle Remember-Me Token
                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
                if ($remember) {
                    $rawToken    = bin2hex(random_bytes(32));
                    $hashedToken = hash("sha256", $rawToken);

                    $remStmt = $conn->prepare(
                        "UPDATE students 
                         SET remember_token = ?, remember_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) 
                         WHERE id = ?"
                    );
                    if ($remStmt) {
                        $remStmt->bind_param("si", $hashedToken, $user["id"]);
                        $remStmt->execute();
                        $remStmt->close();
                    }

                    setcookie(
                        "remember_me",
                        $user["id"] . ":" . $rawToken,
                        time() + (30 * 86400),
                        "/",
                        "",
                        $isSecure,
                        true
                    );
                } else {
                    // If remember me wasn't checked, remove any existing remember token
                    $remClear = $conn->prepare("UPDATE students SET remember_token = NULL, remember_expires = NULL WHERE id = ?");
                    if ($remClear) {
                        $remClear->bind_param("i", $user["id"]);
                        $remClear->execute();
                        $remClear->close();
                    }
                    if (isset($_COOKIE["remember_me"])) {
                        setcookie("remember_me", "", time() - 42000, "/", "", false, true);
                    }
                }

                $conn->close();

                // Role-Based Redirection
                if ($user["role"] === "admin") {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Database service error. Please try again later.";
        }
    }
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Secure Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-box {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .demo-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
        }
        .demo-box h4 {
            margin: 0 0 10px 0;
            color: #334155;
            font-size: 14px;
        }
        .quick-btn {
            background-color: #475569;
            color: white;
            font-size: 12px;
            padding: 6px 12px;
            margin: 2px 4px 2px 0;
            border-radius: 4px;
            cursor: pointer;
        }
        .quick-btn:hover {
            background-color: #1e293b;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            margin: 10px 0 15px 0;
        }
        .checkbox-container input {
            margin-right: 8px;
            cursor: pointer;
        }
        .checkbox-container label {
            cursor: pointer;
            user-select: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    <h1>StudentHub Portal</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="register.html">Register</a>
        <a href="login.php" style="background-color: #2563eb;">Login</a>
        <a href="student_dashboard.php">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="notes.html">Notes</a>
        <a href="assignments.html">Assignments</a>
        <a href="attendance.html">Attendance</a>
        <a href="timetable.html">Timetable</a>
        <a href="profile.html">Profile</a>
        <a href="feedback.html">Feedback</a>
        <a href="about.html">About</a>
    </nav>
</header>

<main>
    <section>
        <h2>Secure User Login</h2>
        <p>Access your personalized student portal or administrator management dashboard.</p>

        <?php if (!empty($successMessage)): ?>
            <div class="alert-box alert-success">
                ✅ <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">
            <label for="email"><strong>Email Address</strong></label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="e.g. student@studenthub.com"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                required
                autocomplete="email"
            >

            <label for="password"><strong>Password</strong></label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >

            <div class="checkbox-container">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me on this computer (30 days)</label>
            </div>

            <button type="submit" id="submitBtn">Sign In</button>
            <button type="reset">Reset</button>
        </form>

        <p style="margin-top: 15px;">
            Don't have an account? <a href="register.html">Register here</a>
        </p>

        <!-- Quick Access Test Buttons for Practical Evaluation -->
        <div class="demo-box">
            <h4>⚡ Quick Test Credentials (1-Click Fill)</h4>
            <button type="button" class="quick-btn" onclick="fillCredentials('kaushalsanavada2007@gmail.com', 'Student@123')">
                👤 Fill Student Account
            </button>
            <button type="button" class="quick-btn" onclick="fillCredentials('kaushalsanavada2008@gmail.com', 'Admin@123')">
                🛡️ Fill Admin Account
            </button>
            <div style="font-size: 12px; color: #64748b; margin-top: 8px;">
                <strong>Student:</strong> kaushalsanavada2007@gmail.com / Student@123<br>
                <strong>Admin:</strong> kaushalsanavada2008@gmail.com / Admin@123
            </div>
        </div>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026 - Practical 10: Secure User Authentication</p>
</footer>

<script>
function fillCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
<script src="script.js"></script>

</body>
</html>
<?php
/**
 * Practical 10: Protected Student Dashboard
 * Accessible only to authenticated users (protected by auth.php)
 */

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

$userId = (int)$_SESSION["user_id"];
$student = null;

$stmt = $conn->prepare(
    "SELECT id, name, email, mobile, course, year, gender, created_at, last_login, role 
     FROM students 
     WHERE id = ? 
     LIMIT 1"
);

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-student {
            background-color: #2563eb;
            color: white;
        }
        .badge-admin {
            background-color: #7c3aed;
            color: white;
        }
        .alert-box {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .profile-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
        }
        .profile-item strong {
            display: block;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .profile-item span {
            font-size: 15px;
            color: #0f172a;
            font-weight: 500;
        }
        .btn-danger {
            background-color: #dc2626;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-secondary {
            background-color: #64748b;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }
        .session-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Dashboard</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="student_dashboard.php" style="background-color: #2563eb;">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="notes.html">Notes</a>
        <a href="assignments.html">Assignments</a>
        <a href="attendance.html">Attendance</a>
        <a href="timetable.html">Timetable</a>
        <a href="profile.html">Profile</a>
        <a href="feedback.html">Feedback</a>
        <a href="about.html">About</a>
        <a href="logout.php" style="background-color: #dc2626; margin-left: auto;">Logout</a>
    </nav>
</header>

<main>
    <section>
        <?php if (isset($_GET["unauthorized"])): ?>
            <div class="alert-box alert-error">
                ⛔ <strong>Access Denied:</strong> You tried to access the Administrator Dashboard, which requires admin privileges. You have been safely redirected to your Student Dashboard.
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2>
                Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "Student") ?>!
            </h2>
            <div>
                <span class="badge <?= ($_SESSION["role"] === "admin") ? "badge-admin" : "badge-student" ?>">
                    <?= htmlspecialchars(strtoupper($_SESSION["role"] ?? "STUDENT")) ?>
                </span>
            </div>
        </div>

        <div class="session-info">
            <div>
                <strong>Active Session Status:</strong> Authenticated via PHP Session ID
                <span style="font-family: monospace; font-size: 12px; color: #1e40af;">
                    (ID: <?= htmlspecialchars(substr(session_id(), 0, 10)) ?>...)
                </span>
            </div>
            <div>
                <span id="timeoutDisplay" style="font-size: 13px; color: #1e3a8a;">
                    ⏱️ Inactivity Timeout: <strong>10:00</strong>
                </span>
            </div>
        </div>

        <h3>Your Account Information</h3>
        <div class="profile-grid">
            <div class="profile-item">
                <strong>Student Name</strong>
                <span><?= htmlspecialchars($student["name"] ?? $_SESSION["name"]) ?></span>
            </div>
            <div class="profile-item">
                <strong>Email Address</strong>
                <span><?= htmlspecialchars($student["email"] ?? $_SESSION["email"]) ?></span>
            </div>
            <div class="profile-item">
                <strong>Course / Department</strong>
                <span><?= htmlspecialchars($student["course"] ?? "Computer Engineering") ?></span>
            </div>
            <div class="profile-item">
                <strong>Academic Year</strong>
                <span><?= htmlspecialchars($student["year"] ?? "3rd Year") ?></span>
            </div>
            <div class="profile-item">
                <strong>Mobile Number</strong>
                <span><?= htmlspecialchars($student["mobile"] ?? "Not Provided") ?></span>
            </div>
            <div class="profile-item">
                <strong>Last Login Timestamp</strong>
                <span style="color: #166534; font-weight: bold;">
                    <?= !empty($student["last_login"]) ? htmlspecialchars($student["last_login"]) : "First Login" ?>
                </span>
            </div>
        </div>

        <div style="margin-top: 25px; display: flex; flex-wrap: wrap; gap: 8px;">
            <a href="logout.php">
                <button type="button" class="btn-danger">🚪 Logout</button>
            </a>
            <?php if (($_SESSION["role"] ?? "") === "admin"): ?>
                <a href="admin_dashboard.php">
                    <button type="button" style="background-color: #7c3aed;">🛡️ Go to Admin Dashboard</button>
                </a>
            <?php endif; ?>
            <a href="profile.html">
                <button type="button" class="btn-secondary">👤 View Full Profile</button>
            </a>
        </div>
    </section>

    <section>
        <h2>Quick Access</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 15px;">
            <a href="notes.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">📚 Study Notes</button>
            </a>
            <a href="assignments.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">📝 Assignments</button>
            </a>
            <a href="attendance.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">✅ Attendance</button>
            </a>
            <a href="timetable.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">📅 Timetable</button>
            </a>
            <a href="feedback.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">💬 Feedback</button>
            </a>
            <a href="events.html" style="text-decoration: none;">
                <button type="button" style="width: 100%;">🎉 Events</button>
            </a>
        </div>
    </section>

    <section>
        <h2>Recent Portal Activity</h2>
        <ul>
            <li>Secure session successfully established via <code>session_regenerate_id(true)</code>.</li>
            <li>Last login timestamp tracked in database: <code><?= htmlspecialchars($student["last_login"] ?? date('Y-m-d H:i:s')) ?></code>.</li>
            <li>10-minute inactivity timer active for your security.</li>
        </ul>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026 - Practical 10: Secure User Authentication</p>
</footer>

<script>
// Visual 10-minute inactivity countdown
let timeLeft = 600; // 10 minutes in seconds
function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    const display = document.getElementById("timeoutDisplay");
    if (display) {
        display.innerHTML = "⏱️ Inactivity Timeout: <strong>" + 
            (minutes < 10 ? "0" : "") + minutes + ":" + 
            (seconds < 10 ? "0" : "") + seconds + "</strong>";
    }
    if (timeLeft <= 0) {
        window.location.href = "login.php?timeout=1";
    } else {
        timeLeft--;
    }
}
setInterval(updateTimer, 1000);
</script>
<script src="script.js"></script>

</body>
</html>
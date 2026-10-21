<?php
/**
 * Practical 10: Protected Administrator Dashboard
 * Strictly restricted to users with role === 'admin'
 */

require_once __DIR__ . "/auth.php";

// Strict Role-Based Access Control (RBAC): Redirect non-admin users
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: student_dashboard.php?unauthorized=1");
    exit;
}

require_once __DIR__ . "/db.php";

// Fetch Student & Admin counts
$totalStudents = 0;
$totalAdmins = 0;

$countStudentsRes = $conn->query("SELECT COUNT(*) AS total FROM students WHERE role = 'student'");
if ($countStudentsRes) {
    $totalStudents = (int)$countStudentsRes->fetch_assoc()["total"];
}

$countAdminsRes = $conn->query("SELECT COUNT(*) AS total FROM students WHERE role = 'admin'");
if ($countAdminsRes) {
    $totalAdmins = (int)$countAdminsRes->fetch_assoc()["total"];
}

// Fetch recently registered students / users
$users = [];
$usersQuery = "SELECT id, name, email, role, course, year, created_at, last_login 
               FROM students 
               ORDER BY id DESC 
               LIMIT 10";
$usersResult = $conn->query($usersQuery);
if ($usersResult) {
    while ($row = $usersResult->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #0f172a;
            margin: 8px 0;
        }
        .stat-label {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .btn-danger {
            background-color: #dc2626;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-purple {
            background-color: #7c3aed;
        }
        .btn-purple:hover {
            background-color: #6d28d9;
        }
        .session-info {
            background-color: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .table-responsive {
            overflow-x: auto;
            margin-top: 15px;
        }
    </style>
</head>

<body>

<header>
    <h1>StudentHub Admin Portal</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="admin_dashboard.php" style="background-color: #7c3aed;">Admin Dashboard</a>
        <a href="student_dashboard.php">Student View</a>
        <a href="students.php">Registered Records</a>
        <a href="events.html">Events</a>
        <a href="notes.html">Notes</a>
        <a href="assignments.html">Assignments</a>
        <a href="logout.php" style="background-color: #dc2626; margin-left: auto;">Logout</a>
    </nav>
</header>

<main>
    <section>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2>
                Welcome, <?= htmlspecialchars($_SESSION["name"] ?? "Administrator") ?>!
            </h2>
            <span class="badge badge-admin">
                🛡️ System Administrator
            </span>
        </div>

        <div class="session-info">
            <div>
                <strong>Security Guard:</strong> RBAC Role Confirmed
                <span style="font-family: monospace; font-size: 12px; color: #6d28d9;">
                    (Session ID: <?= htmlspecialchars(substr(session_id(), 0, 10)) ?>...)
                </span>
            </div>
            <div>
                <span id="timeoutDisplay" style="font-size: 13px; color: #5b21b6;">
                    ⏱️ Inactivity Timeout: <strong>10:00</strong>
                </span>
            </div>
        </div>

        <p>You have full administrative privileges to oversee student accounts and platform activity.</p>

        <!-- Stats Overview Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Students</div>
                <div class="stat-number" style="color: #2563eb;"><?= $totalStudents ?></div>
                <div style="font-size: 12px; color: #64748b;">Registered Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Administrators</div>
                <div class="stat-number" style="color: #7c3aed;"><?= $totalAdmins ?></div>
                <div style="font-size: 12px; color: #64748b;">System Admins</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Accounts</div>
                <div class="stat-number" style="color: #0f172a;"><?= ($totalStudents + $totalAdmins) ?></div>
                <div style="font-size: 12px; color: #64748b;">Database Entries</div>
            </div>
        </div>

        <!-- Quick Access Buttons -->
        <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 8px;">
            <a href="logout.php">
                <button type="button" class="btn-danger">🚪 Logout</button>
            </a>
            <a href="student_dashboard.php">
                <button type="button">👁️ View Student Dashboard</button>
            </a>
            <a href="students.php">
                <button type="button" class="btn-purple">📋 View Full Student Table</button>
            </a>
            <a href="register.html">
                <button type="button" style="background-color: #059669;">➕ Register New Student</button>
            </a>
        </div>
    </section>

    <section>
        <h2>Recent Registered Accounts</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Course / Year</th>
                        <th>Last Login</th>
                        <th>Registered On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= (int)$u["id"] ?></td>
                                <td><strong><?= htmlspecialchars($u["name"]) ?></strong></td>
                                <td><?= htmlspecialchars($u["email"]) ?></td>
                                <td>
                                    <span class="badge <?= ($u["role"] === "admin") ? "badge-admin" : "badge-student" ?>">
                                        <?= htmlspecialchars(strtoupper($u["role"])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(($u["course"] ?? "-") . " / " . ($u["year"] ?? "-")) ?></td>
                                <td style="color: #166534; font-size: 13px;">
                                    <?= !empty($u["last_login"]) ? htmlspecialchars($u["last_login"]) : '<span style="color:#94a3b8;">Never</span>' ?>
                                </td>
                                <td style="font-size: 13px; color: #64748b;">
                                    <?= htmlspecialchars($u["created_at"]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b;">No accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026 - Practical 10: Secure User Authentication</p>
</footer>

<script>
// Visual 10-minute inactivity countdown
let timeLeft = 600;
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
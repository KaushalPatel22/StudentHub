<?php
require "db.php";

$sql = "SELECT id, name, email, mobile, course, year, gender, created_at FROM students ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records - StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .table-container { overflow-x: auto; margin-top: 20px; }
        .empty-message { text-align: center; padding: 20px; color: #64748b; }
    </style>
</head>
<body>
    <header>
        <h1>StudentHub Portal</h1>
        <nav>
            <a href="index.html">Home</a>
            <a href="register.html">Register</a>
            <a href="login.html">Login</a>
            <a href="dashboard.html">Dashboard</a>
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
            <h2>Registered Students</h2>
            
            <div class="table-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Gender</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['mobile']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>

                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">
                        <p>No students have registered yet.</p>
                        <a href="register.html" style="color: #2563eb; text-decoration: none;">Register a student</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close();
}
?>

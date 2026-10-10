<?php
require "db.php";

$errors = [];
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize and collect inputs
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $terms = isset($_POST["terms"]) ? true : false;

    // 2. Server-Side Validation
    if (empty($name) || !preg_match("/^[A-Za-z ]{3,}$/", $name)) {
        $errors[] = "Enter a valid name (letters and spaces only, at least 3 characters).";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (empty($mobile) || !preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must contain exactly 10 digits.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($course)) {
        $errors[] = "Please select a course.";
    }

    if (empty($year)) {
        $errors[] = "Please select your year.";
    }

    if (empty($gender)) {
        $errors[] = "Please select your gender.";
    }

    if (!$terms) {
        $errors[] = "You must accept the terms and conditions.";
    }

    // 3. Database Insertion (if no validation errors)
    if (empty($errors)) {
        // Check for duplicate email
        $checkSql = "SELECT id FROM students WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        if ($checkStmt) {
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();
            if ($checkStmt->num_rows > 0) {
                $errors[] = "A student with this email is already registered.";
            }
            $checkStmt->close();
        } else {
            $errors[] = "Database check failed.";
        }
    }

    if (empty($errors)) {
        // Insert record without storing password
        $sql = "INSERT INTO students (name, email, mobile, course, year, gender) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ssssss", $name, $email, $mobile, $course, $year, $gender);
            
            if ($stmt->execute()) {
                $successMessage = "Registration completed successfully! Your record has been saved.";
                
                // Optional CSV Storage (Task 8)
                $csvData = [$name, $email, $mobile, $course, $year, $gender, date('Y-m-d H:i:s')];
                $fp = fopen('students.csv', 'a');
                if ($fp) {
                    fputcsv($fp, $csvData);
                    fclose($fp);
                }
                
            } else {
                $errors[] = "Failed to register student. Please try again later.";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error. Please try again later.";
        }
    }
    $conn->close();
} else {
    // Direct access to register.php without POST
    $errors[] = "Please submit the registration form.";
}

// Display HTML Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status - StudentHub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .message-box { max-width: 600px; margin: 40px auto; padding: 20px; border-radius: 8px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin-top: 15px; }
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
        <section class="message-box <?php echo !empty($errors) ? 'error' : 'success'; ?>">
            <?php if (!empty($errors)): ?>
                <h2>Registration Failed</h2>
                <ul style="text-align: left; margin-bottom: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="register.html" class="btn">Go Back and Try Again</a>
            <?php else: ?>
                <h2>Success! 🎉</h2>
                <p><?php echo htmlspecialchars($successMessage); ?></p>
                <a href="students.php" class="btn">View Registered Students</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
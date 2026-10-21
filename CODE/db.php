<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "studenthub";

// Enable mysqli exceptions but catch them so we don't expose details
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}

$conn->set_charset("utf8mb4");

?>
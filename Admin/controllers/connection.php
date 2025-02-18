<?php
$servername = "localhost";  // Database server (usually "localhost")
$username = "root";         // Database username
$password = "";             // Database password
$dbname = "chuka";  // Name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

?>

<?php
session_start();
require_once 'connection.php';

// Ensure CSRF token is set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Invalid session. Please try again.";
        error_log("CSRF validation failed for IP: " . $_SERVER['REMOTE_ADDR']);
        header("Location: adminlogin.php");
        exit();
    }

    // Rate Limiting
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $timeframe = 15 * 60; // 15 minutes
    $max_attempts = 5;

    // Check login attempts in the last 15 minutes
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
    mysqli_stmt_bind_param($stmt, "s", $ip_address);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attempts = mysqli_fetch_assoc($result)['count'];
    mysqli_stmt_close($stmt);

    if ($attempts >= $max_attempts) {
        $_SESSION['error_message'] = "Too many login attempts. Try again after 15 minutes.";
        header("Location: adminlogin.php");
        exit();
    }

    // Validate inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required";
        header("Location: adminlogin.php");
        exit();
    }

    // Check admin credentials
    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admins WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            // Log successful login
            $stmt = mysqli_prepare($conn, "INSERT INTO admin_logs (admin_id, ip_address, action, status) VALUES (?, ?, 'login', 'success')");
            mysqli_stmt_bind_param($stmt, "is", $row['id'], $ip_address);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Clear failed login attempts
            $stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE ip_address = ?");
            mysqli_stmt_bind_param($stmt, "s", $ip_address);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Start admin session
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['success_message'] = "Welcome back, " . $row['username'] . "!";
            $_SESSION['last_activity'] = time();

            // Remember Me Feature
            if (!empty($_POST['rememberMe'])) {
                $selector = bin2hex(random_bytes(16));
                $validator = bin2hex(random_bytes(32));
                $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
                $expiry = date('Y-m-d H:i:s', time() + 86400 * 30);

                $stmt = mysqli_prepare($conn, "INSERT INTO auth_tokens (admin_id, selector, hashed_validator, expiry) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "isss", $row['id'], $selector, $hashed_validator, $expiry);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                setcookie("remember_token", $selector . ':' . $validator, time() + 86400 * 30, '/', '', true, true);
            }

            header("Location: adminhome.php");
            exit();
        } else {
            // Log failed attempt
            $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $ip_address, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $_SESSION['error_message'] = "Invalid password";
            header("Location: adminlogin.php");
            exit();
        }
    } else {
        // Log failed attempt
        $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $ip_address, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['error_message'] = "Invalid username";
        header("Location: adminlogin.php");
        exit();
    }
} else {
    header("Location: adminlogin.php");
    exit();
}

mysqli_close($conn);
?>

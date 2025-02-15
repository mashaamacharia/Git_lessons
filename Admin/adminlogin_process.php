<?php
session_start();
require_once  'connection.php';


// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check login attempts
function check_login_attempts($conn, $username, $ip_address) {
    // Delete attempts older than 15 minutes
    $cleanup_stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    if ($cleanup_stmt) {
        $cleanup_stmt->execute();
        $cleanup_stmt->close();
    }

    // Count recent attempts
    $stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts 
                           WHERE (username = ? OR ip_address = ?) 
                           AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['attempt_count'];
}

// Function to record failed attempt
function record_failed_attempt($conn, $username, $ip_address) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ss", $username, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Invalid request verification. Please try again.";
    header("Location: adminlogin.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all fields.";
        header("Location: adminlogin.php");
        exit();
    }

    try {
        // Check login attempts
        $attempts = check_login_attempts($conn, $username, $ip_address);
        if ($attempts >= 5) {
            $_SESSION['error_message'] = "Too many failed login attempts. Please try again after 15 minutes.";
            header("Location: adminlogin.php");
            exit();
        }

        // Prepare SQL statement (updated to `admins` table)
        $stmt = $conn->prepare("SELECT id, username, password, status FROM admins WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Check if account is active
                if ($admin['status'] != 1) {
                    $_SESSION['error_message'] = "Your account is currently inactive. Please contact the system administrator.";
                    header("Location: adminlogin.php");
                    exit();
                }

                // Clear login attempts on successful login
                $clear_stmt = $conn->prepare("DELETE FROM login_attempts WHERE username = ? OR ip_address = ?");
                if ($clear_stmt) {
                    $clear_stmt->bind_param("ss", $username, $ip_address);
                    $clear_stmt->execute();
                    $clear_stmt->close();
                }

                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['is_admin'] = true;

                // Update last login timestamp (updated to `admins` table)
                $updateStmt = $conn->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("i", $admin['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set success message and redirect
                $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($admin['username']) . "!";
                header("Location: adminhome.php");
                exit();
            } else {
                // Record failed attempt
                record_failed_attempt($conn, $username, $ip_address);
                $remaining_attempts = 5 - ($attempts + 1);
                $_SESSION['error_message'] = "Invalid username or password. " . 
                    ($remaining_attempts > 0 ? "You have {$remaining_attempts} attempts remaining." : "Please try again after 15 minutes.");
            }
        } else {
            // Record failed attempt
            record_failed_attempt($conn, $username, $ip_address);
            $remaining_attempts = 5 - ($attempts + 1);
            $_SESSION['error_message'] = "Invalid username or password. " . 
                ($remaining_attempts > 0 ? "You have {$remaining_attempts} attempts remaining." : "Please try again after 15 minutes.");
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred. Please try again later.";
    } finally {
        if (isset($stmt) && $stmt !== false) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Redirect back to login page if we reach here (meaning login failed)
header("Location: adminlogin.php");
exit();

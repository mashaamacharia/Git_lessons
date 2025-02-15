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






<?php
session_start();

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/adminlogin.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2 class="mb-1">Admin Login</h2>
                <p class="text-muted mb-0">Enter your credentials to continue</p>
            </div>

            <!-- Display Error Messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <?php 
                        echo htmlspecialchars($_SESSION['error_message']); 
                        unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Display Success Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="adminlogin_process.php" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                            <label for="username">Username</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </div>
            </form>

            <div class="login-footer">
                <p class="text-muted mb-0">&copy; 2025 Victor Macharia designs. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
        });
    </script>
</body>
</html>



<script>
    document.addEventListener("DOMContentLoaded", function () {
      const announcementsList = document.querySelector(".announcements-list");
      const loadingSpinner = document.getElementById("loadingSpinner");
      const totalAnnouncementsCard = document.getElementById("totalAnnouncementsCard").querySelector("p");
      const activeAnnouncementsCard = document.getElementById("activeAnnouncementsCard").querySelector("p");
      const draftAnnouncementsCard = document.getElementById("draftAnnouncementsCard").querySelector("p");

      // Function to fetch announcements from the server
      async function fetchAnnouncements() {
        try {
          loadingSpinner.style.display = "block";
          const response = await fetch("fetch_announcements.php");
          const data = await response.json();
          renderAnnouncements(data);
          updateStats(data);
        } catch (error) {
          console.error("Error fetching announcements:", error);
          announcementsList.innerHTML = "<p class='text-danger'>Failed to load announcements.</p>";
        } finally {
          loadingSpinner.style.display = "none";
        }
      }

      // Render announcements into the list container
      function renderAnnouncements(announcements) {
        if (announcements.length === 0) {
          announcementsList.innerHTML = "<p>No announcements found.</p>";
          return;
        }
        announcementsList.innerHTML = "";
        announcements.forEach(announcement => {
          const announcementCard = document.createElement("div");
          announcementCard.classList.add("card", "mb-3");
          announcementCard.innerHTML = `
            <div class="card-body">
              <h5 class="card-title">${announcement.title}</h5>
              <p class="card-text">${announcement.content.substring(0, 150)}...</p>
              <p>
                <span class="badge bg-${announcement.status === 'active' ? 'success' : 'secondary'}">${announcement.status}</span>
                <span class="badge bg-${announcement.priority === 'urgent' ? 'danger' : announcement.priority === 'high' ? 'warning' : 'info'}">${announcement.priority}</span>
              </p>
              <button class="btn btn-sm btn-primary view-btn" data-id="${announcement.id}">View</button>
              <button class="btn btn-sm btn-secondary edit-btn" data-id="${announcement.id}">Edit</button>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${announcement.id}">Delete</button>
            </div>
          `;
          announcementsList.appendChild(announcementCard);
        });
      }

      // Update the stats cards based on the fetched announcements
      function updateStats(announcements) {
        const total = announcements.length;
        const active = announcements.filter(a => a.status === "active").length;
        const draft = announcements.filter(a => a.status === "draft").length;

        totalAnnouncementsCard.textContent = total;
        activeAnnouncementsCard.textContent = active;
        draftAnnouncementsCard.textContent = draft;
      }

      // Fetch announcements on page load
      fetchAnnouncements();

      // You can add event listeners for view, edit, delete buttons here
      // For example:
      document.querySelector(".announcements-list").addEventListener("click", function (e) {
        if (e.target.classList.contains("view-btn")) {
          const id = e.target.getAttribute("data-id");
          // Implement AJAX call to load and display the announcement in the view modal.
        }
        if (e.target.classList.contains("edit-btn")) {
          const id = e.target.getAttribute("data-id");
          // Implement AJAX call to load announcement details into the form for editing.
        }
        if (e.target.classList.contains("delete-btn")) {
          const id = e.target.getAttribute("data-id");
          // Implement AJAX call to delete the announcement and update the list.
        }
      });

      // (Optional) Add search, filter, or export functionality event listeners below.
    });
  </script>
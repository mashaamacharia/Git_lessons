<?php
session_start();
// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !$_SESSION['is_admin']) {
    header("Location: adminlogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Electoral System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/adminhome.css">
</head>

<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="navbar-left">
            <button class="toggle-btn" id="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
            <span class="brand-text">Admin Dashboard</span>
        </div>
        <div class="navbar-right">
            <!-- <i class="fas fa-bell nav-icon"></i>
    <i class="fas fa-cog nav-icon"></i> -->
            <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
            <i class="fas fa-user-circle nav-icon"></i>
        </div>
    </nav>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="#" class="active"><i class="fas fa-home"></i>Dashboard</a>
        <a href="register_candidate.php"><i class="fas fa-users"></i>Manage Candidates</a>
        <a href="register_voter.php"><i class="fas fa-user-check"></i>Manage Voters</a>
        <a href="manage_announcements.php"><i class="fas fa-bullhorn"></i>Announcements</a>
        <a href="election_control.php"><i class="fas fa-cogs"></i>Election Settings</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Total Candidates</h4>
                        <!-- Total candidates to be displayed here dynamically -->
                    </div>
                    <i class="fas fa-users stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Registered Voters</h4>
                        <!-- Total voter to be displayed here dynamically -->
                    </div>
                    <i class="fas fa-user-check stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Already Voted</h4>
                        <!-- Display number of candidates that have already voted -->
                    </div>
                    <i class="fas fa-vote-yea stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Voter Turnout</h4>
                        <!-- To be displayed here in percentage -->
                    </div>
                    <i class="fas fa-chart-pie stats-icon"></i>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card activity-feed">
            <h4 class="mb-4">Recent Activity</h4>
            <!-- Recents activities can be updated here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/admin/admin_home.js"></script>
</body>

</html>
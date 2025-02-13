<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Electoral System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/adminhome.css">
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
            <i class="fas fa-bell nav-icon"></i>
            <i class="fas fa-cog nav-icon"></i>
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
                        <p class="stats-number">25</p>
                        <small class="text-success">↑ 12% from last election</small>
                    </div>
                    <i class="fas fa-users stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Registered Voters</h4>
                        <p class="stats-number">500</p>
                        <small class="text-success">↑ 8% from last election</small>
                    </div>
                    <i class="fas fa-user-check stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Active Elections</h4>
                        <p class="stats-number">1</p>
                        <small>Ends in 2 days</small>
                    </div>
                    <i class="fas fa-vote-yea stats-icon"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stats-card">
                    <div class="stats-info">
                        <h4>Voter Turnout</h4>
                        <p class="stats-number">68%</p>
                        <small class="text-warning">↓ 5% from last election</small>
                    </div>
                    <i class="fas fa-chart-pie stats-icon"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button class="action-button">
                <i class="fas fa-plus"></i>
                Add New Candidate
            </button>
            <button class="action-button">
                <i class="fas fa-user-plus"></i>
                Register Voter
            </button>
            <button class="action-button">
                <i class="fas fa-bullhorn"></i>
                New Announcement
            </button>
            <button class="action-button">
                <i class="fas fa-download"></i>
                Download Reports
            </button>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card activity-feed">
            <h4 class="mb-4">Recent Activity</h4>
            <div class="activity-item">
                <strong>New Candidate Registration</strong>
                <p class="mb-0">John Doe registered for President position</p>
                <small class="text-muted">2 minutes ago</small>
            </div>
            <div class="activity-item">
                <strong>Election Update</strong>
                <p class="mb-0">Voter turnout reached 50%</p>
                <small class="text-muted">1 hour ago</small>
            </div>
            <div class="activity-item">
                <strong>System Alert</strong>
                <p class="mb-0">Database backup completed successfully</p>
                <small class="text-muted">3 hours ago</small>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById("toggle-btn");
        const sidebar = document.getElementById("sidebar");
        const content = document.getElementById("content");
        const overlay = document.getElementById("overlay");

        function toggleSidebar() {
            sidebar.classList.toggle("collapsed");
            content.classList.toggle("expanded");

            if (window.innerWidth <= 768) {
                overlay.classList.toggle("active");
            }
        }

        toggleBtn.addEventListener("click", toggleSidebar);
        overlay.addEventListener("click", toggleSidebar);

        document.addEventListener("click", function (event) {
            if (window.innerWidth <= 768 &&
                !sidebar.contains(event.target) &&
                !toggleBtn.contains(event.target) &&
                !sidebar.classList.contains("collapsed")) {
                toggleSidebar();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Settings | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/election_control.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="mb-0">Election Settings</h1>
            <p class="mb-0">Configure and monitor election parameters</p>
        </div>

        <!-- Status Cards -->
        <div class="status-cards">
            <div class="status-card">
                <h3>Registered Voters</h3>
              <!-- To be displayed here dynamically -->
                <small class="text-muted">Total eligible voters</small>
            </div>
            <div class="status-card">
                <h3>Time Remaining</h3>
                <!-- To be displayed here 48:00:00 -->
                <small class="text-muted">Until election ends</small>
            </div>
            <!-- <div class="status-card">
                <h3>Current Turnout</h3>
                <small class="text-muted">Of eligible voters</small>
            </div> -->
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="mb-4">
                <h3>Election Status</h3>
                <span id="electionStatus" class="election-status status-not-started">Not Started</span>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Election Start Date & Time</label>
                        <div class="date-picker">
                            <input type="datetime-local" id="electionStart" class="form-control">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Election End Date & Time</label>
                        <div class="date-picker">
                            <input type="datetime-local" id="electionEnd" class="form-control">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="countdown-timer">
                <span id="countdown">00:00:00</span>
                <span class="timer-label">Time Until Next Phase</span>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="election_control.js"></script>
</body>

</html>
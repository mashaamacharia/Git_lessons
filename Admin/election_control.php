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
                <p class="h2 mb-0">1,234</p>
                <small class="text-muted">Total eligible voters</small>
            </div>
            <div class="status-card">
                <h3>Time Remaining</h3>
                <p class="h2 mb-0" id="timeRemaining">48:00:00</p>
                <small class="text-muted">Until election ends</small>
            </div>
            <div class="status-card">
                <h3>Current Turnout</h3>
                <p class="h2 mb-0">45%</p>
                <small class="text-muted">Of eligible voters</small>
            </div>
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

            <div class="control-buttons">
                <button class="control-button start-button" id="startElection">
                    <i class="fas fa-play me-2"></i>Start Election
                </button>
                <button class="control-button end-button" id="endElection">
                    <i class="fas fa-stop me-2"></i>End Election
                </button>
            </div>
        </div>
    </div>

    <script>
        // Election Status Management
        const electionStatus = document.getElementById("electionStatus");
        const startButton = document.getElementById("startElection");
        const endButton = document.getElementById("endElection");

        startButton.addEventListener("click", function () {
            electionStatus.textContent = "Ongoing";
            electionStatus.className = "election-status status-ongoing";
            startButton.disabled = true;
            endButton.disabled = false;
            startCountdown();
        });

        endButton.addEventListener("click", function () {
            electionStatus.textContent = "Ended";
            electionStatus.className = "election-status status-ended";
            endButton.disabled = true;
            startButton.disabled = true;
            stopCountdown();
        });

        // Countdown Timer
        let countdownInterval;
        function startCountdown() {
            const endDate = new Date(document.getElementById("electionEnd").value);

            countdownInterval = setInterval(() => {
                const now = new Date();
                const distance = endDate - now;

                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("countdown").textContent =
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (distance < 0) {
                    stopCountdown();
                    document.getElementById("countdown").textContent = "EXPIRED";
                    endButton.click();
                }
            }, 1000);
        }

        function stopCountdown() {
            clearInterval(countdownInterval);
        }

        // Date Validation
        document.getElementById("electionStart").addEventListener("change", function () {
            const startDate = new Date(this.value);
            document.getElementById("electionEnd").min = this.value;
        });

        document.getElementById("electionEnd").addEventListener("change", function () {
            const endDate = new Date(this.value);
            document.getElementById("electionStart").max = this.value;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
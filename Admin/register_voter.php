<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register_voter.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="mb-0">Voter Management</h1>
            <p class="mb-0">Manage and monitor registered voters</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-users icon"></i>
                <h3>Total Voters</h3>
                <p class="h2 mb-0">0</p>
                <small class="text-muted">Registered voters</small>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="action-button primary" data-bs-toggle="modal" data-bs-target="#addVoterModal">
                    <i class="fas fa-plus-circle me-2"></i>Add New Voter
                </button>
                <!-- <div>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="fas fa-upload me-2"></i>Import
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div> -->
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search voters...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="facultyFilter" class="form-select">
                            <option value="" disabled selected>Choose faculty...</option>
                            <option value="Science & Tech">Science & Tech</option>
                            <option value="Law">Law</option>
                            <option value="Agriculture">Agriculture</option>
                            <option value="Business">Business</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Education">Education</option>
                            <option value="Environment">Environment</option>
                            <option value="Humanities">Humanities</option>
                            <option value="Nursing">Nursing</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Name</th>
                            <th>Faculty</th>
                            <th>ID No</th>
                            <th>Hostel ID</th>
                            <th>Phone Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                </table>
            </div>
        </div>
    </div>

    <!-- Add Voter Modal -->
    <div class="modal fade" id="addVoterModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Voter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addVoterForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="registration" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Faculty</label>
                            <select name="faculty" class="form-select" required>
                                <option value="" disabled selected>Choose faculty...</option>
                                <option value="Science & Tech">Science & Tech</option>
                                <option value="Law">Law</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Business">Business</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Education">Education</option>
                                <option value="Environment">Environment</option>
                                <option value="Humanities">Humanities</option>
                                <option value="Nursing">Nursing</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input type="text" name="idno" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hostel ID</label>
                            <input type="text" name="hostelid" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}"
                                title="Please enter a valid 10-digit phone number">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="action-button primary">Add Voter</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="voter.js"></script>
</body>

</html>
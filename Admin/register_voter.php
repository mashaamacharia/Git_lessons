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
                <p class="h2 mb-0">1,234</p>
                <small class="text-muted">Registered voters</small>
            </div>
            <div class="stat-card">
                <i class="fas fa-user-check icon"></i>
                <h3>Active Voters</h3>
                <p class="h2 mb-0">1,180</p>
                <small class="text-muted">Currently eligible</small>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-bar icon"></i>
                <h3>Participation Rate</h3>
                <p class="h2 mb-0">95.6%</p>
                <small class="text-muted">Last election</small>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="action-button primary" data-bs-toggle="modal" data-bs-target="#addVoterModal">
                    <i class="fas fa-plus-circle me-2"></i>Add New Voter
                </button>
                <div>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="fas fa-upload me-2"></i>Import
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
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
                            <option value="">All Faculties</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Business">Business</option>
                            <option value="Education">Education</option>
                            <option value="Science">Science</option>
                            <option value="Arts">Arts</option>
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
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CU/001/2024</td>
                            <td>John Doe</td>
                            <td>Engineering</td>
                            <td>****1234</td>
                            <td><span class="voter-status active">Active</span></td>
                            <td class="action-icons">
                                <i class="fas fa-edit text-primary" title="Edit"></i>
                                <i class="fas fa-trash-alt text-danger" title="Delete"></i>
                                <i class="fas fa-ban text-warning" title="Suspend"></i>
                            </td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
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
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Faculty</label>
                            <select class="form-select" required>
                                <option value="">Select Faculty</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Business">Business</option>
                                <option value="Education">Education</option>
                                <option value="Science">Science</option>
                                <option value="Arts">Arts</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" rows="3"></textarea>
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
</body>

</html>
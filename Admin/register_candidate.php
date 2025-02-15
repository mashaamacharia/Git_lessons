<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register_candidate.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="mb-0">Candidate Management</h1>
            <p class="mb-0">Manage and monitor election candidates</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Candidates</h3>
                <p class="h2 mb-0">24</p>
                <small class="text-muted">Across all positions</small>
            </div>

        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="action-button primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
                    <i class="fas fa-plus-circle me-2"></i>Add New Candidate
                </button>
                <!-- <button class="action-button btn btn-outline-secondary">
                    <i class="fas fa-download me-2"></i>Export Data
                </button> -->
            </div>

            <!-- Search and Filter -->
            <div class="search-filter-container">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search candidates...">
                </div>
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
                <select id="positionFilter" class="form-select">
                    <option value="" disabled selected>Choose position...</option>
                    <option value="faculty-rep">Faculty-Rep</option>
                    <option value="male-resident-rep">Male Resident-Rep</option>
                    <option value="female-resident-rep">Female Resident-Rep</option>
                    <option value="male-non-resident-rep">Male Non-Resident</option>
                    <option value="female-non-resident-rep">Female Non-Resident</option>

                </select>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Faculty</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="/api/placeholder/40/40" class="rounded-circle me-3" alt="Profile">
                                    <div>
                                        <div class="fw-bold">John Doe</div>
                                        <small class="text-muted">CU/123/456</small>
                                    </div>
                                </div>
                            </td>
                            <td>President</td>
                            <td>Engineering</td>
                            <td><span class="status-badge active">Active</span></td>
                            <td class="action-icons">
                                <i class="fas fa-edit text-primary"></i>
                                <i class="fas fa-trash-alt text-danger"></i>
                                <i class="fas fa-eye text-info"></i>
                            </td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Candidate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCandidateForm" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration Number</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <select class="form-select" id="position" name="position" required>
                                <option value="" disabled selected>Choose position...</option>
                                <option value="faculty-rep">Faculty-Rep</option>
                                <option value="male-resident-rep">Male Resident-Rep</option>
                                <option value="female-resident-rep">Female Resident-Rep</option>
                                <option value="male-non-resident-rep">Male Non-Resident</option>
                                <option value="female-non-resident-rep">Female Non-Resident</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Faculty</label>
                            <select class="form-select" id="faculty" name="faculty" required>
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
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" required>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="action-button primary">Add Candidate</button>
                            <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="register_candidate.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Candidates | Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../assets/css/admin/register_candidate.css" />
  <style>
    /* Example styling for the modal header based on candidate status */
    .modal-header.active-status {
      background-color: #d4edda; /* green */
    }
    .modal-header.inactive-status {
      background-color: #f8d7da; /* red */
    }
  </style>
</head>
<body>
  <div class="dashboard-container container my-4">
    <!-- Header Section -->
    <div class="header-section mb-4">
      <h1 class="mb-0">Candidate Management</h1>
      <p class="mb-0">Manage and monitor election candidates</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards mb-4">
      <div class="stat-card p-3 border rounded">
        <h3>Total Candidates</h3>
        <p class="h2 mb-0" id="totalCandidates">0</p>
        <small class="text-muted">Across all positions</small>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Action Buttons -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCandidateModal">
          <i class="fas fa-plus-circle me-2"></i>Add New Candidate
        </button>
      </div>

      <!-- Search and Filter -->
      <div class="search-filter-container mb-4 row g-2">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search candidates..." />
          </div>
        </div>
        <div class="col-md-4">
          <select id="facultyFilter" class="form-select">
            <option value="" selected>All Faculties</option>
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
          <select id="positionFilter" class="form-select">
            <option value="" selected>All Positions</option>
            <option value="faculty-rep">Faculty-Rep</option>
            <option value="male-resident-rep">Male Resident-Rep</option>
            <option value="female-resident-rep">Female Resident-Rep</option>
            <option value="male-non-resident-rep">Male Non-Resident</option>
            <option value="female-non-resident-rep">Female Non-Resident</option>
          </select>
        </div>
      </div>

      <!-- Candidates Table -->
      <div class="table-container table-responsive">
        <table class="table custom-table table-striped">
          <thead>
            <tr>
              <th>Registration</th>
              <th>Name</th>
              <th>Position</th>
              <th>Faculty</th>
              <th>Email</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Candidate rows will be inserted dynamically via AJAX -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Centered Alert Overlay (Injected by JS if needed) -->
  <div id="centerOverlay" style="display: none;"></div>

  <!-- Add/Edit Candidate Modal -->
  <div class="modal fade" id="addCandidateModal" tabindex="-1" aria-labelledby="addCandidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header" id="modalHeader">
          <h5 class="modal-title" id="addCandidateModalLabel">Add New Candidate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Form: field names match the JS/PHP code -->
          <form id="addCandidateForm" class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" name="name" required />
            </div>
            <div class="col-md-6">
              <label class="form-label">Registration Number</label>
              <input type="text" class="form-control" name="registration" required />
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
              <select class="form-select" id="faculty" name="faculty">
                <option value="" selected>Choose faculty...</option>
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
              <input type="email" class="form-control" name="email" required />
            </div>
            <div class="col-12 mt-4">
              <button type="submit" class="btn btn-primary">Submit</button>
              <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery (make sure it's loaded before your custom JS) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Custom JS -->
  <script src="../../assets/js/admin/register_candidate.js"></script>
</body>
</html>

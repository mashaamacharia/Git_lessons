<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Announcements | Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="../../assets/css/admin/manage_announcements.css" />
</head>

<body>
  <div class="dashboard-container">
    <!-- Header Section -->
    <div class="header-section">
      <h1 class="mb-0">Announcement Management</h1>
      <p class="mb-0">Create and manage election announcements</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
      <div class="stat-card" id="totalAnnouncementsCard">
        <h3>Total Announcements</h3>
        <p class="h2 mb-0">0</p>
        <small class="text-muted">All time</small>
      </div>
      <div class="stat-card" id="activeAnnouncementsCard">
        <h3>Active Announcements</h3>
        <p class="h2 mb-0">0</p>
        <small class="text-muted">Currently visible</small>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Action Buttons -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <button class="action-button primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
          <i class="fas fa-plus-circle me-2"></i>New Announcement
        </button>
        <div>
          <button class="action-button btn btn-outline-secondary me-2" id="filterButton">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
        </div>
      </div>

      <!-- Filter Options (Initially Hidden) -->
      <div class="filter-options mb-4" id="filterOptions" style="display: none;">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <select class="form-select" id="statusFilter">
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
              <div class="col-md-4">
                <select class="form-select" id="priorityFilter">
                  <option value="">All Priorities</option>
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              <div class="col-md-4">
                <button class="btn btn-primary w-100" id="applyFilters">Apply Filters</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="input-group mb-4">
        <span class="input-group-text">
          <i class="fas fa-search"></i>
        </span>
        <input type="text" class="form-control" id="searchAnnouncements" placeholder="Search announcements..." />
      </div>

      <!-- Announcements List -->
      <div class="announcements-list">
        <!-- Announcements will be loaded here dynamically via AJAX -->
      </div>

      <!-- Loading Spinner -->
      <div class="text-center mt-4" id="loadingSpinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Add/Edit Announcement Modal -->
  <div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Create New Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Note: The form will be submitted via AJAX to process_announcements.php -->
          <form id="addAnnouncementForm" action="process_announcements.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="announcement_id" id="announcement_id" value="" />

            <div class="mb-3">
              <label class="form-label">Announcement Title</label>
              <input type="text" class="form-control" name="title" required placeholder="Enter announcement title" />
            </div>
            <div class="mb-3">
              <label class="form-label">Content</label>
              <textarea class="form-control" name="content" rows="6" required placeholder="Enter announcement content"></textarea>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <option value="active">Active</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority">
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Attachments (Optional)</label>
              <input type="file" class="form-control" name="attachments[]" multiple />
              <div id="existingAttachments" class="mt-2">
                <!-- Existing attachments will be listed here when editing -->
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="action-button primary" id="submitButton">Publish Announcement</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- View Announcement Modal -->
  <div class="modal fade" id="viewAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">View Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="viewAnnouncementContent">
            <!-- Announcement content will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Toast -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-success text-white">
        <strong class="me-auto">Success</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="toastMessage"></div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../assets/js/admin/manage_announcements.js"></script>
  <!-- Include your custom JS file for announcement management -->
</body>

</html>

$(document).ready(function () {
    candidateManager.init();
});

const candidateManager = {
    init: function () {
        this.bindEvents();
        this.loadCandidates();
        this.updateTotalCandidates();
        // Refresh total candidates every 30 seconds.
        setInterval(this.updateTotalCandidates.bind(this), 30000);
    },

    bindEvents: function () {
        // Handle form submission for adding/updating candidate.
        $('#addCandidateForm').on('submit', this.handleAddCandidate.bind(this));

        // Search and filter events.
        $('#searchInput').on('keyup', this.debounce(this.loadCandidates.bind(this), 500));
        $('#facultyFilter, #positionFilter').on('change', this.loadCandidates.bind(this));

        // Delegate click events for edit, delete, and suspend buttons.
        $('.table-container').on('click', '.fa-edit', this.handleEditCandidate.bind(this));
        $('.table-container').on('click', '.fa-trash-alt', this.handleDeleteCandidate.bind(this));
        $('.table-container').on('click', '.fa-ban, .fa-check-circle', this.handleToggleStatus.bind(this));
    },

    handleAddCandidate: function (e) {
        e.preventDefault();
        const form = $(e.target);
        let mode = form.data('mode') || 'add';
        let formData = {
            action: mode,
            fullname: form.find('input[name="fullname"]').val().trim(),
            position: form.find('#position').val(),
            faculty: form.find('#faculty').val(),
            email: form.find('input[name="email"]').val().trim()
        };

        // For update, include candidate ID.
        if (mode === 'update') {
            formData.id = form.data('candidateId');
        }

        this.showCenterOverlay('loading');

        $.ajax({
            url: 'candidate-process.php',
            type: 'POST',
            data: formData,
            success: (response) => {
                let result;
                try {
                    result = JSON.parse(response);
                } catch (error) {
                    console.error('Invalid JSON:', response);
                    this.showCenterOverlay('error', 'Invalid server response.');
                    return;
                }

                if (result.success) {
                    this.showCenterOverlay('success', result.message);
                    $('#addCandidateModal').modal('hide');
                    form.trigger('reset');
                    form.data('mode', 'add').removeData('candidateId');
                    this.loadCandidates();
                    this.updateTotalCandidates();
                } else {
                    this.showCenterOverlay('error', result.message);
                }
            },
            error: () => {
                this.showCenterOverlay('error', 'An error occurred while processing the request.');
            }
        });
    },

    handleEditCandidate: function (e) {
        const row = $(e.target).closest('tr');
        const candidateId = row.data('id');
        const fullname = row.find('td').eq(0).find('.fw-bold').text();
        const email = row.find('td').eq(0).find('small').text();
        const position = row.find('td').eq(1).text();
        const faculty = row.find('td').eq(2).text();

        const form = $('#addCandidateForm');
        form.data('mode', 'update').data('candidateId', candidateId);
        form.find('input[name="fullname"]').val(fullname);
        form.find('#position').val(position);
        form.find('#faculty').val(faculty);
        form.find('input[name="email"]').val(email);
        $('#addCandidateModal').modal('show');
    },

    handleDeleteCandidate: function (e) {
        const row = $(e.target).closest('tr');
        const candidateId = row.data('id');

        if (!candidateId) {
            this.showCenterOverlay('error', 'Candidate ID not found.');
            return;
        }

        // Using a Bootstrap confirmation modal (or simple confirm for now).
        if (confirm('Are you sure you want to delete this candidate?')) {
            this.showCenterOverlay('loading');
            $.ajax({
                url: 'candidate-process.php',
                type: 'POST',
                data: { action: 'delete', id: candidateId },
                success: (response) => {
                    let result;
                    try {
                        result = JSON.parse(response);
                    } catch (error) {
                        console.error('Invalid JSON:', response);
                        this.showCenterOverlay('error', 'Invalid server response.');
                        return;
                    }
                    if (result.success) {
                        this.showCenterOverlay('success', result.message);
                        this.loadCandidates();
                        this.updateTotalCandidates();
                    } else {
                        this.showCenterOverlay('error', result.message);
                    }
                },
                error: () => {
                    this.showCenterOverlay('error', 'An error occurred while deleting the candidate.');
                }
            });
        }
    },

    handleToggleStatus: function (e) {
        const row = $(e.target).closest('tr');
        const candidateId = row.data('id');
        // Get current status from the row. Assume it is displayed in a span with class "status-badge".
        let currentStatus = row.find('.status-badge').text().trim();
        if (!candidateId || !currentStatus) {
            this.showCenterOverlay('error', 'Candidate ID or status not found.');
            return;
        }

        this.showCenterOverlay('loading');

        $.ajax({
            url: 'candidate-process.php',
            type: 'POST',
            data: { action: 'update_status', id: candidateId, status: currentStatus },
            success: (response) => {
                let result;
                try {
                    result = JSON.parse(response);
                } catch (error) {
                    console.error('Invalid JSON:', response);
                    this.showCenterOverlay('error', 'Invalid server response.');
                    return;
                }
                if (result.success) {
                    this.showCenterOverlay('success', result.message);
                    // Update the status badge in the row.
                    row.find('.status-badge')
                        .text(result.newStatus)
                        .toggleClass('active', result.newStatus === 'Active')
                        .toggleClass('inactive', result.newStatus === 'Inactive');
                    // Also, change the suspend icon accordingly.
                    const icon = row.find('.action-icons i.fa-ban, .action-icons i.fa-check-circle');
                    if (result.newStatus === 'Active') {
                        icon.removeClass('fa-check-circle').addClass('fa-ban').attr('title', 'Suspend');
                    } else {
                        icon.removeClass('fa-ban').addClass('fa-check-circle').attr('title', 'Activate');
                    }
                } else {
                    this.showCenterOverlay('error', result.message);
                }
            },
            error: () => {
                this.showCenterOverlay('error', 'An error occurred while updating candidate status.');
            }
        });
    },

    updateTotalCandidates: function () {
        $.ajax({
            url: 'candidate-process.php',
            type: 'POST',
            data: { action: 'get_total' },
            success: (response) => {
                let result;
                try {
                    result = JSON.parse(response);
                } catch (error) {
                    console.error('Invalid JSON:', response);
                    return;
                }
                $('.stat-card .h2').text(result.total);
            },
            error: () => {
                console.error('Failed to retrieve total candidates.');
            }
        });
    },

    loadCandidates: function () {
        const filters = {
            action: 'get',
            search: $('#searchInput').val(),
            faculty: $('#facultyFilter').val(),
            position: $('#positionFilter').val()
        };

        $.ajax({
            url: 'candidate-process.php',
            type: 'POST',
            data: filters,
            success: (response) => {
                let candidates;
                try {
                    candidates = JSON.parse(response);
                } catch (error) {
                    console.error('Invalid JSON:', response);
                    this.showCenterOverlay('error', 'Invalid server response.');
                    return;
                }
                this.updateCandidatesTable(candidates);
            },
            error: () => {
                this.showCenterOverlay('error', 'Failed to load candidates.');
            }
        });
    },

    updateCandidatesTable: function (candidates) {
        const tbody = $('.custom-table tbody');
        tbody.empty();

        if (candidates.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center">No candidates found</td>
                </tr>
            `);
            return;
        }

        candidates.forEach(candidate => {
            // Use a suspend icon that is "fa-ban" if candidate is Active and "fa-check-circle" if Inactive.
            let suspendIcon = (candidate.status === 'Active' || candidate.status === '') ? 
                              '<i class="fas fa-ban text-warning" title="Suspend"></i>' : 
                              '<i class="fas fa-check-circle text-success" title="Activate"></i>';

            tbody.append(`
                <tr data-id="${candidate.id}">
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${candidate.photo_path}" class="rounded-circle me-3" alt="Profile" width="40" height="40">
                            <div>
                                <div class="fw-bold">${candidate.name}</div>
                                <small class="text-muted">${candidate.email}</small>
                            </div>
                        </div>
                    </td>
                    <td>${candidate.position}</td>
                    <td>${candidate.faculty ? candidate.faculty : ''}</td>
                    <td><span class="status-badge ${candidate.status === 'Inactive' ? 'inactive' : 'active'}">${candidate.status || 'Active'}</span></td>
                    <td class="action-icons">
                        <i class="fas fa-edit text-primary" title="Edit"></i>
                        <i class="fas fa-trash-alt text-danger" title="Delete"></i>
                        ${suspendIcon}
                    </td>
                </tr>
            `);
        });
    },

    /**
     * Displays a centered overlay with a loading spinner or a Bootstrap alert.
     * @param {string} type - 'loading', 'success', or 'error'
     * @param {string} message - Message to display for success/error.
     */
    showCenterOverlay: function (type, message = '') {
        if ($('#centerOverlay').length === 0) {
            $('body').append(`
                <div id="centerOverlay" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1050;
                    display: none;">
                    <div id="centerOverlayContent" style="
                        background: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        text-align: center;
                        min-width: 250px;">
                    </div>
                </div>
            `);
        }

        let contentHtml = '';
        if (type === 'loading') {
            contentHtml = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading...</div>
            `;
        } else if (type === 'success') {
            contentHtml = `<div class="alert alert-success" role="alert" style="margin: 0;">${message}</div>`;
        } else if (type === 'error') {
            contentHtml = `<div class="alert alert-danger" role="alert" style="margin: 0;">${message}</div>`;
        }
        
        $('#centerOverlayContent').html(contentHtml);
        $('#centerOverlay').fadeIn(200);
        
        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                $('#centerOverlay').fadeOut(200);
            }, 3000);
        }
    },

    // Utility: Debounce function.
    debounce: function (func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
};
<!-- register_candidate.js -->
<?php
// candidate-process.php
require 'connection.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    // Retrieve form data; faculty is optional.
    $fullname = trim($_POST['fullname'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $faculty  = ($faculty === '') ? null : $faculty; // set to null if empty
    $email    = trim($_POST['email'] ?? '');

    // Validate required fields (fullname, position, email)
    if (empty($fullname) || empty($position) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in the required fields: Full Name, Position, and Email.'
        ]);
        exit;
    }

    // Default photo and status
    $photo_path = '/api/placeholder/40/40';
    $status = "Active";

    $stmt = $conn->prepare("INSERT INTO candidates (name, position, faculty, email, photo_path, status) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssssss", $fullname, $position, $faculty, $email, $photo_path, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'update') {
    // Update candidate info; requires candidate ID.
    $id       = trim($_POST['id'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $faculty  = ($faculty === '') ? null : $faculty;
    $email    = trim($_POST['email'] ?? '');

    if (empty($id) || empty($fullname) || empty($position) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in the required fields: ID, Full Name, Position, and Email.'
        ]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE candidates SET name = ?, position = ?, faculty = ?, email = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssssi", $fullname, $position, $faculty, $email, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'delete') {
    // Delete candidate by ID.
    $id = trim($_POST['id'] ?? '');
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Candidate ID is required for deletion.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Candidate deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete candidate.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'update_status') {
    // Toggle candidate status (Active <-> Inactive)
    $id = trim($_POST['id'] ?? '');
    $currentStatus = trim($_POST['status'] ?? '');

    if (empty($id) || empty($currentStatus)) {
        echo json_encode(['success' => false, 'message' => 'Candidate ID and current status are required.']);
        exit;
    }

    // Toggle status
    $newStatus = ($currentStatus === 'Active') ? 'Inactive' : 'Active';

    $stmt = $conn->prepare("UPDATE candidates SET status = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("si", $newStatus, $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Candidate status updated to ' . $newStatus . '.',
            'newStatus' => $newStatus
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update candidate status.']);
    }
    $stmt->close();
    exit;
}

if ($action === 'get_total') {
    // Get total number of candidates.
    $result = $conn->query("SELECT COUNT(*) as total FROM candidates");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['total' => $row['total']]);
    } else {
        echo json_encode(['total' => 0]);
    }
    exit;
}

if ($action === 'get') {
    // Retrieve candidates with optional filters.
    $search   = trim($_POST['search'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $position = trim($_POST['position'] ?? '');

    $query = "SELECT * FROM candidates WHERE 1 ";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $query .= " AND name LIKE ? ";
        $params[] = '%' . $search . '%';
        $types   .= 's';
    }
    if (!empty($faculty)) {
        $query .= " AND faculty = ? ";
        $params[] = $faculty;
        $types   .= 's';
    }
    if (!empty($position)) {
        $query .= " AND position = ? ";
        $params[] = $position;
        $types   .= 's';
    }

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode([]);
        exit;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
    echo json_encode($candidates);
    $stmt->close();
    exit;
}

// Optionally, add additional actions if needed.
?>

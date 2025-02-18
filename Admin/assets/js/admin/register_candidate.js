$(document).ready(function () {
    // Initial fetch of candidate data
    fetchCandidates();
  
    // Trigger fetching candidates when search or filters change
    $('#searchInput, #facultyFilter, #positionFilter').on('keyup change', function () {
        fetchCandidates();
    });
  
    // Handle form submission for adding/updating candidate
    $('#addCandidateForm').submit(function (e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
    
        // Determine if this is an add or update operation based on hidden candidate id field
        var candidateId = $(this).find('input[name="id"]').val();
        var action = candidateId ? 'update' : 'add';
        formData.push({ name: 'action', value: action });
    
        $.ajax({
            url: '../../models/admin/candidate-process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                showMessage(response.success ? 'success' : 'error', response.message);
                if (response.success) {
                    // Close the modal and reset the form on success
                    $('#addCandidateModal').modal('hide');
                    $('#addCandidateForm')[0].reset();
                    // Remove the hidden candidate id field if it exists
                    $('#addCandidateForm').find('input[name="id"]').remove();
                    // Reset modal title and header status styling
                    $('#addCandidateModalLabel').text('Add New Candidate');
                    $('#modalHeader').removeClass('active-status inactive-status');
                    fetchCandidates();
                }
            },
            error: function () {
                showMessage('error', 'An error occurred');
            }
        });
    });
  
    // Delegate event for clicking the edit icon
    $('table').on('click', '.editCandidate', function () {
        var candidateId = $(this).data('id');
        $.ajax({
            url: '../../models/admin/candidate-process.php',
            type: 'GET',
            data: { action: 'get', id: candidateId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var candidate = response.data;
                    // If not already in the form, append a hidden input to store the candidate id
                    if ($('#addCandidateForm').find('input[name="id"]').length === 0) {
                        $('#addCandidateForm').append('<input type="hidden" name="id" />');
                    }
                    // Prefill the form fields with candidate details
                    $('#addCandidateForm').find('input[name="id"]').val(candidate.id);
                    $('#addCandidateForm').find('input[name="name"]').val(candidate.name);
                    $('#addCandidateForm').find('input[name="registration"]').val(candidate.registration_number);
                    $('#addCandidateForm').find('select[name="position"]').val(candidate.position);
                    $('#addCandidateForm').find('select[name="faculty"]').val(candidate.faculty);
                    $('#addCandidateForm').find('input[name="email"]').val(candidate.email);
    
                    // Change the modal header background based on candidate status
                    if (candidate.status.toLowerCase() === 'active') {
                        $('#modalHeader').removeClass('inactive-status').addClass('active-status');
                    } else {
                        $('#modalHeader').removeClass('active-status').addClass('inactive-status');
                    }
    
                    // Update modal title and show the modal
                    $('#addCandidateModalLabel').text('Edit Candidate');
                    $('#addCandidateModal').modal('show');
                } else {
                    showMessage('error', 'Failed to fetch candidate details');
                }
            },
            error: function () {
                showMessage('error', 'An error occurred');
            }
        });
    });
  
    // Delegate event for clicking the ban/toggle icon
    $('table').on('click', '.banCandidate', function (e) {
        e.preventDefault();
        var candidateId = $(this).data('id');
        var currentStatus = $(this).data('status').toLowerCase();
        var confirmMessage = (currentStatus === 'active') ? 
            'Are you sure you want to deactivate the candidate?' : 
            'Are you sure you want to activate the candidate?';
    
        showConfirm(confirmMessage).then(function (confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '../../models/admin/candidate-process.php',
                    type: 'POST',
                    data: { action: 'ban', id: candidateId },
                    dataType: 'json',
                    success: function (response) {
                        showMessage(response.success ? 'success' : 'error', response.message);
                        if (response.success) {
                            fetchCandidates();
                        }
                    },
                    error: function () {
                        showMessage('error', 'An error occurred while updating status.');
                    }
                });
            }
        });
    });
  
    // Delegate event for clicking the delete icon
    $('table').on('click', '.deleteCandidate', function (e) {
        e.preventDefault();
        var candidateId = $(this).data('id');
    
        showConfirm('Are you sure you want to delete this candidate?').then(function (confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '../../models/admin/candidate-process.php',
                    type: 'POST',
                    data: { action: 'delete', id: candidateId },
                    dataType: 'json',
                    success: function (response) {
                        showMessage(response.success ? 'success' : 'error', response.message);
                        if (response.success) {
                            fetchCandidates();
                        }
                    },
                    error: function () {
                        showMessage('error', 'An error occurred while deleting candidate.');
                    }
                });
            }
        });
    });
});
  
// Function to fetch candidates and update the table and stats card
function fetchCandidates() {
    var search = $('#searchInput').val();
    var faculty = $('#facultyFilter').val();
    var position = $('#positionFilter').val();
  
    $.ajax({
        url: '../../models/admin/candidate-process.php',
        type: 'GET',
        data: { action: 'fetch', search: search, faculty: faculty, position: position },
        dataType: 'json',
        success: function (response) {
            $('#totalCandidates').text(response.total);
            var tbody = $('table tbody');
            tbody.empty();
    
            if (response.data.length > 0) {
                $.each(response.data, function (index, candidate) {
                    // Create status badge with appropriate class
                    var statusClass = candidate.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive';
                    var statusBadge = `<span class="status-badge ${statusClass}">${candidate.status}</span>`;
                    
                    var actions = `
                        <i class="fas fa-edit text-primary editCandidate" title="Edit" data-id="${candidate.id}" style="cursor:pointer; margin-right:8px;"></i>
                        <i class="fas fa-trash-alt text-danger deleteCandidate" title="Delete" data-id="${candidate.id}" style="cursor:pointer; margin-right:8px;"></i>
                        <i class="fas fa-ban text-warning banCandidate" title="Toggle Status" data-id="${candidate.id}" data-status="${candidate.status}" style="cursor:pointer;"></i>
                    `;
                    
                    var row = '<tr>' +
                        '<td>' + candidate.registration_number + '</td>' +
                        '<td>' + candidate.name + '</td>' +
                        '<td>' + candidate.position + '</td>' +
                        '<td>' + candidate.faculty + '</td>' +
                        '<td>' + candidate.email + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' + actions + '</td>' +
                    '</tr>';
                    tbody.append(row);
                });
            } else {
                tbody.append('<tr><td colspan="7" class="text-center">No candidates found.</td></tr>');
            }
        },
        error: function () {
            showMessage('error', 'Error fetching candidates');
        }
    });
}

// Function to display Django-like messages
function showMessage(type, message) {
    // Remove any existing message
    $('.message-overlay').remove();
    
    // Create new message element
    var messageDiv = $('<div>', {
        class: `message-overlay message-${type}`,
        text: message
    });
    
    // Add to body
    $('body').append(messageDiv);
    
    // Trigger reflow to enable transition
    messageDiv[0].offsetHeight;
    
    // Show message
    messageDiv.addClass('show');
    
    // Auto-dismiss after 2 seconds
    setTimeout(function() {
        messageDiv.removeClass('show');
        setTimeout(function() {
            messageDiv.remove();
        }, 300); // Wait for fade out animation to complete
    }, 2000);
}

// Function to show a custom confirmation modal
function showConfirm(message) { 
    return new Promise((resolve) => {
        // If the confirm modal doesn't exist, create it
        if ($('#confirmModal').length === 0) {
            const modalHtml = `
                <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmModalLabel">Please Confirm</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${message}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="confirmYes">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);
        } else {
            // Update message if modal already exists
            $('#confirmModal .modal-body').html(message);
        }
        
        // Create a new Bootstrap modal instance
        const confirmModalEl = document.getElementById('confirmModal');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        let confirmed = false;
        
        // Set up click event for the "Yes" button
        $('#confirmYes').off('click').on('click', () => {
            confirmed = true;
            confirmModal.hide();
        });
        
        // Once the modal is hidden, resolve the Promise
        $(confirmModalEl).off('hidden.bs.modal').on('hidden.bs.modal', () => {
            resolve(confirmed);
        });
        
        // Show the modal
        confirmModal.show();
    });
}
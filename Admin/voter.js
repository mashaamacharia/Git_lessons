// Voter Management AJAX Functions
const voterManager = {
    init: function() {
        this.bindEvents();
        this.loadVoters();
        this.updateTotalVoters();
        
        // Refresh total voters every 30 seconds
        setInterval(this.updateTotalVoters.bind(this), 30000);
    },

    bindEvents: function() {
        // Add voter form submission
        $('#addVoterForm').on('submit', this.handleAddVoter.bind(this));
        
        // Search and filter events
        $('#searchInput').on('keyup', this.debounce(this.loadVoters.bind(this), 500));
        $('#facultyFilter, #statusFilter').on('change', this.loadVoters.bind(this));
        
        // Action buttons
        $('.table-responsive').on('click', '.fa-edit', this.handleEditVoter.bind(this));
        $('.table-responsive').on('click', '.fa-trash-alt', this.handleDeleteVoter.bind(this));
        $('.table-responsive').on('click', '.fa-ban', this.handleToggleStatus.bind(this));

        // Reset form on modal close
        $('#addVoterModal').on('hidden.bs.modal', function () {
            $('#addVoterForm').trigger('reset');
            $('#addVoterForm').data('mode', 'add');
            $('#addVoterForm').find('button[type="submit"]').text('Add Voter');
        });
    },

    updateTotalVoters: function() {
        $.ajax({
            url: 'voter-process.php',
            type: 'POST',
            data: { action: 'get_total' },
            success: (response) => {
                const result = JSON.parse(response);
                $('.stat-card .h2').text(result.total.toLocaleString());
            }
        });
    },

    handleAddVoter: function(e) {
        e.preventDefault();
        const form = $(e.target);
        const formData = new FormData(e.target);
        const mode = form.data('mode') || 'add';
        formData.append('action', mode);

        // Show loading spinner
        this.showCenterOverlay('loading');

        $.ajax({
            url: 'voter-process.php',
            type: 'POST',
            data: Object.fromEntries(formData),
            success: (response) => {
                const result = JSON.parse(response);
                if (result.success) {
                    this.showCenterOverlay('success', result.message);
                    $('#addVoterModal').modal('hide');
                    form.trigger('reset');
                    this.loadVoters();
                    this.updateTotalVoters();
                } else {
                    this.showCenterOverlay('error', result.message);
                }
            },
            error: () => {
                this.showCenterOverlay('error', 'An error occurred while processing the voter');
            }
        });
    },

    handleEditVoter: function(e) {
        const tr = $(e.target).closest('tr');
        // Adjusted indexes: registration is now in td:eq(1)
        const registration = tr.find('td:eq(1)').text();
        const name = tr.find('td:eq(2)').text();
        const faculty = tr.find('td:eq(3)').text();
        const idNo = tr.find('td:eq(4)').text();
    
        const form = $('#addVoterForm');
        form.data('mode', 'update');
        form.find('button[type="submit"]').text('Update Voter');
        
        // Populate the form
        form.find('input[name="registration"]').val(registration);
        form.find('input[name="name"]').val(name);
        form.find('select[name="faculty"]').val(faculty);
        form.find('input[name="idno"]').val(idNo);
        
        // Get additional data via AJAX if needed
        $.ajax({
            url: 'voter-process.php',
            type: 'POST',
            data: {
                action: 'get_voter_details',
                registration: registration
            },
            success: (response) => {
                const result = JSON.parse(response);
                if (result.success) {
                    form.find('input[name="hostelid"]').val(result.voter.HostelID);
                    form.find('input[name="phone"]').val(result.voter.phone_number);
                }
                $('#addVoterModal').modal('show');
            }
        });
    },
    
    loadVoters: function() {
        const filters = {
            action: 'get',
            search: $('#searchInput').val(),
            faculty: $('#facultyFilter').val(),
            status: $('#statusFilter').val()
        };

        $.ajax({
            url: 'voter-process.php',
            type: 'POST',
            data: filters,
            success: (response) => {
                const voters = JSON.parse(response);
                this.updateVotersTable(voters);
            },
            error: () => {
                this.showCenterOverlay('error', 'Failed to load voters');
            }
        });
    },

    handleDeleteVoter: function(e) {
        const registration = $(e.target).closest('tr').find('td:first').text();
        
        // Use custom Bootstrap confirmation modal
        this.showConfirm('Are you sure you want to delete this voter? This action cannot be undone.')
            .then((confirmed) => {
                if (!confirmed) return;
                
                // Show spinner while processing delete
                this.showCenterOverlay('loading');

                $.ajax({
                    url: 'voter-process.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        registration: registration
                    },
                    success: (response) => {
                        const result = JSON.parse(response);
                        if (result.success) {
                            this.showCenterOverlay('success', result.message);
                            this.loadVoters();
                            this.updateTotalVoters();
                        } else {
                            this.showCenterOverlay('error', result.message);
                        }
                    },
                    error: () => {
                        this.showCenterOverlay('error', 'Failed to delete voter');
                    }
                });
            });
    },

    handleToggleStatus: function(e) {
        const tr = $(e.target).closest('tr');
        const registration = tr.find('td:first').text();
        const currentStatus = tr.find('.voter-status').hasClass('active') ? 'active' : 'inactive';
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const confirmMessage = `Are you sure you want to ${newStatus === 'inactive' ? 'deactivate' : 'activate'} this voter?`;

        // Use custom Bootstrap confirmation modal
        this.showConfirm(confirmMessage)
            .then((confirmed) => {
                if (!confirmed) return;
                
                // Show spinner while processing status update
                this.showCenterOverlay('loading');

                $.ajax({
                    url: 'voter-process.php',
                    type: 'POST',
                    data: {
                        action: 'update_status',
                        registration: registration,
                        status: newStatus
                    },
                    success: (response) => {
                        const result = JSON.parse(response);
                        if (result.success) {
                            this.showCenterOverlay('success', result.message);
                            this.loadVoters();
                        } else {
                            this.showCenterOverlay('error', result.message);
                        }
                    },
                    error: () => {
                        this.showCenterOverlay('error', 'Failed to update status');
                    }
                });
            });
    },

    updateVotersTable: function(voters) {
        const tbody = $('.custom-table tbody');
        tbody.empty();

        if (voters.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="text-center">No voters found</td>
                </tr>
            `);
            return;
        }

        voters.forEach(voter => {
            tbody.append(`
                <tr>
                    <td>${voter.Registration}</td>
                    <td>${voter.Name}</td>
                    <td>${voter.Faculty}</td>
                    <td>${voter.IDNo}</td>
                    <td>${voter.HostelID}</td>
                    <td>${voter.phone_number}</td>
                    <td><span class="voter-status ${voter.status.toLowerCase()}">${voter.status}</span></td>
                    <td class="action-icons">
                        <i class="fas fa-edit text-primary" title="Edit"></i>
                        <i class="fas fa-trash-alt text-danger" title="Delete"></i>
                        <i class="fas fa-ban text-warning" title="Toggle Status"></i>
                    </td>
                </tr>
            `);
        });
    },

    /**
     * Creates or reuses a centered overlay for loading or displaying messages.
     * The `type` parameter can be:
     * - 'loading' (shows a spinner)
     * - 'success' (shows a success message)
     * - 'error'   (shows an error message)
     * 
     * When showing a success or error message, the overlay auto-dismisses after 3 seconds.
     */
/**
 * Creates or reuses a centered overlay for loading or displaying messages.
 * The `type` parameter can be:
 * - 'loading' (shows a spinner)
 * - 'success' (shows a success message)
 * - 'error'   (shows an error message)
 *
 * When showing a success or error message, the overlay auto-dismisses after 3 seconds.
 */
showCenterOverlay: function(type, message = '') {
    // Create overlay if it doesn't exist
    if ($('#centerOverlay').length === 0) {
        $('body').append(`
            <div id="centerOverlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1050;
                display: none;
            ">
                <div id="centerOverlayContent" style="
                    background: transparent;
                    border: none;
                    padding: 20px;
                    border-radius: 8px;
                    text-align: center;
                    min-width: 250px;
                ">
                </div>
            </div>
        `);
    }
    
    // Prepare content based on type
    let contentHtml = '';
    if (type === 'loading') {
        contentHtml = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div style="margin-top: 10px; color: #fff;">Loading...</div>
        `;
    } else if (type === 'success') {
        // Bootstrap/Django-like success message
        contentHtml = `<div class="alert alert-success" role="alert" style="margin: 0;">${message}</div>`;
    } else if (type === 'error') {
        // Bootstrap/Django-like error message
        contentHtml = `<div class="alert alert-danger" role="alert" style="margin: 0;">${message}</div>`;
    }
    
    $('#centerOverlayContent').html(contentHtml);
    $('#centerOverlay').fadeIn(200);
    
    // Auto-dismiss overlay for success and error messages after 3 seconds
    if (type === 'success' || type === 'error') {
        setTimeout(() => {
            $('#centerOverlay').fadeOut(200);
        }, 2000);
    }
},


    // Hide the overlay immediately (useful when you want to cancel the spinner)
    hideCenterOverlay: function() {
        $('#centerOverlay').fadeOut(200);
    },

    /**
     * Displays a Bootstrap confirmation modal and returns a Promise
     * that resolves to true if confirmed or false if cancelled.
     */
    showConfirm: function(message) {
        return new Promise((resolve) => {
            // If the confirm modal doesn't exist, create it.
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
    },

    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
};

// Initialize voter management when document is ready
$(document).ready(() => {
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is required for the voter management system');
        return;
    }
    voterManager.init();
});

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
        // Handle candidate form submission.
        $('#addCandidateForm').on('submit', this.handleAddCandidate.bind(this));

        // Search and filter events.
        $('#searchInput').on('keyup', this.debounce(this.loadCandidates.bind(this), 500));
        $('#facultyFilter, #positionFilter').on('change', this.loadCandidates.bind(this));

        // Delegate edit and delete events on table rows.
        $('.table-container').on('click', '.fa-edit', this.handleEditCandidate.bind(this));
        $('.table-container').on('click', '.fa-trash-alt', this.handleDeleteCandidate.bind(this));
    },

    handleAddCandidate: function (e) {
        e.preventDefault();
        const form = $(e.target);
        let mode = form.data('mode') || 'add';
        let formData = {
            action: mode,
            fullname: form.find('input[name="fullname"]').val().trim(),
            position: form.find('#position').val(),
            faculty: form.find('#faculty').val(), // Optional; if empty, processed as NULL in PHP.
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
                const result = JSON.parse(response);
                if (result.success) {
                    this.showCenterOverlay('success', result.message);
                    $('#addCandidateModal').modal('hide');
                    form.trigger('reset');
                    // Reset form mode.
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
        // The candidate ID is stored as a data attribute on the row.
        const candidateId = row.data('id');
        // Retrieve candidate details from the row.
        const fullname = row.find('td').eq(0).find('.fw-bold').text();
        const email = row.find('td').eq(0).find('small').text(); // Email displayed in the row.
        const position = row.find('td').eq(1).text();
        const faculty = row.find('td').eq(2).text();

        const form = $('#addCandidateForm');
        form.data('mode', 'update');
        form.data('candidateId', candidateId);
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

        if (confirm('Are you sure you want to delete this candidate?')) {
            this.showCenterOverlay('loading');

            $.ajax({
                url: 'candidate-process.php',
                type: 'POST',
                data: { action: 'delete', id: candidateId },
                success: (response) => {
                    const result = JSON.parse(response);
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

    updateTotalCandidates: function () {
        $.ajax({
            url: 'candidate-process.php',
            type: 'POST',
            data: { action: 'get_total' },
            success: (response) => {
                const result = JSON.parse(response);
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
                const candidates = JSON.parse(response);
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
                    <td><span class="status-badge active">Active</span></td>
                    <td class="action-icons">
                        <i class="fas fa-edit text-primary" title="Edit"></i>
                        <i class="fas fa-trash-alt text-danger" title="Delete"></i>
                        <i class="fas fa-eye text-info" title="View"></i>
                    </td>
                </tr>
            `);
        });
    },

    /**
     * Displays a centered overlay with a spinner or a Django-like Bootstrap alert.
     * @param {string} type - 'loading', 'success', or 'error'
     * @param {string} message - The message to display (for success/error)
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

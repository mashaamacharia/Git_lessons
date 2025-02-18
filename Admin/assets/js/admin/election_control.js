$(document).ready(function() {
    // Create toast container if it doesn't exist
    if ($('.toast-container').length === 0) {
        $('body').append('<div class="toast-container"></div>');
    }

    // Initialize and load initial data
    loadElectionData();
    initializeCountdown();

    // Event listeners for date inputs to update election settings
    $('#electionStart, #electionEnd').change(function() {
        updateElectionSettings();
    });

    // Function to load election data from the server
    function loadElectionData() {
        $.ajax({
            url: '../../models/admin/election_process.php',
            type: 'GET',
            data: { action: 'get_status' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.status-card:nth-child(1) h3').text(response.data.total_voters);
                    $('.status-card:nth-child(3) h3').text(response.data.turnout + '%');
                    
                    if (response.data.start_time) $('#electionStart').val(response.data.start_time);
                    if (response.data.end_time) $('#electionEnd').val(response.data.end_time);
                    
                    updateCountdownAndStatus();
                }
            },
            error: function() {
                showMessage('error', 'Failed to load election data');
            }
        });
    }

    // Function to update election settings
    function updateElectionSettings() {
        let startDate = $('#electionStart').val();
        let endDate = $('#electionEnd').val();

        if (!startDate || !endDate) {
            showMessage('error', 'Please set both start and end times');
            return;
        }

        if (new Date(startDate) >= new Date(endDate)) {
            showMessage('error', 'End time must be after start time');
            return;
        }

        $.ajax({
            url: '../../models/admin/election_process.php',
            type: 'POST',
            data: {
                action: 'update_settings',
                start_time: startDate,
                end_time: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('success', 'Election settings updated successfully');
                    loadElectionData();
                } else {
                    showMessage('error', response.message);
                }
            },
            error: function() {
                showMessage('error', 'Failed to update election settings');
            }
        });
    }

    // Function to show toast messages
    function showMessage(type, message) {
        // Create new toast message
        const toast = $('<div>', {
            class: `toast-message toast-${type}`,
            text: message
        });
        
        // Add to container
        $('.toast-container').append(toast);
        
        // Trigger reflow and show toast
        toast[0].offsetHeight;
        toast.addClass('show');
        
        // Auto-dismiss after 2 seconds
        setTimeout(function() {
            toast.removeClass('show');
            setTimeout(function() {
                toast.remove();
            }, 300); // Wait for animation to complete
        }, 2000);
    }

    // The rest of your functions remain unchanged
    function updateCountdownAndStatus() {
        let startDate = $('#electionStart').val();
        let endDate = $('#electionEnd').val();
        let currentTime = new Date();
        let targetTime = null;
        let label = "";
        let computedStatus = "Not Started";

        if (startDate && endDate) {
            let startTime = new Date(startDate);
            let endTime = new Date(endDate);

            if (currentTime < startTime) {
                computedStatus = "Not Started";
                targetTime = startTime;
                label = "Starts in: ";
            } else if (currentTime >= startTime && currentTime < endTime) {
                computedStatus = "Ongoing";
                targetTime = endTime;
                label = "Ends in: ";
            } else if (currentTime >= endTime) {
                computedStatus = "Ended";
                targetTime = null;
                label = "Election Ended";
            }
        }

        let countdownStr = "";
        if (targetTime) {
            let diff = targetTime - currentTime;
            if (diff < 0) diff = 0;
            let seconds = Math.floor(diff / 1000) % 60;
            let minutes = Math.floor(diff / (1000 * 60)) % 60;
            let hours = Math.floor(diff / (1000 * 60 * 60)) % 24;
            let days = Math.floor(diff / (1000 * 60 * 60 * 24));
            countdownStr = days + "d " + hours + "h " + minutes + "m " + seconds + "s";
        } else {
            countdownStr = "00d 00h 00m 00s";
        }

        $('#countdown').text(label + countdownStr);
        $('.status-card:nth-child(2) h3').text(countdownStr);

        $('#electionStatus')
            .removeClass()
            .addClass('election-status')
            .addClass('status-' + computedStatus.toLowerCase().replace(' ', '-'))
            .text(computedStatus);
    }

    function initializeCountdown() {
        setInterval(updateCountdownAndStatus, 1000);
    }
});
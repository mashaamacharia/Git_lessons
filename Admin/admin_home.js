// Admin home dashboard JavaScript
$(document).ready(function() {
    // Toggle sidebar
    $('#toggle-btn').click(function() {
        $('#sidebar').toggleClass('collapsed');
        $('#content').toggleClass('expanded');
        $('#overlay').toggleClass('active');
    });

    // Close sidebar when clicking overlay
    $('#overlay').click(function() {
        $('#sidebar').addClass('collapsed');
        $('#content').addClass('expanded');
        $('#overlay').removeClass('active');
    });

    // Function to fetch dashboard statistics
    function fetchDashboardStats() {
        $.ajax({
            url: 'adminhome_process.php',
            type: 'GET',
            data: { action: 'fetch_stats' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update statistics cards
                    updateStatsCards(response.data);
                    // Update activity feed
                    updateActivityFeed(response.activities);
                } else {
                    console.error('Server error:', response.message);
                    showMessage('error', response.message || 'Failed to fetch dashboard statistics');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                showMessage('error', 'Error connecting to server: ' + error);
            }
        });
    }

    // Function to update statistics cards
    function updateStatsCards(stats) {
        if (!stats) return;

        // Update total candidates
        $('.stats-card:eq(0) .stats-info').html(`
            <h4>Total Candidates</h4>
            <h2>${stats.total_candidates || 0}</h2>
        `);

        // Update registered voters
        $('.stats-card:eq(1) .stats-info').html(`
            <h4>Registered Voters</h4>
            <h2>${stats.registered_voters || 0}</h2>
        `);

        // Update votes cast
        $('.stats-card:eq(2) .stats-info').html(`
            <h4>Already Voted</h4>
            <h2>${stats.votes_cast || 0}</h2>
        `);

        // Update voter turnout
        $('.stats-card:eq(3) .stats-info').html(`
            <h4>Voter Turnout</h4>
            <h2>${stats.voter_turnout || 0}%</h2>
        `);
    }

    // Function to update activity feed
    function updateActivityFeed(activities) {
        if (!activities || activities.length === 0) {
            $('.activity-feed').html(`
                <h4 class="mb-4">Recent Activity</h4>
                <p>No recent activities</p>
            `);
            return;
        }

        const activityHtml = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ${activity.icon}"></i>
                </div>
                <div class="activity-details">
                    <p>${activity.message}</p>
                    <small>${activity.time}</small>
                </div>
            </div>
        `).join('');

        $('.activity-feed').html(`
            <h4 class="mb-4">Recent Activity</h4>
            ${activityHtml}
        `);
    }

    // Function to show messages
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
            }, 300);
        }, 2000);
    }

    // Initial fetch of dashboard stats
    fetchDashboardStats();

    // Refresh dashboard stats every 30 seconds
    setInterval(fetchDashboardStats, 30000);
});
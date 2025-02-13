document.addEventListener('DOMContentLoaded', function() {
    // Load announcements on page load
    loadAnnouncements();
    
    // Handle form submission
    const form = document.getElementById('addAnnouncementForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('process_announcements.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAnnouncementModal'));
                modal.hide();
                
                // Reload announcements
                loadAnnouncements();
                
                // Show success message
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function loadAnnouncements() {
    fetch('process_announcements.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const announcementsList = document.querySelector('.announcements-list');
                announcementsList.innerHTML = ''; // Clear existing announcements
                
                data.data.forEach(announcement => {
                    announcementsList.innerHTML += createAnnouncementCard(announcement);
                });
            }
        })
        .catch(error => console.error('Error:', error));
}

function createAnnouncementCard(announcement) {
    return `
        <div class="announcement-card" data-id="${announcement.id}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="announcement-title">${announcement.title}</h4>
                    <div class="announcement-meta">
                        <i class="fas fa-calendar-alt me-2"></i>Posted on ${new Date(announcement.created_at).toLocaleDateString()}
                        <span class="ms-3">
                            <i class="fas fa-user me-2"></i>by ${announcement.created_by_name}
                        </span>
                    </div>
                </div>
                <span class="status-badge ${announcement.status}">${announcement.status}</span>
            </div>
            <p class="mb-3">${announcement.content}</p>
            <div class="action-buttons">
                <button class="btn btn-sm btn-outline-primary me-2" onclick="editAnnouncement(${announcement.id})">
                    <i class="fas fa-edit me-1"></i>Edit
                </button>
                <button class="btn btn-sm btn-outline-danger me-2" onclick="deleteAnnouncement(${announcement.id})">
                    <i class="fas fa-trash-alt me-1"></i>Delete
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="viewAnnouncement(${announcement.id})">
                    <i class="fas fa-eye me-1"></i>View
                </button>
            </div>
        </div>
    `;
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch('process_announcements.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                loadAnnouncements();
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
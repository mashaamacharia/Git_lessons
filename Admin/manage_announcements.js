$(document).ready(function(){
  // Load announcements on page load
  loadAnnouncements();

  // Toggle filter options visibility
  $('#filterButton').click(function(){
      $('#filterOptions').slideToggle();
  });

  // Attach event listeners for filtering:
  // Trigger loadAnnouncements when the search input changes.
  $('#searchAnnouncements').on('keyup', loadAnnouncements);
  // Trigger loadAnnouncements when the status or priority dropdown changes.
  $('#statusFilter, #priorityFilter').on('change', loadAnnouncements);
  // If you have an "Apply Filters" button, trigger on click.
  $('#applyFilters').on('click', function(e){
      e.preventDefault();
      loadAnnouncements();
  });

  // Form submission for create/update
  $('#addAnnouncementForm').submit(function(e){
      e.preventDefault();
      let formData = new FormData(this);
      let actionVal = $('#announcement_id').val() ? 'update' : 'create';
      formData.set('action', actionVal);

      $.ajax({
          url: 'process_announcements.php',
          type: 'POST',
          data: formData,
          dataType: 'json',
          contentType: false,
          processData: false,
          success: function(response){
              if(response.success){
                  showMessage('success', response.message);
                  $('#addAnnouncementModal').modal('hide');
                  $('#addAnnouncementForm')[0].reset();
                  $('#announcement_id').val('');
                  $('#existingAttachments').html('');
                  loadAnnouncements();
              } else {
                  showMessage('error', response.message);
              }
          },
          error: function(){
              showMessage('error', 'An error occurred.');
          }
      });
  });

  // Click event for editing an announcement
  $(document).on('click', '.editAnnouncement', function(){
      let id = $(this).data('id');
      $.ajax({
          url: 'process_announcements.php',
          type: 'GET',
          data: { action: 'get', announcement_id: id },
          dataType: 'json',
          success: function(response){
              if(response.success){
                  let announcement = response.data;
                  $('#announcement_id').val(announcement.id);
                  $('#addAnnouncementForm [name="title"]').val(announcement.title);
                  $('#addAnnouncementForm [name="content"]').val(announcement.content);
                  $('#addAnnouncementForm [name="status"]').val(announcement.status);
                  $('#addAnnouncementForm [name="priority"]').val(announcement.priority);
                  
                  let attachmentsHTML = '';
                  if(announcement.attachments && announcement.attachments.length){
                      attachmentsHTML = '<p>Existing Attachments:</p><ul>';
                      announcement.attachments.forEach(function(file){
                          attachmentsHTML += `<li><a href="uploads/announcements/${file}" target="_blank">${file}</a></li>`;
                      });
                      attachmentsHTML += '</ul>';
                  }
                  $('#existingAttachments').html(attachmentsHTML);
                  $('#modalTitle').text('Edit Announcement');
                  $('#addAnnouncementModal').modal('show');
              } else {
                  showMessage('error', response.message);
              }
          },
          error: function(){
              showMessage('error', 'An error occurred while fetching announcement details.');
          }
      });
  });

  // Click event for deleting an announcement
  $(document).on('click', '.deleteAnnouncement', function(){
      let id = $(this).data('id');
      showConfirm('Are you sure you want to delete this announcement?').then(function(confirmed) {
          if(confirmed){
              $.ajax({
                  url: 'process_announcements.php',
                  type: 'POST',
                  data: { action: 'delete', announcement_id: id },
                  dataType: 'json',
                  success: function(response){
                      if(response.success){
                          showMessage('success', response.message);
                          loadAnnouncements();
                      } else {
                          showMessage('error', response.message);
                      }
                  },
                  error: function(){
                      showMessage('error', 'An error occurred while deleting announcement.');
                  }
              });
          }
      });
  });

  // Function to load announcements list and update stats cards
  function loadAnnouncements(){
      let search = $('#searchAnnouncements').val();
      let status = $('#statusFilter').val();
      let priority = $('#priorityFilter').val();

      $.ajax({
          url: 'process_announcements.php',
          type: 'GET',
          data: { action: 'fetch', search: search, status: status, priority: priority },
          dataType: 'json',
          success: function(response){
              if(response.success){
                  // Update stats cards
                  $('#totalAnnouncementsCard p').text(response.stats.total);
                  $('#activeAnnouncementsCard p').text(response.stats.active);
                  $('#draftAnnouncementsCard p').text(response.stats.draft);

                  let html = '';
                  if(response.data.length > 0){
                      response.data.forEach(function(announcement){
                          let statusClass = announcement.status.toLowerCase() === 'active' ? 'status-active' : 'status-draft';
                          let cardClass = announcement.status.toLowerCase() === 'active' ? 'active' : 'draft';
                          
                          html += `
                              <div class="announcement-item card mb-3 ${cardClass}">
                                  <div class="card-body">
                                      <h5 class="card-title">${announcement.title}</h5>
                                      <p class="card-text">${announcement.content.substring(0,150)}...</p>
                                      <p class="card-text">
                                          <span class="status-badge ${statusClass}">${announcement.status}</span>
                                          <small class="text-muted ml-2">Priority: ${announcement.priority}</small>
                                      </p>
                                      <div class="announcement-actions">
                                          <i class="fas fa-edit text-primary editAnnouncement" title="Edit" data-id="${announcement.id}" style="cursor:pointer; margin-right:10px;"></i>
                                          <i class="fas fa-trash-alt text-danger deleteAnnouncement" title="Delete" data-id="${announcement.id}" style="cursor:pointer;"></i>
                                      </div>
                                  </div>
                              </div>
                          `;
                      });
                  } else {
                      html = '<p>No announcements found.</p>';
                  }
                  $('.announcements-list').html(html);
              } else {
                  showMessage('error', response.message);
              }
          },
          error: function(){
              showMessage('error', 'Error loading announcements.');
          }
      });
  }

  // Function to display message overlay
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

  // Function to show confirmation dialog
  function showConfirm(message) { 
      return new Promise((resolve) => {
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
              $('#confirmModal .modal-body').html(message);
          }
          
          const confirmModalEl = document.getElementById('confirmModal');
          const confirmModal = new bootstrap.Modal(confirmModalEl);
          let confirmed = false;
          
          $('#confirmYes').off('click').on('click', () => {
              confirmed = true;
              confirmModal.hide();
          });
          
          $(confirmModalEl).off('hidden.bs.modal').on('hidden.bs.modal', () => {
              resolve(confirmed);
          });
          
          confirmModal.show();
      });
  }
});

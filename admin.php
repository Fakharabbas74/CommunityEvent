<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Only allow access if logged in
redirectIfNotLoggedIn();

// Render admin event management page
require_once 'includes/adminHeader.php';
?>

<h2>Events Management</h2>

<div id="message" class="alert" style="display:none;"></div>

<!-- Add Event Button -->
<button id="add-event-btn" class="btn btn-save" style="margin-bottom: 20px;">
    <i class="fas fa-plus"></i> Add New Event
</button>

<!-- Events Table -->
<div class="table-responsive">
    <table class="events-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="events-table-body">
            <!-- Events will be loaded here via AJAX -->
        </tbody>
    </table>
</div>

<!-- Loading Indicator -->
<div id="loading-events" class="text-center" style="padding: 20px;">
    <i class="fas fa-spinner fa-spin"></i> Loading events...
</div>

<!-- Add/Edit Event Modal -->
<div id="event-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 id="modal-title">Add New Event</h3>
        <div id="modal-message" class="alert" style="display:none;"></div>
        <form id="event-form" enctype="multipart/form-data">
            <input type="hidden" id="event-id" name="event_id" value="">
            
            <div class="form-group">
                <label for="modal-title-input">Title*</label>
                <input type="text" id="modal-title-input" name="title" required>
            </div>

            <div class="form-group">
                <label for="modal-description">Description*</label>
                <textarea id="modal-description" name="description" rows="5" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="modal-event-date">Date*</label>
                    <input type="date" id="modal-event-date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="modal-event-time">Time*</label>
                    <input type="time" id="modal-event-time" name="event_time" required>
                </div>
            </div>

            <div class="form-group">
                <label for="modal-location">Location*</label>
                <input type="text" id="modal-location" name="location" required>
            </div>

            <div class="form-group">
                <label for="modal-category">Category*</label>
                <select id="modal-category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Exhibition">Exhibition</option>
                    <option value="Fitness">Fitness</option>
                    <option value="Conference">Conference</option>
                    <option value="Social">Social</option>
                </select>
            </div>

            <div class="form-group">
                <label for="modal-image">Event Image</label>
                <input type="file" id="modal-image" name="image" accept="image/*">
                <div id="current-image-container" style="margin-top: 10px; display:none;">
                    <p>Current Image:</p>
                    <img id="current-image" src="" style="max-width: 200px;">
                    <label>
                        <input type="checkbox" name="remove_image"> Remove current image
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-save">Save</button>
            <button type="button" class="btn btn-cancel close-modal">Cancel</button>
        </form>
    </div>
</div>

<script>
// Global variables
let eventsData = [];

// Initialize the page
$(document).ready(function() {
    loadEvents();
    setupEventListeners();
    setupModal();
});

function loadEvents() {
    $('#loading-events').show();
    $('#events-table-body').empty();
    
    $.ajax({
        url: 'ajax/get-events.php',
        method: 'GET',
        data: { admin: true }, // Get all events for admin
        dataType: 'json',
        success: function(data) {
            if (data.success && data.events.length > 0) {
                eventsData = data.events;
                renderEventsTable(data.events);
            } else {
                $('#events-table-body').html('<tr><td colspan="7">No events found</td></tr>');
            }
        },
        error: function(xhr) {
            showMessage('Error loading events: ' + xhr.responseText, 'error');
        },
        complete: function() {
            $('#loading-events').hide();
        }
    });
}

function renderEventsTable(events) {
    const tableBody = $('#events-table-body');
    tableBody.empty();
    
    events.forEach(event => {
        const eventDate = new Date(event.event_date + 'T' + event.event_time);
        const formattedDate = eventDate.toLocaleDateString();
        const formattedTime = eventDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        tableBody.append(`
            <tr data-event-id="${event.id}">
                <td>${event.id}</td>
                <td>${event.title}</td>
                <td>${formattedDate}</td>
                <td>${formattedTime}</td>
                <td>${event.location}</td>
                <td>${event.category}</td>
                <td class="actions">
                    <button class="btn btn-edit" onclick="openEditModal(${event.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-delete" onclick="confirmDelete(${event.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `);
    });
}

function setupEventListeners() {
    // BroadcastChannel for real-time updates
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('events');
        channel.onmessage = function(e) {
            if (e.data.type === 'new-event') {
                addEventToTable(e.data.event);
            } else if (e.data.type === 'event-updated') {
                updateEventInTable(e.data.event);
            } else if (e.data.type === 'event-deleted') {
                removeEventFromTable(e.data.eventId);
            }
        };
    }
    
    // localStorage fallback
    window.addEventListener('storage', function(e) {
        if (e.key === 'newEventAdded' && e.newValue) {
            addEventToTable(JSON.parse(e.newValue));
        } else if (e.key === 'eventUpdated' && e.newValue) {
            updateEventInTable(JSON.parse(e.newValue));
        } else if (e.key === 'eventDeleted' && e.newValue) {
            removeEventFromTable(e.newValue);
        }
    });
}

function setupModal() {
    const modal = $('#event-modal');
    
    // Open modal for adding new event
    $('#add-event-btn').click(function() {
        $('#modal-title').text('Add New Event');
        $('#event-form')[0].reset();
        $('#event-id').val('');
        $('#current-image-container').hide();
        modal.show();
    });
    
    // Close modal
    $('.close-modal').click(function() {
        modal.hide();
    });
    
    // Handle form submission
    $('#event-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const eventId = $('#event-id').val();
        
        $('#modal-message').hide();
        
        const url = eventId ? 'ajax/update-event.php' : 'ajax/add-event.php';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    showMessage(data.message, 'success');
                    modal.hide();
                    
                    // Broadcast the change
                    if (eventId) {
                        broadcastEventUpdate(data.event);
                    } else {
                        broadcastNewEvent(data.event);
                    }
                } else {
                    showModalMessage(data.message || 'Operation failed', 'error');
                }
            },
            error: function(xhr) {
                let error = 'An error occurred';
                try {
                    const data = JSON.parse(xhr.responseText);
                    error = data.message || error;
                } catch (e) {
                    console.error('Error parsing response:', xhr.responseText);
                }
                showModalMessage(error, 'error');
            }
        });
    });
}

function openEditModal(eventId) {
    const event = eventsData.find(e => e.id == eventId);
    if (!event) return;
    
    $('#modal-title').text('Edit Event');
    $('#event-id').val(event.id);
    $('#modal-title-input').val(event.title);
    $('#modal-description').val(event.description);
    $('#modal-event-date').val(event.event_date);
    $('#modal-event-time').val(event.event_time);
    $('#modal-location').val(event.location);
    $('#modal-category').val(event.category);
    
    if (event.image_path) {
        $('#current-image').attr('src', 'assets/images/' + event.image_path);
        $('#current-image-container').show();
    } else {
        $('#current-image-container').hide();
    }
    
    $('#event-modal').show();
}

function confirmDelete(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    $.ajax({
        url: 'ajax/delete-event.php?id=' + eventId,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                showMessage('Event deleted successfully', 'success');
                broadcastEventDeletion(eventId);
            } else {
                showMessage(data.message || 'Failed to delete event', 'error');
            }
        },
        error: function(xhr) {
            showMessage('Error deleting event: ' + xhr.responseText, 'error');
        }
    });
}

// Real-time update functions
function addEventToTable(event) {
    eventsData.unshift(event); // Add to beginning of array
    renderEventsTable(eventsData);
}

function updateEventInTable(updatedEvent) {
    const index = eventsData.findIndex(e => e.id == updatedEvent.id);
    if (index !== -1) {
        eventsData[index] = updatedEvent;
        renderEventsTable(eventsData);
    }
}

function removeEventFromTable(eventId) {
    eventsData = eventsData.filter(e => e.id != eventId);
    renderEventsTable(eventsData);
}

// Broadcast functions
function broadcastNewEvent(event) {
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('events');
        channel.postMessage({ type: 'new-event', event: event });
        setTimeout(() => channel.close(), 100);
    }
    localStorage.setItem('newEventAdded', JSON.stringify(event));
    setTimeout(() => localStorage.removeItem('newEventAdded'), 100);
}

function broadcastEventUpdate(event) {
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('events');
        channel.postMessage({ type: 'event-updated', event: event });
        setTimeout(() => channel.close(), 100);
    }
    localStorage.setItem('eventUpdated', JSON.stringify(event));
    setTimeout(() => localStorage.removeItem('eventUpdated'), 100);
}

function broadcastEventDeletion(eventId) {
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('events');
        channel.postMessage({ type: 'event-deleted', eventId: eventId });
        setTimeout(() => channel.close(), 100);
    }
    localStorage.setItem('eventDeleted', eventId);
    setTimeout(() => localStorage.removeItem('eventDeleted'), 100);
}

// Helper functions
function showMessage(message, type) {
    $('#message').removeClass('alert-success alert-danger')
        .addClass(`alert-${type}`)
        .text(message)
        .show()
        .delay(5000)
        .fadeOut();
}

function showModalMessage(message, type) {
    $('#modal-message').removeClass('alert-success alert-danger')
        .addClass(`alert-${type}`)
        .text(message)
        .show();
}
</script>



<?php require_once 'includes/footer.php'; ?>
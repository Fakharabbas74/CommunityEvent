<?php 
require_once 'includes/header.php';
require_once 'includes/auth.php';
?>

<h1>Community Events</h1>

<!-- Search and Filters -->
<div class="search-filter">
    <div class="form-group">
        <input type="text" id="search-input" placeholder="Search events...">
    </div>
    <div class="form-group">
        <select id="category-filter">
            <option value="">All Categories</option>
            <option value="Workshop">Workshop</option>
            <option value="Exhibition">Exhibition</option>
            <option value="Fitness">Fitness</option>
            <option value="Conference">Conference</option>
        </select>
    </div>
    <div class="form-group">
        <select id="date-filter">
            <option value="">All Dates</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="future">Future Events</option>
        </select>
    </div>
    <button id="search-btn" class="btn btn-save">Search</button>
</div>

<!-- Events Container -->
<div id="events-container" class="events-grid"></div>
<div id="no-events" class="alert" style="display:none;">No events found matching your criteria.</div>
<div id="loading-events" class="alert">Loading events...</div>

<script>
// Shared function to create event card HTML
function createEventCard(event) {
    const eventDate = new Date(`${event.event_date}T${event.event_time}`);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const formattedDate = eventDate.toLocaleDateString('en-US', options);
    const formattedTime = eventDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    
    const imagePath = event.image_path 
        ? `assets/images/${event.image_path}` 
        : 'assets/images/default-event.jpg';
    
    return `
        <div class="event-card fade-in" data-event-id="${event.id}">
            <div class="event-image">
                <img src="${imagePath}" alt="${event.title}" loading="lazy">
            </div>
            <div class="event-details">
                <h3>${event.title}</h3>
                <div class="event-meta">
                    <span class="event-date">
                        <i class="far fa-calendar-alt"></i> ${formattedDate} at ${formattedTime}
                    </span><br>
                    <span class="event-location">
                        <i class="fas fa-map-marker-alt"></i> ${event.location}
                    </span>
                </div>
                <p>${event.description.substring(0, 100)}...</p>
                <span class="event-category">${event.category}</span>
                
            </div>
        </div>
    `;
}

function editEvent(eventId) {
    window.location.href = `edit-event.php?id=${eventId}`;
}

async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    try {
        const response = await fetch(`ajax/delete-event.php?id=${eventId}`);
        const data = await response.json();
        
        if (data.success) {
            // Remove the event card from DOM
            $(`[data-event-id="${eventId}"]`).remove();
            
            // Broadcast the deletion
            if (typeof BroadcastChannel !== 'undefined') {
                const channel = new BroadcastChannel('events');
                channel.postMessage({ type: 'event-deleted', eventId: eventId });
                setTimeout(() => channel.close(), 100);
            }
            localStorage.setItem('eventDeleted', eventId);
            setTimeout(() => localStorage.removeItem('eventDeleted'), 100);
            
            // Show success message
            alert('Event deleted successfully');
        } else {
            throw new Error(data.message || 'Failed to delete event');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting event: ' + error.message);
    }
}

// Update the real-time listeners in index.php
if (typeof BroadcastChannel !== 'undefined') {
    const eventChannel = new BroadcastChannel('events');
    eventChannel.onmessage = function(e) {
        if (e.data.type === 'new-event') {
            addNewEventToDisplay(e.data.event);
        } else if (e.data.type === 'event-updated') {
            updateEventDisplay(e.data.event);
        } else if (e.data.type === 'event-deleted') {
            $(`[data-event-id="${e.data.eventId}"]`).remove();
        }
    };
}

window.addEventListener('storage', function(e) {
    if (e.key === 'newEventAdded' && e.newValue) {
        addNewEventToDisplay(JSON.parse(e.newValue));
    } else if (e.key === 'eventUpdated' && e.newValue) {
        updateEventDisplay(JSON.parse(e.newValue));
    } else if (e.key === 'eventDeleted' && e.newValue) {
        $(`[data-event-id="${e.newValue}"]`).remove();
    }
});

function updateEventDisplay(updatedEvent) {
    const existingCard = $(`[data-event-id="${updatedEvent.id}"]`);
    if (existingCard.length) {
        existingCard.replaceWith(createEventCard(updatedEvent));
    }
}


// Load events function
function loadEvents() {
    $('#loading-events').show();
    $('#no-events').hide();
    
    const searchTerm = $('#search-input').val();
    const category = $('#category-filter').val();
    const dateFilter = $('#date-filter').val();
    
    $.ajax({
        url: 'ajax/get-events.php',
        method: 'GET',
        data: {
            search: searchTerm,
            category: category,
            date: dateFilter
        },
        dataType: 'json',
        success: function(data) {
            $('#events-container').empty();
            
            if (data.events && data.events.length > 0) {
                data.events.forEach(event => {
                    $('#events-container').append(createEventCard(event));
                });
            } else {
                $('#no-events').show();
            }
        },
        error: function(xhr) {
            console.error('Error loading events:', xhr.responseText);
            $('#no-events').text('Error loading events. Please try again.').show();
        },
        complete: function() {
            $('#loading-events').hide();
        }
    });
}

// Add new event to display
function addNewEventToDisplay(event) {
    if ($(`[data-event-id="${event.id}"]`).length === 0) {
        $('#events-container').prepend(createEventCard(event));
        $('#no-events').hide();
    }
}

// Initialize the page
$(document).ready(function() {
    // Load initial events
    loadEvents();
    
    // Set up filter listeners
    $('#search-input, #category-filter, #date-filter').on('change keyup', loadEvents);
    $('#search-btn').on('click', loadEvents);
    
    // Set up real-time event listeners
    if (typeof BroadcastChannel !== 'undefined') {
        const eventChannel = new BroadcastChannel('events');
        eventChannel.onmessage = function(e) {
            if (e.data.type === 'new-event') {
                addNewEventToDisplay(e.data.event);
            }
        };
    }
    
    window.addEventListener('storage', function(e) {
        if (e.key === 'newEventAdded' && e.newValue) {
            addNewEventToDisplay(JSON.parse(e.newValue));
        }
    });
});
// Update the real-time listeners in index.php
function setupRealTimeListeners() {
    // BroadcastChannel method
    if (typeof BroadcastChannel !== 'undefined') {
        const eventChannel = new BroadcastChannel('events');
        eventChannel.onmessage = function(e) {
            if (e.data.type === 'new-event') {
                addNewEventToDisplay(e.data.event);
            } else if (e.data.type === 'event-updated') {
                updateEventDisplay(e.data.event);
            } else if (e.data.type === 'event-deleted') {
                $(`[data-event-id="${e.data.eventId}"]`).remove();
                checkEmptyEvents();
            }
        };
    }

    // localStorage fallback
    window.addEventListener('storage', function(e) {
        if (e.key === 'newEventAdded' && e.newValue) {
            addNewEventToDisplay(JSON.parse(e.newValue));
        } else if (e.key === 'eventUpdated' && e.newValue) {
            updateEventDisplay(JSON.parse(e.newValue));
        } else if (e.key === 'eventDeleted' && e.newValue) {
            $(`[data-event-id="${e.newValue}"]`).remove();
            checkEmptyEvents();
        }
    });
}

function updateEventDisplay(updatedEvent) {
    const existingCard = $(`[data-event-id="${updatedEvent.id}"]`);
    if (existingCard.length) {
        existingCard.replaceWith(createEventCard(updatedEvent));
    } else {
        // If event doesn't exist (maybe filtered out), add it if it matches current filters
        addNewEventToDisplay(updatedEvent);
    }
}

function checkEmptyEvents() {
    if ($('#events-container').children().length === 0) {
        $('#no-events').show();
    }
}

// Initialize the page
$(document).ready(function() {
    // Load initial events
    loadEvents();
    
    // Set up filter listeners
    $('#search-input, #category-filter, #date-filter').on('change keyup', loadEvents);
    $('#search-btn').on('click', loadEvents);
    
    // Set up real-time event listeners
    setupRealTimeListeners();
});
</script>

<?php require_once 'includes/footer.php'; ?>
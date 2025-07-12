<?php
// Include the header (includes HTML <head>, navigation, etc.)
require_once 'includes/header.php';

// Include authentication logic to ensure only logged-in users can access
require_once 'includes/auth.php';
?>

<!-- Main heading -->
<h2>Add New Event</h2>

<!-- Placeholder for success or error messages -->
<div id="form-message" class="alert" style="display:none;"></div>

<!-- Event submission form -->
<form id="event-form" method="POST" enctype="multipart/form-data">
    
    <!-- Event title input -->
    <div class="form-group">
        <label for="title">Title*</label>
        <input type="text" id="title" name="title" required>
    </div>

    <!-- Event description input -->
    <div class="form-group">
        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="5" required></textarea>
    </div>

    <!-- Event date and time (side by side using flexbox) -->
    <div class="form-row" style="display: flex; gap: 15px;">
        <div class="form-group" style="flex: 1;">
            <label for="event_date">Date*</label>
            <input type="date" id="event_date" name="event_date" required>
        </div>
        <div class="form-group" style="flex: 1;">
            <label for="event_time">Time*</label>
            <input type="time" id="event_time" name="event_time" required>
        </div>
    </div>

    <!-- Event location input -->
    <div class="form-group">
        <label for="location">Location*</label>
        <input type="text" id="location" name="location" required>
    </div>

    <!-- Category dropdown -->
    <div class="form-group">
        <label for="category">Category*</label>
        <select id="category" name="category" required>
            <option value="">Select a category</option>
            <option value="Workshop">Workshop</option>
            <option value="Exhibition">Exhibition</option>
            <option value="Fitness">Fitness</option>
            <option value="Conference">Conference</option>
            <option value="Social">Social</option>
        </select>
    </div>

    <!-- Event image upload -->
    <div class="form-group">
        <label for="image">Event Image</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <!-- Form action buttons -->
    <button type="submit" class="btn btn-save">Save Event</button>
    <a href="index.php" class="btn btn-cancel">Cancel</a>
</form>

<!-- JavaScript for AJAX form submission -->
<script>
$(document).ready(function() {
    // Handle form submission via AJAX
    $('#event-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        const form = e.target;
        const formData = new FormData(form); // Gather form data (including image)
        
        $('#form-message').hide(); // Hide previous message if any
        
        // Send form data to server
        $.ajax({
            url: 'ajax/add-event.php', // Backend endpoint to handle event creation
            type: 'POST',
            data: formData,
            processData: false, // Prevent jQuery from converting the data
            contentType: false, // Allow multipart/form-data for file upload
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Show success message
                    $('#form-message').removeClass('alert-danger').addClass('alert-success')
                        .text(data.message).show();
                    
                    // Reset form after successful submission
                    form.reset();
                    
                    // Real-time update using BroadcastChannel or localStorage fallback
                    if (data.event) {
                        // BroadcastChannel API
                        if (typeof BroadcastChannel !== 'undefined') {
                            const channel = new BroadcastChannel('events');
                            channel.postMessage({ type: 'new-event', event: data.event });
                            setTimeout(() => channel.close(), 100);
                        }

                        // localStorage fallback (for cross-tab sync)
                        localStorage.setItem('newEventAdded', JSON.stringify(data.event));
                        setTimeout(() => localStorage.removeItem('newEventAdded'), 100);

                        // Call frontend display function if defined
                        if (typeof addNewEventToDisplay === 'function') {
                            addNewEventToDisplay(data.event);
                        } else {
                            // Otherwise redirect to index after 2 seconds
                            setTimeout(() => window.location.href = 'index.php', 2000);
                        }
                    }
                } else {
                    throw new Error(data.message || 'Failed to add event');
                }
            },
            error: function(xhr) {
                // Handle errors from server
                let error = 'An error occurred';
                try {
                    const data = JSON.parse(xhr.responseText);
                    error = data.message || error;
                } catch (e) {
                    console.error('Error parsing response:', xhr.responseText);
                }

                // Show error message
                $('#form-message').removeClass('alert-success').addClass('alert-danger')
                    .text(error).show();
            }
        });
    });
});
</script>

<?php
// Include footer content (closing HTML tags, footer scripts, etc.)
require_once 'includes/footer.php';
?>

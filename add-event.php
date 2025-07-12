<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
?>

<h2>Add New Event</h2>

<div id="form-message" class="alert" style="display:none;"></div>

<form id="event-form" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title*</label>
        <input type="text" id="title" name="title" required>
    </div>

    <div class="form-group">
        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="5" required></textarea>
    </div>

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

    <div class="form-group">
        <label for="location">Location*</label>
        <input type="text" id="location" name="location" required>
    </div>

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

    <div class="form-group">
        <label for="image">Event Image</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn btn-save">Save Event</button>
    <a href="index.php" class="btn btn-cancel">Cancel</a>
</form>

<script>
$(document).ready(function() {
    $('#event-form').on('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        $('#form-message').hide();
        
        $.ajax({
            url: 'ajax/add-event.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#form-message').removeClass('alert-danger').addClass('alert-success')
                        .text(data.message).show();
                    
                    form.reset();
                    
                    // Broadcast the new event
                    if (data.event) {
                        if (typeof BroadcastChannel !== 'undefined') {
                            const channel = new BroadcastChannel('events');
                            channel.postMessage({ type: 'new-event', event: data.event });
                            setTimeout(() => channel.close(), 100);
                        }
                        localStorage.setItem('newEventAdded', JSON.stringify(data.event));
                        setTimeout(() => localStorage.removeItem('newEventAdded'), 100);
                        
                        // If we're on the frontend page, add the event
                        if (typeof addNewEventToDisplay === 'function') {
                            addNewEventToDisplay(data.event);
                        } else {
                            // Redirect after 2 seconds if not on frontend
                            setTimeout(() => window.location.href = 'index.php', 2000);
                        }
                    }
                } else {
                    throw new Error(data.message || 'Failed to add event');
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
                $('#form-message').removeClass('alert-success').addClass('alert-danger')
                    .text(error).show();
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
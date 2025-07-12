<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Get event ID from URL
$eventId = $_GET['id'] ?? 0;

// Fetch event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching event: " . $e->getMessage());
}
?>

<h2>Edit Event</h2>

<div id="form-message" class="alert" style="display:none;"></div>

<form id="event-form" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['id']) ?>">
    
    <div class="form-group">
        <label for="title">Title*</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($event['description']) ?></textarea>
    </div>

    <div class="form-row" style="display: flex; gap: 15px;">
        <div class="form-group" style="flex: 1;">
            <label for="event_date">Date*</label>
            <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required>
        </div>

        <div class="form-group" style="flex: 1;">
            <label for="event_time">Time*</label>
            <input type="time" id="event_time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="location">Location*</label>
        <input type="text" id="location" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>
    </div>

    <div class="form-group">
        <label for="category">Category*</label>
        <select id="category" name="category" required>
            <option value="">Select a category</option>
            <?php 
            $categories = ['Workshop', 'Exhibition', 'Fitness', 'Conference', 'Social'];
            foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $event['category'] === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Event Image</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if ($event['image_path']): ?>
            <div class="current-image">
                <p>Current Image:</p>
                <img src="assets/images/<?= htmlspecialchars($event['image_path']) ?>" style="max-width: 200px; margin-top: 10px;">
                <label>
                    <input type="checkbox" name="remove_image"> Remove current image
                </label>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-save">Update Event</button>
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
            url: 'ajax/update-event.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    $('#form-message').removeClass('alert-danger').addClass('alert-success')
                        .text(data.message).show();
                    
                    // Broadcast the updated event
                    if (data.event) {
                        // BroadcastChannel method
                        if (typeof BroadcastChannel !== 'undefined') {
                            const channel = new BroadcastChannel('events');
                            channel.postMessage({ 
                                type: 'event-updated', 
                                event: data.event 
                            });
                            setTimeout(() => channel.close(), 100);
                        }
                        
                        // localStorage fallback
                        localStorage.setItem('eventUpdated', JSON.stringify(data.event));
                        setTimeout(() => localStorage.removeItem('eventUpdated'), 100);
                        
                        // Redirect after 2 seconds
                        setTimeout(() => window.location.href = 'index.php', 2000);
                    }
                } else {
                    throw new Error(data.message || 'Failed to update event');
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
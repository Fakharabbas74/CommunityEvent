<?php require_once 'includes/auth.php'; ?>
<?php require_once 'includes/header.php'; ?>
<?php require_once '../includes/config.php'; ?>

<h2>Edit Event</h2>

<?php
$error = '';
$categories = ['Workshop', 'Exhibition', 'Fitness', 'Book Club', 'Conference', 'Social'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing event
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if (!$event) {
        die('Event not found');
    }
} catch (PDOException $e) {
    die("Error fetching event: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $image_path = $event['image_path']; // keep existing unless changed

    if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload if a new one is uploaded
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../assets/images/';
            $filename = basename($_FILES['image']['name']);
            $uniqueFilename = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $filename);
            $targetFile = $uploadDir . $uniqueFilename;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $uniqueFilename;
            } else {
                $error = 'Error uploading new image.';
            }
        }

        // Update DB
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE events 
                    SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, category = ?, image_path = ?
                    WHERE id = ?");
                $stmt->execute([$title, $description, $event_date, $event_time, $location, $category, $image_path, $id]);

                $_SESSION['success_message'] = 'Event updated successfully!';
                header('Location: ../index.php');
                exit();
            } catch (PDOException $e) {
                $error = 'Error updating event: ' . $e->getMessage();
            }
        }
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="event-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title*</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($event['description']) ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="event_date">Date*</label>
            <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required>
        </div>

        <div class="form-group">
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
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $cat == $event['category'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Replace Image (optional)</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($event['image_path'])): ?>
            <div class="current-image">
                <p>Current Image:</p>
                <img src="../assets/images/<?= htmlspecialchars($event['image_path']) ?>" alt="Event Image" width="150">
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-save">Update Event</button>
    <a href="../index.php" class="btn btn-cancel">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>

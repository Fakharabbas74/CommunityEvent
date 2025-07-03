<?php require_once 'includes/auth.php'; ?>
<?php require_once 'includes/header.php'; ?>

<h2>Add New Event</h2>

<?php
$error = '';
$categories = ['Workshop', 'Exhibition', 'Fitness', 'Book Club', 'Conference', 'Social'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../includes/config.php';

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $image_path = null;

    // Basic validation
    if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../assets/images/';
            $filename = basename($_FILES['image']['name']);
            $uniqueFilename = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $filename); // sanitize
            $targetFile = $uploadDir . $uniqueFilename;

            // Create folder if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $uniqueFilename;
            } else {
                $error = 'Error uploading image.';
            }
        }

        // Insert into DB
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, category, image_path)
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $event_time, $location, $category, $image_path]);

                $_SESSION['success_message'] = 'Event added successfully!';
                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                $error = 'Error adding event: ' . $e->getMessage();
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
        <input type="text" id="title" name="title" required>
    </div>

    <div class="form-group">
        <label for="description">Description*</label>
        <textarea id="description" name="description" rows="5" required></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="event_date">Date*</label>
            <input type="date" id="event_date" name="event_date" required>
        </div>

        <div class="form-group">
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
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="image">Event Image</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>

    <button type="submit" class="btn btn-save">Save Event</button>
    <a href="index.php" class="btn btn-cancel">Cancel</a>
</form>

<?php require_once 'includes/footer.php'; ?>

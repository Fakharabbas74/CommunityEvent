<?php
require_once '../includes/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Default response structure
$response = [
    'success' => false,
    'message' => '',
    'event' => null
];

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Required fields for updating the event
    $required = ['event_id', 'title', 'description', 'event_date', 'event_time', 'location', 'category'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Extract and sanitize POST data
    $eventId = $_POST['event_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] == 'on';

    // Fetch the current image path for the event
    $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $currentImage = $stmt->fetchColumn();
    $image_path = $currentImage;

    // If the user requested to remove the image
    if ($removeImage) {
        if ($currentImage && file_exists("../assets/images/$currentImage")) {
            unlink("../assets/images/$currentImage"); // Delete file from filesystem
        }
        $image_path = null;
    }

    // If a new image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../assets/images/';

        // Delete the old image if it exists
        if ($currentImage && file_exists("$uploadDir$currentImage")) {
            unlink("$uploadDir$currentImage");
        }

        // Validate uploaded image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not a valid image');
        }

        // Generate unique file name and target path
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $filename;

        // Move the uploaded image to the destination
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload image');
        }

        $image_path = $filename; // Store new filename
    }

    // Update the event in the database
    $stmt = $pdo->prepare("UPDATE events SET 
        title = ?, 
        description = ?, 
        event_date = ?, 
        event_time = ?, 
        location = ?, 
        category = ?, 
        image_path = ?
        WHERE id = ?");
    
    $stmt->execute([
        $title, 
        $description, 
        $event_date, 
        $event_time, 
        $location, 
        $category, 
        $image_path, 
        $eventId
    ]);

    // Fetch the updated event to return in response
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set success response
    $response = [
        'success' => true,
        'message' => 'Event updated successfully',
        'event' => $event
    ];

} catch (Exception $e) {
    // Catch application-level exceptions (e.g., validation errors)
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (PDOException $e) {
    // Catch database-level exceptions
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

// Return JSON response
echo json_encode($response);
exit;

<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'event' => null
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $required = ['event_id', 'title', 'description', 'event_date', 'event_time', 'location', 'category'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $eventId = $_POST['event_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] == 'on';

    // Get current image path
    $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $currentImage = $stmt->fetchColumn();
    $image_path = $currentImage;

    // Handle image upload/removal
    if ($removeImage) {
        // Delete the current image file
        if ($currentImage && file_exists("../assets/images/$currentImage")) {
            unlink("../assets/images/$currentImage");
        }
        $image_path = null;
    }

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../assets/images/';
        
        // Delete old image if exists
        if ($currentImage && file_exists("../assets/images/$currentImage")) {
            unlink("../assets/images/$currentImage");
        }

        // Validate and move new image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image');
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload image');
        }

        $image_path = $filename;
    }

    // Update event in database
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

    // Get the updated event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'message' => 'Event updated successfully',
        'event' => $event
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
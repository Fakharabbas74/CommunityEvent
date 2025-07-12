<?php
// Load database configuration
require_once '../includes/config.php';

// Set the content type to JSON for the AJAX response
header('Content-Type: application/json');

// Default response structure
$response = [
    'success' => false,
    'message' => '',
    'event' => null
];

try {
    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // List of required POST fields
    $required = ['title', 'description', 'event_date', 'event_time', 'location', 'category'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize and store form input
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $image_path = null;

    // Handle optional image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../assets/images/'; // Directory to store uploaded images

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Validate uploaded file is an image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not a valid image');
        }

        // Generate a unique filename to avoid name conflicts
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $filename;

        // Move the uploaded file to target directory
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload image');
        }

        // Save relative path to store in the database
        $image_path = $filename;
    }

    // Insert new event into the database
    $stmt = $pdo->prepare("
        INSERT INTO events (title, description, event_date, event_time, location, category, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $event_date, $event_time, $location, $category, $image_path]);

    // Retrieve the inserted event using last inserted ID
    $event_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return success response with event data
    $response = [
        'success' => true,
        'message' => 'Event added successfully',
        'event' => $event
    ];

} catch (Exception $e) {
    // Handle general errors (validation, upload, etc.)
    $response['message'] = $e->getMessage();
    http_response_code(400); // Bad Request
} catch (PDOException $e) {
    // Handle database-specific errors
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500); // Internal Server Error
}

// Send the response in JSON format
echo json_encode($response);
exit;

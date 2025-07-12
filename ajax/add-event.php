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
    $required = ['title', 'description', 'event_date', 'event_time', 'location', 'category'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Process data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_time = trim($_POST['event_time']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $image_path = null;

    // Handle file upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../assets/images/';
        
        // Create directory if needed
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Validate image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            throw new Exception('File is not an image');
        }

        // Generate unique filename
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload image');
        }

        $image_path = $filename;
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, category, image_path)
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $event_date, $event_time, $location, $category, $image_path]);

    // Get the inserted event
    $event_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'message' => 'Event added successfully',
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
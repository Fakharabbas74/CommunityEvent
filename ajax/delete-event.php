<?php
// Include database configuration
require_once '../includes/config.php';

// Set response content type to JSON
header('Content-Type: application/json');

// Default response structure
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Ensure the request method is GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    // Validate and retrieve event ID from the query string
    $eventId = $_GET['id'] ?? 0;
    if (!$eventId) {
        throw new Exception('Invalid event ID');
    }

    // Fetch the image path of the event before deletion
    $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $imagePath = $stmt->fetchColumn(); // returns just the value, not an array

    // Delete the event record from the database
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$eventId]);

    // If the event had an image, delete the image file from disk
    if ($imagePath && file_exists("../assets/images/$imagePath")) {
        unlink("../assets/images/$imagePath");
    }

    // Set successful response
    $response = [
        'success' => true,
        'message' => 'Event deleted successfully',
        'eventId' => $eventId
    ];

    // Return the JSON response to the frontend
    echo json_encode($response);

    // Optional: Handle server-side broadcasting (if using JS BroadcastChannel or session-based logic)
    if (session_status() === PHP_SESSION_ACTIVE) {
        // This part is theoretical. PHP does not support BroadcastChannel natively.
        // BroadcastChannel emulation could be done via sockets or push servers.
        // Leaving this part for semantic parity with JS.
        
        // BroadcastChannel (used in client-side JS, not valid in PHP)
        if (class_exists('BroadcastChannel')) {
            $channel = new BroadcastChannel('events');
            $channel->postMessage([
                'type' => 'event-deleted',
                'eventId' => $eventId
            ]);
            $channel->close();
        }

        // Fallback method: Store info in session for access by client-side JavaScript
        $_SESSION['eventDeleted'] = $eventId;
    }

    // Exit after response
    exit;

} catch (Exception $e) {
    // Handle validation or logical errors
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (PDOException $e) {
    // Handle database-related errors
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

// Output error response
echo json_encode($response);
exit;

<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    $eventId = $_GET['id'] ?? 0;
    if (!$eventId) {
        throw new Exception('Invalid event ID');
    }

    // Get image path before deleting
    $stmt = $pdo->prepare("SELECT image_path FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $imagePath = $stmt->fetchColumn();

    // Delete the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$eventId]);

    // Delete the associated image file if exists
    if ($imagePath && file_exists("../assets/images/$imagePath")) {
        unlink("../assets/images/$imagePath");
    }

    $response = [
        'success' => true,
        'message' => 'Event deleted successfully',
        'eventId' => $eventId
    ];

    // Output the response first so the broadcast happens after
    echo json_encode($response);
    
    // Broadcast the deletion
    if (session_status() === PHP_SESSION_ACTIVE) {
        // BroadcastChannel method
        if (class_exists('BroadcastChannel')) {
            $channel = new BroadcastChannel('events');
            $channel->postMessage([
                'type' => 'event-deleted',
                'eventId' => $eventId
            ]);
            $channel->close();
        }
        
        // localStorage fallback
        $_SESSION['eventDeleted'] = $eventId;
    }

    exit;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
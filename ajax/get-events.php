<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'events' => []
];

try {
    // Get filter parameters
    $searchTerm = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $dateFilter = $_GET['date'] ?? '';
    $isAdminRequest = isset($_GET['admin']);

    $query = "SELECT * FROM events WHERE 1=1";
    $params = [];

    if (!empty($searchTerm)) {
        $query .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
        $params[':search'] = "%$searchTerm%";
    }

    if (!empty($category)) {
        $query .= " AND category = :category";
        $params[':category'] = $category;
    }

    // For admin, show all events by default unless filtered
    if (!$isAdminRequest) {
        if (!empty($dateFilter)) {
            switch ($dateFilter) {
                case 'today':
                    $query .= " AND event_date = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $query .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
                    break;
                case 'future':
                    $query .= " AND event_date >= CURDATE()";
                    break;
            }
        } else {
            // Default for public: show future events
            // $query .= " AND event_date >= CURDATE()";
        }
    }

    $query .= " ORDER BY event_date ASC, event_time ASC";

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    $response['events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
exit;
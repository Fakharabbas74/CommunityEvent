<?php
require_once 'includes/config.php';

// Show errors while developing
ini_set('display_errors', 1);
error_reporting(E_ALL);

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$dateFilter = $_GET['date'] ?? '';

$query = "SELECT * FROM events WHERE 1=1";
$params = [];

// Add search keyword filter
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Add category filter
if (!empty($category)) {
    $query .= " AND category = :category";
    $params[':category'] = $category;
}

// Add date filter
if (!empty($dateFilter)) {
    switch ($dateFilter) {
        case 'today':
            $query .= " AND event_date = CURDATE()";
            break;
        case 'week':
            $query .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $query .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'future':
            $query .= " AND event_date > CURDATE()";
            break;
    }
}

$query .= " ORDER BY event_date ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clean and proper JSON response
    header('Content-Type: application/json');
    echo json_encode($events);
    exit;
} catch (PDOException $e) {
    // Error JSON
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

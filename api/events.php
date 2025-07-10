<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

try {
    // Get filter parameters
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $dateFilter = isset($_GET['date']) ? trim($_GET['date']) : '';

    // Base query
    $sql = "SELECT * FROM events WHERE 1=1";
    $params = [];

    // Add search term filter
    if (!empty($searchTerm)) {
        $sql .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
        $params[':search'] = "%$searchTerm%";
    }

    // Add category filter
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    // Add date filter
    if (!empty($dateFilter)) {
        switch ($dateFilter) {
            case 'today':
                $sql .= " AND event_date = CURDATE()";
                break;
            case 'week':
                $sql .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'future':
                $sql .= " AND event_date >= CURDATE()";
                break;
        }
    } else {
        // Default: show future events
        $sql .= " AND event_date >= CURDATE()";
    }

    // Order by date and time
    $sql .= " ORDER BY event_date, event_time";

    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    // Fetch results
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode([
        'success' => true,
        'events' => $events
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
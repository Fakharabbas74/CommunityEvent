<?php
// Include database connection settings
require_once '../includes/config.php';

// Set the response to be JSON
header('Content-Type: application/json');

// Initialize the default response structure
$response = [
    'success' => false,
    'message' => '',
    'events' => []
];

try {
    // Retrieve query parameters from GET request
    $searchTerm = $_GET['search'] ?? '';        // Optional keyword search
    $category = $_GET['category'] ?? '';        // Optional category filter
    $dateFilter = $_GET['date'] ?? '';          // Optional date range filter
    $isAdminRequest = isset($_GET['admin']);    // Check if admin access (shows all events)

    // Start building SQL query
    $query = "SELECT * FROM events WHERE 1=1";  // 1=1 allows easy appending of AND conditions
    $params = [];

    // Filter: Search term in title, description, or location
    if (!empty($searchTerm)) {
        $query .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
        $params[':search'] = "%$searchTerm%"; // Wrap with % for partial match
    }

    // Filter: Specific category
    if (!empty($category)) {
        $query .= " AND category = :category";
        $params[':category'] = $category;
    }

    // Date filter applies only for public (non-admin) views
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
            // Optional default: show future events only
            // $query .= " AND event_date >= CURDATE()";
        }
    }

    // Sort events by date and time ascending
    $query .= " ORDER BY event_date ASC, event_time ASC";

    // Prepare and bind query
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Execute the query
    $stmt->execute();

    // Fetch all events into an associative array
    $response['events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['success'] = true;

} catch (PDOException $e) {
    // Handle database-related errors
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    // Handle general errors
    $response['message'] = 'Error: ' . $e->getMessage();
    http_response_code(400);
}

// Return the final response as JSON
echo json_encode($response);
exit;

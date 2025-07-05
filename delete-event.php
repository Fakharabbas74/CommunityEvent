<?php
require_once 'includes/auth.php';

// Get event ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['success_message'] = 'Event deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting event: ' . $e->getMessage();
    }
}

header('Location: index.php');
exit();
?>
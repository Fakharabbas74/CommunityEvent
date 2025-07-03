<?php require_once 'includes/auth.php'; ?>
<?php require_once 'includes/header.php'; ?>

<h2>Manage Events</h2>

<?php
// Display success message if exists
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}

// Fetch all events
try {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}
?>

<table class="events-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Location</th>
            <th>Category</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['title']) ?></td>
            <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td><?= htmlspecialchars($event['category']) ?></td>
            <td class="actions">
                <a href="edit-event.php?id=<?= $event['id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                <a href="delete-event.php?id=<?= $event['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- <?php require_once 'includes/footer.php'; ?> -->
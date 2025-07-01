<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h2>Discover Upcoming Community Events</h2>
        <p>Find workshops, exhibitions, and activities in your area</p>
    </div>
</section>

<section class="search-filter">
    <div class="search-box">
        <input type="text" id="search-input" placeholder="Search events...">
        <button id="search-btn"><i class="fas fa-search"></i></button>
    </div>
    <div class="filters">
        <select id="category-filter">
            <option value="">All Categories</option>
            <?php
            $stmt = $pdo->query("SELECT DISTINCT category FROM events ORDER BY category");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.htmlspecialchars($row['category']).'">'.htmlspecialchars($row['category']).'</option>';
            }
            ?>
        </select>
        <select id="date-filter">
            <option value="">All Dates</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="future">Future Events</option>
        </select>
    </div>
</section>

<section id="upcoming-events" class="events-section">
    <h2>Upcoming Events</h2>
    <div id="events-container" class="events-grid">
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM events");
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<div class="event-card" data-category="'.htmlspecialchars($row['category']).'" data-date="'.htmlspecialchars($row['event_date']).'">';
                    echo '<div class="event-image">';
                    echo '<img src="assets/images/'.(!empty($row['image_path']) ? htmlspecialchars($row['image_path']) : 'default.jpg').'" alt="'.htmlspecialchars($row['title']).'">';
                    echo '</div>';
                    echo '<div class="event-details">';
                    echo '<h3>'.htmlspecialchars($row['title']).'</h3>';
                    echo '<div class="event-meta">';
                    echo '<span class="event-date"><i class="far fa-calendar-alt"></i> '.date('M j, Y', strtotime($row['event_date'])).' at '.date('g:i a', strtotime($row['event_time'])).'</span>';
                    echo '<span class="event-location"><i class="fas fa-map-marker-alt"></i> '.htmlspecialchars($row['location']).'</span>';
                    echo '</div>';
                    echo '<p>'.htmlspecialchars(substr($row['description'], 0, 150)).(strlen($row['description']) > 150 ? '...' : '').'</p>';
                    echo '<span class="event-category">'.htmlspecialchars($row['category']).'</span>';
                    echo '</div></div>';
                }
            } else {
                echo '<p class="no-events">No upcoming events found. Please check back later.</p>';
            }
        } catch(PDOException $e) {
            echo '<p class="error">Error loading events: '.$e->getMessage().'</p>';
        }
        ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
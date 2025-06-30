<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .hero {
            background: #0077cc;
            color: #fff;
            text-align: center;
            padding: 50px 20px;
        }
        .search-filter {
            background: #f4f4f4;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .search-box input {
            padding: 10px;
            width: 200px;
        }
        .search-box button {
            padding: 10px;
        }
        .filters select {
            padding: 10px;
        }
        .events-section {
            padding: 40px 20px;
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .event-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .event-image img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .event-details {
            padding: 15px;
        }
        .event-details h3 {
            margin: 0 0 10px;
        }
        .event-meta {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .event-category {
            background: #0077cc;
            color: white;
            padding: 5px 10px;
            display: inline-block;
            border-radius: 4px;
            font-size: 12px;
        }
        .no-events {
            text-align: center;
            font-size: 18px;
            color: #888;
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Discover Upcoming Community Events</h2>
            <p>Find workshops, exhibitions, and activities in your area</p>
        </div>
    </section>

    <!-- Filters -->
    <section class="search-filter">
        <div class="search-box">
            <input type="text" id="search-input" placeholder="Search events...">
            <button id="search-btn"><i class="fas fa-search"></i></button>
        </div>
        <div class="filters">
            <select id="category-filter">
                <option value="">All Categories</option>
                <option value="Workshop">Workshop</option>
                <option value="Exhibition">Exhibition</option>
                <option value="Fitness">Fitness</option>
                <option value="Conference">Conference</option>
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

    <!-- Events Grid -->
    <section id="upcoming-events" class="events-section">
        <h2>Upcoming Events</h2>
        <div id="events-container" class="events-grid">
            <!-- Dummy Event Card 1 -->
            <div class="event-card" data-category="Workshop" data-date="2025-07-02">
                <div class="event-image">
                    <img src="assets/images/workshop.jpg" alt="Creative Writing Workshop">
                </div>
                <div class="event-details">
                    <h3>Creative Writing Workshop</h3>
                    <div class="event-meta">
                        <span class="event-date"><i class="far fa-calendar-alt"></i> Jul 2, 2025 at 3:00 pm</span><br>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> Community Center Hall</span>
                    </div>
                    <p>Explore your creativity and improve your writing skills with our interactive sessions...</p>
                    <span class="event-category">Workshop</span>
                </div>
            </div>

            <!-- Dummy Event Card 2 -->
            <div class="event-card" data-category="Exhibition" data-date="2025-07-10">
                <div class="event-image">
                    <img src="assets/images/exhibition.jpg" alt="Art Exhibition">
                </div>
                <div class="event-details">
                    <h3>Annual Art Exhibition</h3>
                    <div class="event-meta">
                        <span class="event-date"><i class="far fa-calendar-alt"></i> Jul 10, 2025 at 10:00 am</span><br>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> City Gallery</span>
                    </div>
                    <p>Come and enjoy the beautiful artwork by local artists displayed in our summer collection...</p>
                    <span class="event-category">Exhibition</span>
                </div>
            </div>

            <!-- Dummy Event Card 3 -->
            <div class="event-card" data-category="Fitness" data-date="2025-07-15">
                <div class="event-image">
                    <img src="assets/images/fitness.jpg" alt="Morning Yoga Session">
                </div>
                <div class="event-details">
                    <h3>Morning Yoga in the Park</h3>
                    <div class="event-meta">
                        <span class="event-date"><i class="far fa-calendar-alt"></i> Jul 15, 2025 at 6:00 am</span><br>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> Central Park</span>
                    </div>
                    <p>Start your day with peaceful yoga and fresh air. Suitable for all experience levels...</p>
                    <span class="event-category">Fitness</span>
                </div>
            </div>
        </div>
    </section>

</body>
</html>

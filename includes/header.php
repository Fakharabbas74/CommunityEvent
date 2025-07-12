<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Community Events</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="css/events.css">
  <style>
    /* Basic Navbar Styling */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
    }

    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #333;
      color: white;
      padding: 10px 20px;
    }

    .navbar .logo {
      font-size: 24px;
      font-weight: bold;
    }

    .navbar ul {
      list-style: none;
      display: flex;
      margin: 0;
      padding: 0;
    }

    .navbar ul li {
      margin-left: 20px;
    }

    .navbar ul li a {
      color: white;
      text-decoration: none;
      font-size: 16px;
    }

    .navbar ul li a:hover {
      text-decoration: underline;
    }

    .container {
      padding: 20px;
    }
        footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 10px;
    }

    footer a {
      color: #ccc;
      text-decoration: none;
      margin: 0 10px;
    }

    footer a:hover {
      color: white;
    }
  </style>
</head>
<body>

  <header class="navbar">
    <div class="logo">
      <i class="fas fa-calendar-check"></i> Community Events
    </div>
    <ul>
      <li><a href="#">Home</a></li>
      <li><a href="./admin.php">Admin</a></li>

      <!-- <li><a href="#">Events</a></li>
      <li><a href="#">Create Event</a></li>
      <li><a href="#">Contact</a></li> -->
    </ul>
  </header>

  <div class="container">
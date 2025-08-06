<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
    exit();
}

$clientName = $_SESSION['first_name'] ?? 'Client';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard | Titulo</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: #fff;
      min-height: 100vh;
    }

    .topnav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      z-index: 1000;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .topnav .brand {
      font-size: 22px;
      font-weight: bold;
      color: #00ffcc;
      letter-spacing: 1px;
    }

    .topnav .nav-links a {
      margin-left: 25px;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .topnav .nav-links a:hover {
      color: #00ffcc;
    }

    .main {
      padding: 100px 40px 40px;
      max-width: 1000px;
      margin: auto;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }

    .header h1 {
      font-size: 28px;
      color: #fff;
    }

    .search-box input {
      padding: 10px 15px;
      border: none;
      border-radius: 8px;
      outline: none;
      width: 250px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .search-box input::placeholder {
      color: #ccc;
    }

    .updates-section {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .update-card {
      background: rgba(255, 255, 255, 0.05);
      border-left: 5px solid #00ffcc;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      transition: transform 0.2s ease;
    }

    .update-card:hover {
      transform: scale(1.01);
    }

    .update-card h3 {
      margin-bottom: 8px;
      color: #00ffcc;
    }

    .update-card p {
      margin: 0;
      font-size: 14px;
      color: #ccc;
    }
  </style>
</head>
<body>

  <div class="topnav">
    <div class="brand">TITULO</div>
    <div class="nav-links">
      <a href="client_dashboard.php">Dashboard</a>
      <a href="client_files.php">Files</a>
      <a href="client_profile.php">Profile</a>
      <a href="client_chat.php">Chats</a>
      <a href="client-side_tracking">Tracking</a>
      <a href="index.php">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="header">
      <h1>Welcome, <?php echo htmlspecialchars($first_name . " " . $last_name); ?>!</h1>
      <div class="search-box">
        <input type="text" placeholder="Search your updates...">
      </div>
    </div>

    <div class="updates-section">
      <div class="update-card">
        <h3>Survey Plan Uploaded</h3>
        <p>Status: Completed<br><small>Last updated: August 5, 2025</small></p>
      </div>

      <div class="update-card">
        <h3>Site Inspection Scheduled</h3>
        <p>Status: Pending<br><small>Last updated: August 3, 2025</small></p>
      </div>

      <div class="update-card">
        <h3>Initial Document Review</h3>
        <p>Status: In Progress<br><small>Last updated: July 29, 2025</small></p>
      </div>
    </div>
  </div>

</body>
</html>


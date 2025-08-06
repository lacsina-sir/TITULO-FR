<?php
// Database configuration
$host = "localhost";
$dbname = "titulo_db";
$username = "root";
$password = "";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}

// Fetch statistics
$totalRequests = 0;
$surveysCompleted = 0;
$pendingApprovals = 0;

try {
  $stmt = $pdo->query("SELECT COUNT(*) FROM client_requests");
  $totalRequests = $stmt->fetchColumn();

  $stmt = $pdo->query("SELECT COUNT(*) FROM survey_files WHERE status = 'Completed'");
  $surveysCompleted = $stmt->fetchColumn();

  $stmt = $pdo->query("SELECT COUNT(*) FROM client_updates WHERE status = 'Pending'");
  $pendingApprovals = $stmt->fetchColumn();
} catch (PDOException $e) {
  // Use default values if queries fail
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Titulo</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: #fff;
      display: flex;
    }

    .sidebar {
      width: 220px;
      background-color: #111;
      height: 100vh;
      padding-top: 20px;
      position: fixed;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 22px;
      color: #00bcd4;
    }

    .sidebar a {
      display: block;
      padding: 15px 20px;
      color: #bbb;
      text-decoration: none;
      transition: 0.3s;
    }

    .sidebar a:hover {
      background-color: #333;
      color: #fff;
    }

    .main {
      margin-left: 220px;
      padding: 30px;
      width: 100%;
    }

    .main h1 {
      font-size: 28px;
      margin-bottom: 20px;
      color: #fff;
    }

    .search-bar {
      margin-bottom: 30px;
    }

    .search-bar form {
      display: flex;
      gap: 10px;
    }

    .search-bar input[type="text"] {
      flex: 1;
      padding: 12px;
      border-radius: 6px;
      border: none;
      font-size: 16px;
    }

    .search-bar button {
      padding: 12px 20px;
      background-color: #00bcd4;
      color: #000;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .search-bar button:hover {
      background-color: #0097a7;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .card {
      background-color: #1e1e2f;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      color: #fff;
    }

    .card h3 {
      margin-top: 0;
      font-size: 20px;
      color: #00bcd4;
    }

    .card p {
      font-size: 16px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>Titulo Admin</h2>
  <a href="admin_dashboard.php">Dashboard</a>
  <a href="client_request.php">Client Requests</a>
  <a href="client_updates.php">Client Updates</a>
  <a href="transaction_files.php">Survey Files</a>
  <a href="admin_chat.php">Chat</a>
  <a href="index.php">Logout</a>
</div>

<div class="main">
  <h1>Welcome, Admin</h1>

  <!-- Search Bar -->
  <div class="search-bar">
    <form action="search.php" method="GET">
      <input type="text" name="transaction_id" placeholder="Search by Transaction Number" required />
      <button type="submit">Search</button>
    </form>
  </div>

  <!-- Dashboard Cards -->
  <div class="cards">
    <div class="card">
      <h3>Total Requests</h3>
      <p><?= $totalRequests ?> this month</p>
    </div>

    <div class="card">
      <h3>Surveys Completed</h3>
      <p><?= $surveysCompleted ?> approved</p>
    </div>

    <div class="card">
      <h3>Pending Client Approvals</h3>
      <p><?= $pendingApprovals ?> surveys need confirmation</p>
    </div>
  </div>
</div>

</body>
</html>

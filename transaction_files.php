<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Survey Files | Admin Panel</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
      color: #fff;
    }

    .navbar {
      background-color: #1a1a1a;
      padding: 15px;
      color: white;
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
    }

    .sidebar {
      width: 220px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: #111;
      padding-top: 60px;
      box-shadow: 2px 0 10px rgba(0, 255, 255, 0.1);
    }

    .sidebar a {
      display: block;
      padding: 15px 20px;
      color: white;
      text-decoration: none;
      transition: 0.3s;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #00ffff;
      color: black;
    }

    .container {
      margin-left: 240px;
      padding: 80px 20px 20px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .header h1 {
      font-size: 26px;
      border-bottom: 2px solid #00ffff;
      padding-bottom: 8px;
      margin: 0;
    }

    .search-box input {
      padding: 10px;
      width: 280px;
      border-radius: 5px;
      border: none;
    }

    .table-container {
      background: #1f2b38;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 255, 255, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #fff;
    }

    table th {
      background-color: #00bcd4;
      color: #000;
      padding: 12px;
    }

    table td {
      padding: 12px;
      border-bottom: 1px solid #333;
    }

    table tr:nth-child(even) {
      background-color: #263646;
    }

    table tr:hover {
      background-color: #33475b;
    }
  </style>
</head>
<body>

  <div class="navbar">Admin Dashboard - Survey Files</div>

  <div class="sidebar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_client_request.php">Client Requests</a>
    <a href="survey_files.php" class="active">Survey Files</a>
    <a href="admin_client_update.php">Client Updates</a>
    <a href="pending_updates.php">Pending Updates</a>
    <a href="admin_chat.php">Client Chat</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="container">
    <div class="header">
      <h1>Survey Files</h1>
      <div class="search-box">
        <input type="text" placeholder="Search survey files...">
      </div>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Survey Type</th>
            <th>Location</th>
            <th>Status</th>
            <th>Date Submitted</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Placeholder data – replace with actual database fetch later
            $surveyFiles = [
              ["Angelica Ramos", "Lot Survey", "Marcos Village", "Completed", "July 31, 2025"],
              ["Daniel Cruz", "Relocation Survey", "Camp 7", "In Progress", "July 30, 2025"],
              ["Ana Lopez", "Site Verification", "La Trinidad", "Pending", "July 29, 2025"]
            ];

            foreach ($surveyFiles as $file) {
              echo "<tr>";
              foreach ($file as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
              }
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>

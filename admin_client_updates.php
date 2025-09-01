<?php
// DB connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "titulo_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM pending_updates ORDER BY last_updated DESC";
$result = $conn->query($sql);

// Fetch rejected requests
$rejected_sql = "SELECT * FROM rejected_requests ORDER BY rejected_at DESC";
$rejected_result = $conn->query($rejected_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Pending Updates | Admin Panel</title>
  <style>
    body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
    color: #fff;
    display: flex;
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
    transition: background 0.3s, color 0.3s;
    }

    .sidebar a:hover {
      background-color: #333;
      color: #fff;
    }

    .sidebar a.active {
      background-color: #00ffff;
      color: black;
    }

    .container {
      margin-left: 240px;
      padding: 80px 20px 20px;
    }
        
    h1 {
      font-size: 28px;
      margin-bottom: 20px;
      border-bottom: 2px solid #00ffff;
      padding-bottom: 10px;
      color: #fff;
    }

    .search-bar input {
      padding: 10px;
      width: 300px;
      border-radius: 5px;
      border: none;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #1f2b38;
      border-radius: 10px;
      overflow: hidden;
    }

    table thead {
      background-color: #00bcd4;
      color: #000;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
    }

    tbody tr:nth-child(even) {
      background-color: #263646;
    }

    tbody tr:hover {
      background-color: #33475b;
    }

    .actions button {
      padding: 6px 10px;
      background-color: #00ffff;
      border: none;
      color: #000;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 5px;
    }

    .actions button:hover {
      background-color: #1de9b6;
    }

    .container-one {
        max-width: 1000px;
        margin: 50px auto;
        padding: 30px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        animation: fadeIn 1s ease-in-out;
    }

    #toggleRejectedBtn {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: none;
      color: #00ffff;
      border: 2px solid #00ffff;
      padding: 8px 16px;
      font-size: 14px;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    #toggleRejectedBtn:hover {
      background-color: #00ffff;
      color: #000;
      box-shadow: 0 0 8px rgba(0, 255, 255, 0.6);
    }

    #toggleRejectedBtn:active {
      transform: scale(0.95);
    }

  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Titulo Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_client_request.php">Client Requests</a>
    <a href="admin_client_update.php" class="active">Client Updates</a>
    <a href="transaction_files.php">Survey Files</a>
    <a href="admin_chat.php">Chat</a>
    <a href="index.php">Logout</a>
  </div>

  <div class="container">
    <h1>Client Updates</h1>
    <div class="search-bar">
      <input type="text" placeholder="Search pending updates...">
    </div>

  <?php if ($result && $result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Client Name</th>
          <th>Status</th>
          <th>Last Updated</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td colspan="4">
            <div class="update-card">
              <h3><?= htmlspecialchars($row['client_name']) ?> — <?= htmlspecialchars($row['request_type']) ?></h3>
              <p>Transaction #: <strong><?= htmlspecialchars($row['transaction_number']) ?></strong></p>
              <p>Status: <span style="color:#00cc66;font-weight:bold;"><?= htmlspecialchars($row['status']) ?></span></p>
              <p>Last Updated: <?= date('F j, Y, g:i a', strtotime($row['last_updated'])) ?></p>

              <?php 
                $details = json_decode($row['details'], true);
                if (is_array($details)) {
                  foreach ($details as $key => $val) {
                    if (!is_array($val) && $val !== '') {
                      echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($val) . '<br>';
                    }
                  }
                }
              ?>
              <form method="post" action="update_pending_status.php" style="margin-top:10px;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="edit">Edit</button>
                <button type="submit" name="mark_done">Mark as Done</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p class="container-one">No pending updates found.</p>
  <?php endif; ?>


  <div class="button-row">
    <button id="toggleRejectedBtn">Show Rejected Requests</button>
  </div>


  <div id="rejectedSection" class="container-one" style="display: none;">
    <h2>Rejected Requests</h2>
    <?php if ($rejected_result && $rejected_result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Type</th>
            <th>Reason</th>
            <th>Details</th>
            <th>Rejected At</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($r = $rejected_result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['client_name']) ?></td>
              <td><?= htmlspecialchars($r['type']) ?></td>
              <td><?= htmlspecialchars($r['reason']) ?></td>
              <td>
                <?php 
                  $details = json_decode($r['details'], true);
                  if (is_array($details)) {
                    foreach ($details as $key => $val) {
                      if (!is_array($val) && $val !== '') {
                        echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($val) . '<br>';
                      }
                    }
                  }
                ?>
              </td>
              <td><?= date('F j, Y, g:i a', strtotime($r['rejected_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No rejected requests found.</p>
    <?php endif; ?>
  </div>

<script>
  document.getElementById("toggleRejectedBtn").addEventListener("click", function(){
      const section = document.getElementById("rejectedSection");
      if (section.style.display === "none") {
          section.style.display = "block";
          this.textContent = "Hide Rejected Requests";
      } else {
          section.style.display = "none";
          this.textContent = "Show Rejected Requests";
      }
  });
</script>

</body>
</html>

<?php $conn->close(); ?>

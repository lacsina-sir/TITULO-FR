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

$sql = "SELECT * FROM pending_updates WHERE is_done = 0 ORDER BY last_updated DESC";
$result = $conn->query($sql);
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
    <h1>Pending Updates</h1>
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
              <td><?= htmlspecialchars($row['client_name']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td><?= date('F j, Y', strtotime($row['last_updated'])) ?></td>
              <td class="actions">
                <form method="post" action="update_pending_status.php" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" name="edit">Edit</button>
                  <button type="submit" name="mark_done">Mark as Done</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No pending updates found.</p>
    <?php endif; ?>
  </div>

</body>
</html>

<?php $conn->close(); ?>

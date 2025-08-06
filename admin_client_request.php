<?php
// Connect to the database
$host = "localhost";
$user = "root";
$pass = "";
$db = "compass_north";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client requests
$sql = "SELECT * FROM client_requests ORDER BY date_requested DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Requests - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            background: linear-gradient(to right, #1a1a1a, #2b2b2b);
            color: white;
        }

        .sidebar {
            width: 220px;
            background-color: #111;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #333;
        }

        .main {
            margin-left: 220px;
            padding: 30px;
            flex: 1;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .request-card {
            background-color: #1f1f1f;
            border-left: 5px solid #00bcd4;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px #00bcd4;
        }

        .request-card h3 {
            margin-bottom: 5px;
            color: #00bcd4;
        }

        .request-card p {
            margin: 5px 0;
        }

        .request-card small {
            color: #ccc;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_client_requests.php">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
        <a href="admin_survey_files.php">Survey Files</a>
        <a href="admin_pending_updates.php">Pending Updates</a>
        <a href="admin_chat.php">Chat</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h1>Client Requests</h1>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="request-card">
                    <h3><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['email']) ?>)</h3>
                    <p><?= nl2br(htmlspecialchars($row['request'])) ?></p>
                    <small>Requested on <?= date("F j, Y, g:i a", strtotime($row['date_requested'])) ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No client requests found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

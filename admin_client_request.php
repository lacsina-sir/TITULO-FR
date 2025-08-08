<?php

// CONNECT
$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FETCH latest form submissions
$sql = "
    SELECT
        CONCAT(name, ' ', last_name)    AS full_name,
        type,
        location,
        others_text,
        file_path,
        created_at
    FROM client_forms
    ORDER BY created_at DESC
";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Requests – Admin</title>
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

        .main {
        margin-left: 220px;
        padding: 30px;
        width: calc(100% - 220px);
        }
        
        .main h1 {
        font-size: 28px;
        margin-bottom: 20px;
        color: #fff;
        }
        .request-card {
        background-color: #1e1e2f;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        margin-bottom: 20px;
        transition: transform 0.2s ease;
        }

        .request-card h3 {
        margin: 0 0 5px;
        font-size: 20px;
        color: #00bcd4;
        }

        .request-card p {
        margin: 8px 0;
        font-size: 16px;
        }

        .request-card small {
        color: #bbb;
        }
        
        .request-card a {
        color: #00ffcc;
        text-decoration: none;
        }
        .request-card a:hover {
        text-decoration: underline;
        }
    </style>
    </head>
    <body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Titulo Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_client_requests.php" class="active">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
        <a href="transaction_files.php">Survey Files</a>
        <a href="admin_chat.php">Chat</a>
        <a href="index.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h1>Client Requests</h1>

        <?php if ($result->num_rows): ?>
        <?php while ($r = $result->fetch_assoc()): ?>
            <div class="request-card">
            <h3><?= htmlspecialchars($r['full_name']) ?></h3>
            <p>Type: <?= htmlspecialchars($r['type']) ?></p>

            <?php if ($r['type'] === 'Land Survey'): ?>
                <?php if ($r['file_path']): ?>
                <p>Uploaded Form: 
                    <a href="<?= htmlspecialchars($r['file_path']) ?>" target="_blank">
                    View / Download
                    </a>
                </p>
                <?php else: ?>
                <p><em>No file uploaded.</em></p>
                <?php endif; ?>

            <?php elseif ($r['type'] === 'Follow Up'): ?>
                <p>Location of Property: <?= htmlspecialchars($r['location']) ?></p>

            <?php elseif ($r['type'] === 'Others'): ?>
                <p>Details: <?= nl2br(htmlspecialchars($r['others_text'])) ?></p>
            <?php endif; ?>

            <small>Requested on <?= date("F j, Y, g:i a", strtotime($r['created_at'])) ?></small>
            </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p>No client requests found.</p>
        <?php endif; ?>
    </div>

</body>
</html>

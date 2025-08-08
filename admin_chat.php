<?php
session_start();
require_once("db_connection.php");

// Redirect if not admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Fetch messages
$messages = [];
if ($conn) {
    $query = "SELECT * FROM messages ORDER BY timestamp ASC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: #111;
            padding: 20px;
            color: #fff;
            position: fixed;
            height: 100vh;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 22px;
            text-align: center;
        }

        .sidebar a {
            display: block;
            color: #ddd;
            padding: 12px;
            margin-bottom: 10px;
            text-decoration: none;
            border-left: 3px solid transparent;
        }

        .sidebar a:hover {
            background: #333;
            border-left: 3px solid #0af;
        }

        .main-content {
            margin-left: 220px;
            padding: 20px;
            flex: 1;
            color: #fff;
        }

        .chat-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 15px;
        }

        .message.admin {
            text-align: right;
        }

        .message.admin .content {
            background: #007BFF;
            display: inline-block;
            padding: 10px 15px;
            border-radius: 15px;
        }

        .message.client .content {
            background: #444;
            display: inline-block;
            padding: 10px 15px;
            border-radius: 15px;
        }

        form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
        }

        button {
            padding: 10px 20px;
            border: none;
            background: #0af;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #0080ff;
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <h2>TITULO Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_client_request.php">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
        <a href="transaction_files.php">Survey Files</a>
        <a href="admin_chat.php">Chat</a>
        <a href="index.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Admin-Client Chat</h1>

        <div class="chat-container">
            <?php foreach ($messages as $msg): ?>
                <div class="message <?php echo $msg['sender'] === 'admin' ? 'admin' : 'client'; ?>">
                    <div class="content">
                        <strong><?php echo ucfirst($msg['sender']); ?>:</strong>
                        <?php echo htmlspecialchars($msg['message']); ?>
                        <div style="font-size: 10px; margin-top: 4px;">
                            <?php echo date("M d, Y H:i", strtotime($msg['timestamp'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <form action="send_message_admin.php" method="post">
            <input type="text" name="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

</body>
</html>

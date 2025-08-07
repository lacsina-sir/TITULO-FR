<?php
session_start();
include 'db_connection.php';

// Check if client is logged in
if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

// Send message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, message, timestamp) VALUES ('client', ?, 'admin', 1, ?, NOW())");
        $stmt->bind_param("is", $client_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_type='client' AND sender_id=?) OR (receiver_type='client' AND receiver_id=?) ORDER BY timestamp ASC");
$stmt->bind_param("ii", $client_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Chat</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to bottom right, #1e1e2f, #2c3e50);
            color: white;
        }

        .top-nav {
            position: fixed;
            width: 100%;
            background: #14141f;
            padding: 15px 30px;
            font-size: 20px;
            font-weight: bold;
            z-index: 100;
            box-shadow: 0 0 10px #000;
        }

        .chat-container {
            margin-top: 80px;
            padding: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .message-box {
            background: #2f2f3f;
            padding: 15px;
            margin: 10px 0;
            border-radius: 12px;
            max-width: 70%;
            box-shadow: 0 0 8px rgba(0, 150, 255, 0.4);
        }

        .client {
            align-self: flex-end;
            background: #0084ff;
            color: white;
        }

        .admin {
            align-self: flex-start;
            background: #444;
        }

        .messages {
            display: flex;
            flex-direction: column;
        }

        form {
            margin-top: 20px;
            display: flex;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 8px 0 0 8px;
            border: none;
            outline: none;
        }

        button {
            padding: 10px 20px;
            border: none;
            background: #00aaff;
            color: white;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
        }

        button:hover {
            background: #007acc;
        }
    </style>
</head>
<body>

<div class="top-nav">Client Chat</div>

<div class="chat-container">
    <div class="messages">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="message-box <?php echo $row['sender_type'] === 'client' ? 'client' : 'admin'; ?>">
                <strong><?php echo ucfirst($row['sender_type']); ?>:</strong> <?php echo htmlspecialchars($row['message']); ?>
                <br><small><?php echo date("M d, Y H:i", strtotime($row['timestamp'])); ?></small>
            </div>
        <?php endwhile; ?>
    </div>

    <form method="POST">
        <input type="text" name="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</div>

</body>
</html>

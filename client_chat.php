<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$clientName = $_SESSION['first_name'] ?? 'Client';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Chat | Titulo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: white;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* NAVBAR STYLE (from client_dashboard) */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(to right, #0f0c29, #302b63, #24243e);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
      box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
    }

    .navbar .logo {
      font-size: 24px;
      font-weight: bold;
      color: #00ffff;
      text-shadow: 0 0 10px #00ffff;
    }

    .navbar .nav-links {
      display: flex;
      gap: 25px;
    }

    .navbar .nav-links a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      font-size: 16px;
      transition: color 0.3s;
      text-shadow: 0 0 5px rgba(0, 255, 255, 0.4);
    }

    .navbar .nav-links a:hover {
      color: #00ffff;
    }

    .page-content {
      padding-top: 90px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .chat-container {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .message {
      max-width: 60%;
      padding: 12px 18px;
      border-radius: 20px;
      font-size: 0.95rem;
      word-wrap: break-word;
      backdrop-filter: blur(4px);
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    .message.admin {
      align-self: flex-start;
      background: rgba(255, 255, 255, 0.1);
      color: #eee;
      border-left: 4px solid #f39c12;
    }

    .message.client {
      align-self: flex-end;
      background: rgba(0, 153, 255, 0.2);
      color: #fff;
      border-right: 4px solid #00aaff;
    }

    .timestamp {
      font-size: 0.75rem;
      margin-top: 5px;
      opacity: 0.6;
    }

    .input-container {
      display: flex;
      padding: 15px 20px;
      background: rgba(0, 0, 0, 0.85);
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.4);
    }

    .input-container input[type="text"] {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
      outline: none;
      margin-right: 10px;
    }

    .input-container button {
      padding: 12px 20px;
      background: #00aaff;
      color: white;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .input-container button:hover {
      background: #0077cc;
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <div class="navbar">
    <div class="logo">Titulo</div>
    <div class="nav-links">
      <a href="client_dashboard.php">Dashboard</a>
      <a href="client_profile.php">Profile</a>
      <a href="client_chat.php">Chat</a>
      <a href="client-side_tracking.php">Tracking</a>
      <a href="client_files.php">Files</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <!-- PAGE CONTENT -->
  <div class="page-content">
    <div class="chat-container">
      <!-- Example messages -->
      <div class="message admin">
        Hello! How can I assist you today?
        <div class="timestamp">Admin • 2:03 PM</div>
      </div>

      <div class="message client">
        Hi! I have a question about my recent request.
        <div class="timestamp"><?php echo $clientName; ?> • 2:05 PM</div>
      </div>
    </div>

    <form class="input-container" method="post" action="send_message.php">
      <input type="text" name="message" placeholder="Type your message..." required>
      <button type="submit">Send</button>
    </form>
  </div>

</body>
</html>

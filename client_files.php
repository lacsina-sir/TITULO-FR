<?php
// Start session and include DB connection if needed
// session_start();
// include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Files | Titulo</title>
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

    .file-section {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .file-card {
      background: rgba(255, 255, 255, 0.05);
      border-left: 5px solid #00ffcc;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      transition: transform 0.2s ease;
    }

    .file-card:hover {
      transform: scale(1.01);
    }

    .file-card h3 {
      margin-bottom: 8px;
      color: #00ffcc;
    }

    .file-card p {
      margin: 0;
      font-size: 14px;
      color: #ccc;
    }

    .file-card a {
      color: #00ffcc;
      text-decoration: underline;
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
      <a href="client-side_tracking.php">Tracking</a>
      <a href="index.php">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="header">
      <h1>Your Files</h1>
      <div class="search-box">
        <input type="text" placeholder="Search your files...">
      </div>
    </div>

    <div class="file-section">
      <div class="file-card">
        <h3>Survey Plan.pdf</h3>
        <p>Uploaded: August 5, 2025<br>
        <a href="#">Download</a></p>
      </div>

      <div class="file-card">
        <h3>Land Title Verification.docx</h3>
        <p>Uploaded: August 3, 2025<br>
        <a href="#">Download</a></p>
      </div>

      <div class="file-card">
        <h3>Official Receipt.jpg</h3>
        <p>Uploaded: August 1, 2025<br>
        <a href="#">Download</a></p>
      </div>
    </div>
  </div>

</body>
</html>

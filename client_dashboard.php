<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Get first and last name from session
$first_name = $_SESSION['first_name'] ?? 'Client';
$last_name = $_SESSION['last_name'] ?? '';

include 'db_connection.php';
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch submitted forms for this user
$query = "SELECT type, status, rejection_reason, date, location, others_text, file_path FROM client_forms WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard | Titulo</title>
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

    .updates-section, .form-section {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .update-card {
      background: rgba(255, 255, 255, 0.05);
      border-left: 5px solid #00ffcc;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      transition: transform 0.2s ease;
    }

    .update-card:hover {
      transform: scale(1.01);
    }

    .update-card h3 {
      margin-bottom: 8px;
      color: #00ffcc;
    }

    .update-card p {
      margin: 0;
      font-size: 14px;
      color: #ccc;
    }

    .chatbot-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 65px;
    height: 65px;
    background: #00ffcc;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 2000;
    transition: box-shadow 0.2s;
  }
  .chatbot-btn:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
  }
  .chatbot-btn svg {
    width: 32px;
    height: 32px;
    fill: #222;
  }
  .chatbot-modal {
    display: none;
    position: fixed;
    bottom: 0;
    right: 40px;
    width: 370px;
    height: 500px;
    background: #222;
    border-radius: 18px 18px 0 0;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    z-index: 2100;
    overflow: hidden;
    flex-direction: column;
    animation: fadeInUp 0.3s;
  }
  .chatbot-modal.active {
    display: flex;
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px);}
    to { opacity: 1; transform: translateY(0);}
  }
  .chatbot-btn.hide {
    display: none;
  }
  .chatbot-modal-header {
    background: #00ffcc;
    color: #222;
    padding: 12px 18px;
    font-weight: bold;
    font-size: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .chatbot-close {
    cursor: pointer;
    font-size: 22px;
    font-weight: bold;
    color: #222;
  }
  .chatbot-iframe {
    border: none;
    width: 100%;
    height: 100%;
    background: #222;
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
      <a href="client_form.php">Forms</a>
      <a href="client-side_tracking.php">Tracking</a>
      <a href="index.php">Logout</a>
    </div>
  </div>

  <div class="main">
    <div class="header">
      <h1>Welcome, <?php echo htmlspecialchars($first_name . " " . $last_name); ?>!</h1>
      <div class="search-box">
        <input type="text" placeholder="Search your updates...">
      </div>
    </div>

    <div class="updates-section">
      <div class="update-card">
        <h3>Survey Plan Uploaded</h3>
        <p>Status: Completed<br><small>Last updated: August 5, 2025</small></p>
      </div>

      <div class="update-card">
        <h3>Site Inspection Scheduled</h3>
        <p>Status: Pending<br><small>Last updated: August 3, 2025</small></p>
      </div>

      <div class="update-card">
        <h3>Initial Document Review</h3>
        <p>Status: In Progress<br><small>Last updated: July 29, 2025</small></p>
      </div>
    </div>

    <!-- New Form Section -->
    <div class="form-section" style="margin-top:40px;" >
      <h2 style="color:#00ffcc;margin-bottom:18px;">Your Submitted Forms</h2>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="update-card">
            <h3><?php echo htmlspecialchars($row['type']); ?> Submitted</h3>
              <p>
                <?php
                $status = strtolower($row['status']);
                $statusColor = '#ffcc00'; // default: pending (waiting for approval)
                $statusLabel = 'Waiting for approval';

                if ($status === 'approved') {
                    $statusColor = '#00cc66';
                    $statusLabel = 'Approved';
                } elseif ($status === 'rejected') {
                    $statusColor = '#ff3333';
                    $statusLabel = 'Rejected';
                }
                ?>
                Status: <span style="color:<?php echo $statusColor; ?>; font-weight:bold;">
                  <?php echo $statusLabel; ?>
                </span><br>
                Date Submitted: <?php echo htmlspecialchars($row['date']); ?>
                <?php if (strtolower($status) === 'rejected' && !empty($row['reject_reason'])): ?>
                  <br><span style="color:#ff3333;font-weight:bold;">Reason: <?php echo htmlspecialchars($row['reject_reason']); ?></span>
                <?php endif; ?>
              </p>
            <?php if (!empty($row['location'])): ?>
              <p>Location: <?php echo htmlspecialchars($row['location']); ?></p>
            <?php endif; ?>
            <?php if (!empty($row['others_text'])): ?>
              <p>Reason of Reject: <?php echo htmlspecialchars($row['others_text']); ?></p>
            <?php endif; ?>
            <?php if (!empty($row['file_path'])): ?>
              <p>File: <a href="<?php echo htmlspecialchars($row['file_path']); ?>" style="color:#00ffcc;" target="_blank">Download</a></p>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="update-card">
          <h3>No forms submitted yet.</h3>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="chatbot-btn" id="chatbotBtn" title="Chat-Admin">
    <svg viewBox="0 0 24 24"><path d="M12 3C7.03 3 3 6.58 3 11c0 2.39 1.19 4.54 3.17 6.13L5 21l4.13-1.17C10.73 20.61 11.36 21 12 21c4.97 0 9-3.58 9-8s-4.03-8-9-8zm0 16c-.52 0-1.03-.07-1.52-.19l-.36-.09-2.44.69.69-2.44-.09-.36C6.07 15.03 5 13.13 5 11c0-3.31 3.58-6 8-6s8 2.69 8 6-3.58 6-8 6z"/></svg>
  </div>


  <div class="chatbot-modal" id="chatbotModal">
    <iframe src="client_chatbot.php" class="chatbot-iframe"></iframe>
  </div>

  <script>
  const chatbotBtn = document.getElementById('chatbotBtn');
  const chatbotModal = document.getElementById('chatbotModal');

  chatbotBtn.onclick = function() {
    chatbotModal.classList.add('active');
    chatbotBtn.classList.add('hide');
  };

  // Listen for close message from iframe
  window.addEventListener('message', function(event) {
    if (event.data === 'closeChatbot') {
      chatbotModal.classList.remove('active');
      chatbotBtn.classList.remove('hide');
    }
  });
</script>
</body>
</html>


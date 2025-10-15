<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
  $message = trim($_POST['message']);
  if ($message !== '') {
    $conn = new mysqli("localhost", "root", "", "titulo_db");
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
    $user_id = $_SESSION['user_id'] ?? 1; 
    $stmt = $conn->prepare("INSERT INTO chat_messages (sender, user_id, message) VALUES ('user', ?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
    $conn->close();
  }
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Chat with Admin</title>
  <style>
    body {
      margin: 0;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }

    .chatbot-container {
      background: #fff;
      border-radius: 0;
      box-shadow: none;
      display: flex;
      flex-direction: column;
      height: 100vh;
      width: 100vw;
      overflow: hidden;
    }

    /* Header */
    .chatbot-header {
      background: #fff;
      color: #222;
      padding: 10px 16px;
      display: flex;
      align-items: left;
      justify-content: left;
      font-weight: bold;
      font-size: 16px;
      position: relative;
    }

    .chatbot-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chatbot-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      object-fit: cover;
    }

    .chatbot-header-right {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 22px;
      color: #333;
      font-weight: bold;
    }

    /* Messages */
    .chatbot-messages {
      flex: 1;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow-y: auto;
      background: #fff;
    }

    .bubble {
      max-width: 75%;
      padding: 10px 16px;
      border-radius: 18px;
      font-size: 15px;
      line-height: 1.4;
      position: relative;
      word-wrap: break-word;
    }

    .bubble.user {
      align-self: flex-end;
      background: #00a884;
      color: #fff;
      border-bottom-right-radius: 6px;
    }

    .bubble.admin {
      align-self: flex-start;
      background: #f1f1f1;
      color: #333;
      border-bottom-left-radius: 6px;
    }

    .timestamp {
      font-size: 11px;
      color: #999;
      margin-top: 2px;
      text-align: right;
    }

    /* Input area */
    .chatbot-input-area {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px;
      background: #f7f7f7;
      border-top: 1px solid #ddd;
    }

    .chatbot-input {
      flex: 1;
      border: none;
      border-radius: 20px;
      padding: 12px;
      background: #eee;
      font-size: 16px;
      outline: none;
    }

    .chatbot-send-btn {
      background: #00a884;
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .chatbot-send-btn svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    .chatbot-send-btn:hover {
      background: #009671;
    }

    .timestamp {
      font-size: 11px;
      color: #999;
      margin-top: 2px;
    }

    .date-separator {
      text-align: center;
      margin: 15px 0;
      font-size: 13px;
      color: #aaa;
      }

    /* Align timestamp according to bubble type */
    .bubble.user + .timestamp {
      text-align: right;
    }

    .bubble.admin + .timestamp {
      text-align: left;
    }

    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 6px;
    }
    ::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 6px;
    }

    @media (max-width: 480px) {
      .chatbot-input {
        font-size: 14px;
      }

      .bubble {
        font-size: 14px;
      }
    }

  </style>
</head>
<body>
  <div class="chatbot-container">
    <!-- Header -->
    <div class="chatbot-header">
      <div class="chatbot-header-left">
        <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" class="chatbot-avatar" alt="Admin">
        <div class="chatbot-header-title">Admin</div>
      </div>
      <div class="chatbot-header-right" id="chatbotClose">&times;</div>
    </div>

    <!-- Messages -->
    <div class="chatbot-messages" id="chatbotMessages">
      <div class="bubble admin">Hello! Welcome to Compass North. I'm Admin.</div>
      <div class="bubble admin">Do you need any assistance?</div>
    </div>

    <!-- Input -->
    <form class="chatbot-input-area" id="chatbotForm" autocomplete="off">
      <input type="text" name="message" class="chatbot-input" id="chatbotInput" placeholder="Type your message..." required>
      <button type="submit" class="chatbot-send-btn">
        <svg viewBox="0 0 24 24"><path d="M2 21l21-9-21-9v7l15 2-15 2z"/></svg>
      </button>
    </form>
  </div>

  <script>
    const messages = document.getElementById('chatbotMessages');
    const form = document.getElementById('chatbotForm');
    const input = document.getElementById('chatbotInput');
    const userId = <?php echo json_encode($_SESSION['user_id'] ?? 1); ?>;

    // 🟢 Function to load all messages from DB
    function loadMessages(scrollToBottom = false) {
        fetch('get_messages.php?user_id=' + userId)
        .then(res => res.json())
        .then(data => {
            const chatContainer = document.getElementById('chatbotMessages');

            // Remove existing dynamic messages
            const existingBubbles = chatContainer.querySelectorAll('.bubble.dynamic, .timestamp.dynamic, .date-separator');
            existingBubbles.forEach(el => el.remove());

            let lastDate = null;

            data.forEach(msg => {
                const msgDate = new Date(msg.timestamp);
                const formattedDate = msgDate.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });

                // Add date separator if it's a new day
                if (lastDate !== formattedDate) {
                    const dateDiv = document.createElement('div');
                    dateDiv.className = 'date-separator';
                    dateDiv.textContent = formattedDate;
                    dateDiv.style.textAlign = 'center';
                    dateDiv.style.color = '#999';
                    dateDiv.style.margin = '15px 0';
                    chatContainer.appendChild(dateDiv);
                    lastDate = formattedDate;
                }

                // Message bubble
                const bubble = document.createElement('div');
                bubble.className = 'bubble ' + (msg.sender === 'user' ? 'user' : 'admin') + ' dynamic';
                bubble.textContent = msg.message;
                chatContainer.appendChild(bubble);

                // Timestamp below message
                const time = document.createElement('div');
                time.className = 'timestamp dynamic';
                time.textContent = msgDate.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
                chatContainer.appendChild(time);
            });

            if (scrollToBottom) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        })
        .catch(err => console.error('Error loading messages:', err));
    }

    // 🟢 Send message
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(text) + '&sender=user&user_id=' + userId
      })
      .then(res => res.json())
      .then(response => {
        if (response.success) {
          input.value = '';
          loadMessages(true); // reload & scroll down
        }
      });
    });

    // 🟢 Auto-load messages when chatbot opens
    window.addEventListener('DOMContentLoaded', () => loadMessages(true));

    // 🟢 Auto-refresh every 3 seconds (real-time updates)
    setInterval(loadMessages, 3000);

    // 🟢 Close chatbot
    document.getElementById('chatbotClose').onclick = () => {
      window.parent.postMessage('closeChatbot', '*');
    };
  </script>
</body>
</html>

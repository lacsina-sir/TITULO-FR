<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
  $message = trim($_POST['message']);
  if ($message !== '') {
    $conn = new mysqli("localhost", "root", "", "titulo_db");
    $stmt = $conn->prepare("INSERT INTO chat_messages (sender, client_id, message) VALUES ('client', ?, ?)");
    $stmt->bind_param("is", $client_id, $message);
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #fff;
  }

  .chatbot-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: rgba(34, 34, 34, 0.98);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    max-width: 370px;
    margin: auto;
  }

  .chatbot-header {
    background: #00ffcc;
    color: #222;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
    font-weight: bold;
    font-size: 18px;
  }

  .chatbot-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(20,58,109,0.15);
  }

  .chatbot-header-info {
    display: flex;
    flex-direction: column;
  }

  .chatbot-header-title {
    font-size: 16px;
    font-weight: 600;
    color: #222;
  }
  
  .chatbot-header-status {
    font-size: 13px;
    color: #143a6d;
  }

  .chatbot-close {
    cursor: pointer;
    font-size: 22px;
    font-weight: bold;
    color: #222;
  }
  
  .chatbot-messages {
    flex: 1;
    padding: 18px 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    overflow-y: auto;
    background: transparent;
  }

  .bubble {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.18);
    margin-bottom: 2px;
    word-break: break-word;
    position: relative;
  }

  .bubble.user {
    align-self: flex-end;
    background: #00ffcc;
    color: #222;
    border-bottom-right-radius: 6px;
  }

  .bubble.admin {
    align-self: flex-start;
    background: #222;
    color: #fff;
    border-bottom-left-radius: 6px;
    border: 1px solid #00ffcc;
  }

  .chatbot-input-area {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #222;
    border-bottom-left-radius: 18px;
    border-bottom-right-radius: 18px;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.15);
    gap: 8px;
  }

  .chatbot-input {
    flex: 1;
    padding: 10px 14px;
    border-radius: 18px;
    border: 1px solid #00ffcc;
    font-size: 15px;
    outline: none;
    background: rgba(255,255,255,0.08);
    color: #fff;
  }

  .chatbot-input::placeholder {
    color: #ccc;
  }
  
  .chatbot-send-btn {
    background: #00ffcc;
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
  }

  .chatbot-send-btn:hover {
    background: #00e6b3;
  }

  .chatbot-send-btn svg {
    width: 22px;
    height: 22px;
    fill: #222;
  }

  .typing-dots {
    display: inline-flex;
    gap: 4px;
  }

  .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #00ffcc;
    animation: blink 1.4s infinite both;
  }

  @keyframes blink {
    0%, 20% {
      transform: scale(1);
      opacity: 1;
    }
    50% {
      transform: scale(1.2);
      opacity: 0.7;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }
  </style>
</head>
<body>
  <div class="chatbot-container">
    <div class="chatbot-header">
      <img src="https://logodix.com/logo/1707094.png" class="chatbot-avatar" alt="Admin">
      <div class="chatbot-header-info">
        <div class="chatbot-header-title">Chat with Admin</div>
        <div class="chatbot-header-status">We're online</div>
      </div>
      <span class="chatbot-close" id="chatbotClose" title="Close" style="margin-left:auto;cursor:pointer;font-size:22px;font-weight:bold;">&times;</span>
    </div>
    <div class="chatbot-messages" id="chatbotMessages">
      <div class="bubble admin" id="welcomeMsg">Hello! Welcome to Compass North, how can we assist you today?</div>
    </div>
    <form class="chatbot-input-area" id="chatbotForm" autocomplete="off">
      <input type="text" name="message" class="chatbot-input" id="chatbotInput" placeholder="Enter your message..." required>
      <button type="submit" class="chatbot-send-btn">
        <svg viewBox="0 0 24 24"><path d="M2 21l21-9-21-9v7l15 2-15 2z"/></svg>
      </button>
    </form>
  </div>

  <script>
const messages = document.getElementById('chatbotMessages');

// Show "Admin is typing..."
function showAdminTyping() {
  messages.innerHTML = '';

  const typingBubble = document.createElement('div');
  typingBubble.className = 'bubble admin';
  typingBubble.id = 'adminTyping';

  // Create animated dots
  typingBubble.innerHTML = `
    <span class="typing-dots">
      <span class="dot"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </span>
  `;
  messages.appendChild(typingBubble);
  messages.scrollTop = messages.scrollHeight;

  setTimeout(() => {
    typingBubble.textContent = 'Hello! Welcome to Compass North, how can we assist you today?';
    typingBubble.id = '';
  }, 3000);
}

// Run when chat loads
window.onload = showAdminTyping;

const form = document.getElementById('chatbotForm');
const input = document.getElementById('chatbotInput');

form.addEventListener('submit', function(e) {
  e.preventDefault();
  const text = input.value.trim();
  if (text) {
    // Show user bubble
    const userBubble = document.createElement('div');
    userBubble.className = 'bubble user';
    userBubble.textContent = text;
    messages.appendChild(userBubble);
    messages.scrollTop = messages.scrollHeight;
    input.value = '';

    // Send message to admin_chat.php
    fetch('admin_chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'message=' + encodeURIComponent(text)
    })
    .then(response => response.text())
    .then(data => {
      // Optionally handle admin response here
    });
  }
});

const chatbotClose = document.getElementById('chatbotClose');
chatbotClose.onclick = () => {
  window.parent.postMessage('closeChatbot', '*');
};
</script>
</body>
</html>
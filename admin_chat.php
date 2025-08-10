<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$clientResult = $conn->query("
    SELECT DISTINCT c.id, c.name 
    FROM chat_messages m 
    JOIN clients c ON m.client_id = c.id 
    ORDER BY m.id DESC
");

$clients = [];
if ($clientResult) {
    while ($row = $clientResult->fetch_assoc()) {
        $clients[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Admin Chat</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<style>
/* kept your original look, minimal changes */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
    color: #fff;
    display: flex;
    height: 100vh;
}

/* Left clients column */
.sidebar {
    width: 260px;
    background-color: #111;
    padding: 20px;
    overflow-y: auto;
    box-sizing: border-box;
}

/* small hamburger (toggles admin nav) */
.hamburger {
    width: 40px;
    height: 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    cursor: pointer;
    margin-bottom: 18px;
}
.hamburger span {
    display: block;
    width: 28px;
    height: 4px;
    background: #00bcd4;
    margin: 4px 0;
    border-radius: 2px;
    transition: 0.3s;
}
.hamburger:hover span { background: #fff; }

.client {
    display: flex;
    align-items: center;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #1e1e2f;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}
.client:hover { background-color: #2c3e50; }

.client-name { font-size: 16px; color: #fff; }

/* admin nav that slides in/out (independent) */
.sidebar-nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    background: rgba(20, 32, 44, 0.98);
    box-shadow: 2px 0 16px rgba(0,0,0,0.18);
    z-index: 9999;
    transform: translateX(-100%);
    transition: transform 0.3s;
}
.sidebar-nav.active { transform: translateX(0); }
.sidebar-nav-content { display:flex; flex-direction:column; padding:40px 24px; gap:18px; align-items:left; }
.sidebar-nav-content .admin-text { font-size:22px; color:#00bcd4; margin-bottom:20px; text-align:center; }
.sidebar-nav-content a { color:#00bcd4; text-decoration:none; font-size:18px; font-weight:bold; padding:8px 0; transition: color 0.2s; }
.sidebar-nav-content a:hover { color:#fff; }

/* Chat area */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 20px;
    box-sizing: border-box;
}
.chat-header { font-size: 20px; margin-bottom: 10px; color: #00ffff; }
.chat-box {
    flex: 1;
    background-color: rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 20px;
    overflow-y: auto;
}
.message {
    max-width: 60%;
    margin-bottom: 15px;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    word-break: break-word;
}
.message.client { background-color: #2c3e50; color: #fff; align-self: flex-start; border-bottom-left-radius: 6px; }
.message.admin  { background-color: #00bcd4; color: #000; align-self: flex-end; border-bottom-right-radius: 6px; }

.chat-input { display:flex; margin-top:10px; }
.chat-input input {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: none;
    background-color: #333;
    color: #fff;
    outline: none;
}
.chat-input button {
    padding: 10px 20px;
    background-color: #00bcd4;
    border: none;
    border-radius: 8px;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    margin-left: 10px;
}
.chat-input button:hover { background-color: #0097a7; }

/* small responsive tweaks */
@media (max-width: 800px) {
    .sidebar { width: 220px; }
}
</style>
</head>
<body>

    <!-- Clients column -->
    <div class="sidebar">
        <div class="hamburger" id="hamburgerMenu" title="Menu">
            <span></span><span></span><span></span>
        </div>

        <h2>Clients</h2>
        <?php foreach ($clients as $client): ?>
            <div class="client" data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name'], ENT_QUOTES) ?>">
                <div class="client-name"><?= htmlspecialchars($client['name']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Slide-in admin nav (toggled by hamburger) -->
    <div class="sidebar-nav" id="sidebarNav" aria-hidden="true">
        <div class="sidebar-nav-content">
            <h2 class="admin-text">Titulo Admin</h2>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="admin_client_request.php">Client Requests</a>
            <a href="admin_client_updates.php">Client Updates</a>
            <a href="transaction_files.php">Survey Files</a>
            <a href="admin_chat.php" class="active">Chat</a>
            <a href="index.php">Logout</a>
        </div>
    </div>

    <!-- Chat area -->
    <div class="chat-area">
        <div class="chat-header" id="chatHeader">Select a client to start chatting</div>

        <div class="chat-box" id="chatBox">
            <div style="color:#aaa; text-align:center; margin-top:40px;">Select a client to view messages.</div>
        </div>

        <div id="chatInputContainer"></div>
    </div>

<script>
// ---- DOM refs ----
const chatBox = document.getElementById('chatBox');
const chatInputContainer = document.getElementById('chatInputContainer');
const chatHeader = document.getElementById('chatHeader');
const sidebarNav = document.getElementById('sidebarNav');
const hamburgerMenu = document.getElementById('hamburgerMenu');

let selectedClientId = null;
let lastTimestamp = "0000-00-00 00:00:00";
let pollIntervalId = null;

// render a single message into chatBox
function renderMessage(msg) {
    const div = document.createElement('div');
    // guard keys in case server returns slightly different names
    const sender = msg.sender || msg.from || 'client';
    div.className = 'message ' + (sender === 'admin' ? 'admin' : 'client');

    const label = document.createElement('span');
    label.style.fontWeight = 'bold';
    label.style.color = sender === 'client' ? '#00bcd4' : '#0097a7';
    const name = msg.name || msg.client_name || (sender === 'admin' ? 'Admin' : 'Client');
    label.textContent = sender === 'client' ? (name + ': ') : 'Admin: ';
    div.appendChild(label);

    // message text
    const textNode = document.createTextNode((msg.message || msg.text || '') + ' ');
    div.appendChild(textNode);

    // optional timestamp
    if (msg.timestamp) {
        const t = document.createElement('div');
        t.style.fontSize = '12px';
        t.style.opacity = '0.7';
        t.textContent = msg.timestamp;
        div.appendChild(t);
    }

    chatBox.appendChild(div);
}

// fetch messages from server for selected client
async function fetchMessages() {
    if (!selectedClientId) return;
    try {
        const res = await fetch(`get_messages.php?client_id=${encodeURIComponent(selectedClientId)}&lastTimestamp=${encodeURIComponent(lastTimestamp)}`);
        if (!res.ok) throw new Error('Network error: ' + res.status);
        const messages = await res.json();
        if (!Array.isArray(messages)) return;

        // append and update lastTimestamp if provided
        messages.forEach(msg => {
            renderMessage(msg);
            if (msg.timestamp) lastTimestamp = msg.timestamp;
        });

        // scroll to bottom
        chatBox.scrollTop = chatBox.scrollHeight;
    } catch (err) {
        console.error('fetchMessages error:', err);
    }
}

// create and attach chat input form
function createChatForm() {
    chatInputContainer.innerHTML = `
        <form class="chat-input" id="chatForm" autocomplete="off">
            <input type="text" id="messageInput" name="message" placeholder="Type your message..." required />
            <button type="submit">Send</button>
        </form>
    `;
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        try {
            const formData = new FormData();
            formData.append('message', message);
            formData.append('sender', 'admin');
            formData.append('client_id', selectedClientId);

            const res = await fetch('send_message.php', { method: 'POST', body: formData });
            const result = await res.json();

            if (result && result.success) {
                // optimistic render
                const now = new Date();
                const ts = now.toISOString().slice(0,19).replace('T',' ');
                renderMessage({ sender: 'admin', message: message, timestamp: ts });
                chatBox.scrollTop = chatBox.scrollHeight;
                messageInput.value = '';
            } else {
                alert(result && result.error ? result.error : 'Failed to send message');
            }
        } catch (err) {
            console.error('send message error', err);
            alert('Network error, message not sent.');
        }
    });
}

// attach click handlers to client items
document.querySelectorAll('.client').forEach(clientEl => {
    clientEl.addEventListener('click', async () => {
        selectedClientId = clientEl.dataset.id;
        const clientName = clientEl.dataset.name || 'Client';

        // update header and clear previous messages
        chatHeader.textContent = `Chat with ${clientName}`;
        chatBox.innerHTML = '<div style="color:#aaa; text-align:center; margin-top:12px;">Loading messages...</div>';

        // reset lastTimestamp for fresh fetch
        lastTimestamp = "0000-00-00 00:00:00";

        // create input and fetch
        createChatForm();
        chatBox.innerHTML = '';
        await fetchMessages();

        // start polling (only one interval)
        if (pollIntervalId) clearInterval(pollIntervalId);
        pollIntervalId = setInterval(fetchMessages, 3000);
    });
});

// hamburger toggles admin nav (won't affect chat)
hamburgerMenu.addEventListener('click', () => {
    sidebarNav.classList.toggle('active');
    sidebarNav.setAttribute('aria-hidden', !sidebarNav.classList.contains('active'));
});

// close admin nav when clicking outside
document.addEventListener('click', (e) => {
    const isInside = sidebarNav.contains(e.target) || hamburgerMenu.contains(e.target);
    if (!isInside && sidebarNav.classList.contains('active')) {
        sidebarNav.classList.remove('active');
        sidebarNav.setAttribute('aria-hidden', 'true');
    }
});
</script>

</body>
</html>

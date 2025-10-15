<?php
session_start();
$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$clientResult = $conn->query("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name,
            (SELECT message FROM chat_messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) AS last_message
    FROM users u
    ORDER BY u.first_name ASC
");

$user_first_name = $_SESSION['first_name'] ?? 'Client';
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
        <title>Admin Chat | Titulo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <style>
        /* --- General Layout --- */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: #fff;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background-color: #111;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
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
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #333;
            color: #fff;
        }

        .sidebar a.active {
            background-color: #00ffff;
            color: #000;
        }

        /* --- Main Chat Layout --- */
        .main {
            margin-left: 220px;
            display: flex;
            width: calc(100% - 220px);
            height: 100vh;
        }

        /* Left column: Client list */
        .client-list {
            width: 300px;
            background-color: #111827;
            padding: 20px;
            overflow-y: auto;
            border-right: 2px solid #00bcd4;
        }

        .client-list h3 {
            text-align: center;
            color: #00ffff;
            margin-bottom: 20px;
        }

        .client {
            background-color: #1f2937;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .client:hover {
            background-color: #2c3e50;
        }

        .client .name {
            font-size: 16px;
            font-weight: bold;
            color: #00bcd4;
        }

        .client .message-preview {
            font-size: 13px;
            color: #ccc;
        }

        /* Right column: Chat area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .chat-header {
            font-size: 20px;
            color: #00ffff;
            margin-bottom: 10px;
            border-bottom: 2px solid #00bcd4;
            padding-bottom: 8px;
        }

        .chat-box {
            flex: 1;
            background-color: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        /* Chat message styles */
        .message {
            display: flex;
            flex-direction: column;
            max-width: 60%;
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 12px;
            word-break: break-word;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            position: relative;
        }

        /* Client messages (left) */
        .message.client {
            background-color: #2c3e50;
            color: #fff;
            align-self: flex-start;
        }

        /* Admin messages (right) */
        .message.admin {
            background-color: #00bcd4;
            color: #000;
            align-self: flex-end;
        }

        /* Name on top of message */
        .message .sender-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            display: none;
        }

        /* Timestamp below message */
        .message .timestamp {
            display: none;
            font-size: 12px;
            opacity: 0.7;
            margin-top: 4px;
        }

        /* Timestamp outside bubble */
        .timestamp-outside {
            font-size: 12px;
            color: #aaa;
            margin: 2px 0 10px 0;
            background: none;
            box-shadow: none;
            padding: 0;
            max-width: 60%;
        }

        /* Align left or right */
        .timestamp-outside.admin {
            text-align: right;
            margin-left: auto;
            margin-right: 0;
        }

        .timestamp-outside.client {
            text-align: left;
            margin-left: 0;
            margin-right: auto;
        }

        .timestamp-outside.admin {
            text-align: right;
        }

        .timestamp-outside.client {
            text-align: left;
        }

        /* Date separator */
        .date-separator {
            text-align: center;
            margin: 15px 0;
            font-size: 13px;
            color: #aaa;
        }

        .chat-input {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }

        .chat-input input[type="text"] {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background-color: #333;
            color: #fff;
            outline: none;
            font-size: 15px;
        }

        .chat-input input[type="file"] {
            display: none;
        }

        .chat-input label {
            background-color: #00bcd4;
            color: #000;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        .chat-input button {
            padding: 12px 20px;
            background-color: #00bcd4;
            color: #000;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }

        .chat-input button:hover {
            background-color: #0097a7;
        }

        @media (max-width: 900px) {
            .client-list {
                display: none;
            }
        }

        .client.active {
            background-color: #0096a7a9 !important;
            color: #000;
        }

        </style>
    </head>
    <body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Titulo Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_client_request.php">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
        <a href="transaction_files.php">Survey Files</a>
        <a href="admin_chat.php" class="active">Chat</a>
        <a href="index.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <!-- Client List -->
        <div class="client-list">
            <h3>Clients</h3>
            <?php foreach ($clients as $client): ?>
            <div class="client" data-id="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name'], ENT_QUOTES) ?>">
                <div class="name"><?= htmlspecialchars($client['name']) ?></div>
                <div class="message-preview">Click to view messages</div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <div class="chat-header" id="chatHeader">Select a client to start chatting</div>
            <div class="chat-box" id="chatBox">
            <div style="color:#aaa; text-align:center; margin-top:40px;">Select a client to view messages.</div>
            </div>
            <div id="chatInputContainer"></div>
        </div>
    </div>

    <script>
        const shownMessages = new Set();
        const chatBox = document.getElementById('chatBox');
        const chatInputContainer = document.getElementById('chatInputContainer');
        const chatHeader = document.getElementById('chatHeader');

        let selectedClientId = null;
        let selectedClientName = null; // ✅ store current client's name
        let lastTimestamp = "0000-00-00 00:00:00";
        let pollIntervalId = null;

        function renderMessage(msg) {
            // Date separator
            const msgDate = new Date(msg.timestamp).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
            if (!renderMessage.lastDate || renderMessage.lastDate !== msgDate) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'date-separator';
                dateDiv.textContent = msgDate;
                chatBox.appendChild(dateDiv);
                renderMessage.lastDate = msgDate;
            }

            // Message bubble
            const div = document.createElement('div');
            const sender = msg.sender || msg.from || 'client';
            div.className = 'message ' + (sender === 'admin' ? 'admin' : 'client');

            // Message text
            if (msg.message) {
                const textDiv = document.createElement('div');
                textDiv.textContent = msg.message;
                div.appendChild(textDiv);
            }

            // File attachment
            if (msg.file_path) {
                const fileDiv = document.createElement('div');
                fileDiv.style.marginTop = '5px';
                const ext = msg.file_path.split('.').pop().toLowerCase();
                // If image, show preview
                if (['jpg','jpeg','png','gif','webp'].includes(ext)) {
                    const img = document.createElement('img');
                    img.src = msg.file_path;
                    img.style.maxWidth = '200px';
                    img.style.borderRadius = '8px';
                    fileDiv.appendChild(img);
                } else {
                    // Otherwise, provide a download link
                    const a = document.createElement('a');
                    a.href = msg.file_path;
                    a.target = "_blank";
                    a.textContent = `📎 ${msg.file_path.split('/').pop()}`;
                    a.style.color = sender === 'admin' ? '#000' : '#fff';
                    a.style.textDecoration = 'underline';
                    fileDiv.appendChild(a);
                }
                div.appendChild(fileDiv);
            }

            chatBox.appendChild(div);

            // Timestamp outside
            if (msg.timestamp) {
                const t = document.createElement('div');
                t.className = 'timestamp-outside ' + (sender === 'admin' ? 'admin' : 'client');
                t.textContent = new Date(msg.timestamp).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                chatBox.appendChild(t);
            }
        }
        

        async function fetchMessages() {
            if (!selectedClientId) return;
            try {
                const res = await fetch(`get_messages.php?user_id=${encodeURIComponent(selectedClientId)}&lastTimestamp=${encodeURIComponent(lastTimestamp)}`);
                if (!res.ok) throw new Error('Network error');
                const messages = await res.json();
                if (!Array.isArray(messages)) return;

                messages.forEach(msg => {
                    if (shownMessages.has(msg.id)) return; // prevent duplicates
                    shownMessages.add(msg.id);
                    renderMessage(msg);
                    if (msg.timestamp) lastTimestamp = msg.timestamp;
                });

            } catch (err) {
                console.error(err);
            }
        }

        function createChatForm() {
            chatInputContainer.innerHTML = `
            <form class="chat-input" id="chatForm" autocomplete="off">
                <label for="fileInput">📎</label>
                <input type="file" id="fileInput" name="file" accept="image/*" />
                <input type="text" id="messageInput" name="message" placeholder="Type your message..." required />
                <button type="submit">Send</button>
            </form>
            `;

            const chatForm = document.getElementById('chatForm');
            const messageInput = document.getElementById('messageInput');
            const fileInput = document.getElementById('fileInput');

            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const message = messageInput.value.trim();
                const file = fileInput.files[0];

                if (!message && !file) return;

                const formData = new FormData();
                formData.append('message', message);
                formData.append('sender', 'admin');
                formData.append('user_id', selectedClientId);
                if (file) formData.append('file', file);

                try {
                    const res = await fetch('send_message.php', { method: 'POST', body: formData });
                    const result = await res.json();

                    if (result && result.success && result.message) {
                        // ✅ Add the new message ID to prevent duplicate rendering
                        shownMessages.add(result.message.id);
                        renderMessage(result.message); // now only render once
                        messageInput.value = '';
                        fileInput.value = '';
                    } else {
                        alert('Failed to send message');
                    }
                } catch {
                    alert('Network error');
                }
            });
        }

        // ✅ FIX: dynamic client name + proper reset when switching clients
        document.querySelectorAll('.client').forEach(clientEl => {
            clientEl.addEventListener('click', async () => {
                // 🔹 Highlight active client
                document.querySelectorAll('.client').forEach(el => el.classList.remove('active'));
                clientEl.classList.add('active');

                // 🔹 Get client info
                selectedClientId = clientEl.dataset.id;
                selectedClientName = clientEl.dataset.name; // ✅ store name for dynamic display

                // 🔹 Update header and reset display
                chatHeader.textContent = `Chat with ${selectedClientName}`;
                chatBox.innerHTML = '<div style="color:#aaa; text-align:center; margin-top:12px;">Loading messages...</div>';

                // 🔹 Reset chat state for new client
                shownMessages.clear();
                lastTimestamp = "0000-00-00 00:00:00";

                // 🔹 Create new chat input form
                createChatForm();

                // 🔹 Clear chat box and load messages for new client
                chatBox.innerHTML = '';
                await fetchMessages();

                // 🔹 Restart polling for the new client
                if (pollIntervalId) clearInterval(pollIntervalId);
                pollIntervalId = setInterval(fetchMessages, 3000);
            });
        });
    </script>
    </body>
</html>

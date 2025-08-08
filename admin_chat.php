<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: flex;
            flex-direction: column;
            height: 100vh; /* Set height to fill the viewport */
        }

        .chat-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            flex-grow: 1; /* Allows the chat container to fill available space */
            overflow-y: auto;
            margin-bottom: 20px; /* Add margin to separate from the form */
        }

        .message {
            margin-bottom: 15px;
            display: flex;
        }

        .message.admin {
            justify-content: flex-end;
            text-align: right;
        }
        
        .message.client {
            justify-content: flex-start;
            text-align: left;
        }

        .message .content {
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 60%;
        }

        .message.admin .content {
            background: #007BFF;
            text-align: left;
        }

        .message.client .content {
            background: #444;
        }

        form {
            display: flex;
            gap: 10px;
            margin-top: auto; /* Pushes the form to the bottom */
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: #333;
            color: #fff;
            outline: none;
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
        
        .message-header {
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }

        .message-timestamp {
            font-size: 10px;
            color: #ccc;
            display: block;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>TITULO Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
<<<<<<< HEAD
        <a href="client_request.php">Client Requests</a>
        <a href="client_updates.php">Client Updates</a>
=======
        <a href="admin_client_request.php">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
>>>>>>> e7d746828b0c93d756b627104781753b9d1ebf93
        <a href="transaction_files.php">Survey Files</a>
        <a href="admin_chat.php">Chat</a>
        <a href="index.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Admin-Client Chat</h1>
        <div class="chat-container" id="chatContainer">
            <!-- Messages will be injected here by JavaScript -->
        </div>

        <form id="chatForm">
            <input type="text" name="message" id="messageInput" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        const chatContainer = document.getElementById('chatContainer');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        
        let lastMessageTimestamp = 0;

        function renderMessage(msg) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', msg.sender);
            
            const contentDiv = document.createElement('div');
            contentDiv.classList.add('content');
            
            const messageHeader = document.createElement('span');
            messageHeader.classList.add('message-header');
            messageHeader.textContent = msg.sender === 'admin' ? 'You' : 'Client';

            const messageText = document.createTextNode(msg.message);

            const timestampSpan = document.createElement('span');
            timestampSpan.classList.add('message-timestamp');
            timestampSpan.textContent = new Date(msg.timestamp * 1000).toLocaleString();

            contentDiv.appendChild(messageHeader);
            contentDiv.appendChild(messageText);
            contentDiv.appendChild(timestampSpan);
            messageDiv.appendChild(contentDiv);
            
            chatContainer.appendChild(messageDiv);
        }

        async function fetchMessages() {
            try {
                const response = await fetch(`get_messages.php?lastTimestamp=${lastMessageTimestamp}`);
                const newMessages = await response.json();

                if (newMessages.length > 0) {
                    newMessages.forEach(msg => {
                        renderMessage(msg);
                    });
                    lastMessageTimestamp = newMessages[newMessages.length - 1].timestamp;
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        }
        
        // Handle form submission to send new message
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (message === '') return;
            
            try {
                const formData = new FormData();
                formData.append('message', message);
                formData.append('sender', 'admin'); 

                const response = await fetch('send_message.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                
                if (result.success) {
                    messageInput.value = '';
                    fetchMessages();
                } else {
                    console.error('Failed to send message:', result.error);
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        });

        // Initial fetch and then poll for new messages every 2 seconds
        document.addEventListener('DOMContentLoaded', () => {
            fetchMessages();
            setInterval(fetchMessages, 2000); 
        });

    </script>

</body>
</html>

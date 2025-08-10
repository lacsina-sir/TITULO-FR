<?php
session_start();
$client_id = $_SESSION['client_id'];
$conn = new mysqli("localhost", "root", "", "titulo_db");
$result = $conn->query("SELECT * FROM chat_messages WHERE client_id = $client_id ORDER BY id ASC");
$messages = [];
while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
}
echo json_encode($messages);
?>

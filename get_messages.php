<?php
// get_messages.php
$conn = new mysqli("localhost", "root", "", "titulo_db");

$clientId = intval($_GET['client_id']);
$lastTimestamp = $_GET['lastTimestamp'] ?? '0000-00-00 00:00:00';

$query = "SELECT m.*, c.name 
        FROM chat_messages m 
        LEFT JOIN clients c ON m.client_id = c.id 
        WHERE m.client_id = $clientId AND m.timestamp > '$lastTimestamp' 
        ORDER BY m.timestamp ASC";

$result = $conn->query($query);
$messages = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($messages);
?>

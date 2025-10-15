<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "titulo_db");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "DB connection failed"]));
}

$user_id = $_GET['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT id, sender, message, file_path, created_at AS timestamp
    FROM chat_messages
    WHERE user_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "DB connection failed"]));
}

$user_id = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? 1);
$sender = $_POST['sender'] ?? 'user';
$message = trim($_POST['message'] ?? '');
$file_path = null;

// Handle file upload (optional)
if (!empty($_FILES['file']['name'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES["file"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        $file_path = $targetFile;
    }
}

$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender, message, file_path) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $sender, $message, $file_path);
$success = $stmt->execute();

if ($success) {
    $newMessageId = $conn->insert_id;
    $timestamp = date('Y-m-d H:i:s'); // current server timestamp
    echo json_encode([
        "success" => true,
        "message" => [
            "id" => $newMessageId,
            "sender" => $sender,
            "message" => $message,
            "file_path" => $file_path,
            "timestamp" => $timestamp,
            "client_name" => $_SESSION['first_name'] ?? 'Client'
        ]
    ]);
} else {
    echo json_encode(["success" => false]);
}

$stmt->close();
$conn->close();
?>

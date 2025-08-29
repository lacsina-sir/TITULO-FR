<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($id <= 0 || empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("UPDATE client_forms SET status = 'rejected', rejection_reason = ? WHERE id = ?");
$stmt->bind_param("si", $reason, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>

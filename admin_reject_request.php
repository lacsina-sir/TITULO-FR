<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if (!isset($_POST['id'], $_POST['reason'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

$id = intval($_POST['id']);
$reason = trim($_POST['reason']);

// Get the form
$stmt = $conn->prepare("SELECT * FROM client_forms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();
$stmt->close();

if (!$form) {
    echo json_encode(['success' => false, 'error' => 'Request not found']);
    exit;
}

$clientName = $form['name'] . ' ' . $form['last_name'];
$type = $form['type'];
$userId = $form['user_id'];
$now = date('Y-m-d H:i:s');

// Insert into rejected_requests
$insert = $conn->prepare("
    INSERT INTO rejected_requests (user_id, client_name, type, reason, rejected_at)
    VALUES (?, ?, ?, ?, ?)
");
$insert->bind_param("issss", $userId, $clientName, $type, $reason, $now);
$success = $insert->execute();
$insert->close();

// Update original status
$update = $conn->prepare("UPDATE client_forms SET status = 'rejected' WHERE id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

echo json_encode(['success' => $success]);
$conn->close();
?>

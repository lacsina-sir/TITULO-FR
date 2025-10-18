<?php
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing ID']);
    exit;
}

$id = intval($_POST['id']);

// Fetch the client form by ID
$stmt = $conn->prepare("SELECT * FROM client_forms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$formResult = $stmt->get_result();
$form = $formResult->fetch_assoc();
$stmt->close();

if (!$form) {
    echo json_encode(['success' => false, 'error' => 'Request not found']);
    exit;
}

// Prepare data
$clientName = $form['name'] . ' ' . $form['last_name'];
$status = 'Approved';
$now = date('Y-m-d H:i:s');
$details = json_encode($form);

// Insert into pending_updates
$insert = $conn->prepare("
    INSERT INTO pending_updates (user_id, client_name, request_type, transaction_number, status, last_updated, details)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$insert->bind_param(
    "issssss",
    $form['user_id'],
    $clientName,
    $form['type'],
    $form['transaction_number'],
    $status,
    $now,
    $details
);
$success = $insert->execute();
$insert->close();

// Update original form status
$update = $conn->prepare("UPDATE client_forms SET status = 'approved' WHERE id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

echo json_encode(['success' => $success]);
$conn->close();
?>

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
$clientName = $form['name'] . ' ' . $form['last_name'];
// set server timezone for consistent timestamps
date_default_timezone_set('Asia/Manila');
$status = 'Approved';
$now = date('Y-m-d H:i:s');
$details = json_encode($form);

// ✅ Insert into pending_updates
$insertPending = $conn->prepare("
    INSERT INTO pending_updates (user_id, client_name, request_type, transaction_number, status, last_updated, details)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$insertPending->bind_param(
    "issssss",
    $form['user_id'],
    $clientName,
    $form['type'],
    $form['transaction_number'],
    $status,
    $now,
    $details
);
$success = $insertPending->execute();
$insertPending->close();

// Insert a single progress_tracker row for this approval
$pt = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, NOW())");
if (!$pt) {
    echo json_encode(['success' => false, 'error' => 'DB prepare failed (progress_tracker): ' . $conn->error]);
    $conn->close();
    exit;
}
$stat = 'Approved';
$remarks = 'Approved by admin';
$expenses = '';
$pt->bind_param("isss", $id, $stat, $remarks, $expenses);
if (!$pt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to insert progress_tracker: ' . $pt->error]);
    $pt->close();
    $conn->close();
    exit;
}
$pt->close();

// Update original form status
$update = $conn->prepare("UPDATE client_forms SET status = 'approved' WHERE id = ?");
if (!$update) {
    echo json_encode(['success' => false, 'error' => 'DB prepare failed (update client_forms): ' . $conn->error]);
    $conn->close();
    exit;
}
$update->bind_param("i", $id);
if (!$update->execute()) {
    echo json_encode(['success' => false, 'error' => 'Failed to update client_forms: ' . $update->error]);
    $update->close();
    $conn->close();
    exit;
}
$update->close();

echo json_encode(['success' => (bool)$success]);
$conn->close();
?>

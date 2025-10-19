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

// Log into progress tracker
$insert2 = $conn->prepare("
    INSERT INTO progress_tracker (client_id, status, reason, updated_at)
    VALUES (?, 'rejected', ?, NOW())
");
$insert2->bind_param("is", $id, $reason);
$insert2->execute();
$insert2->close();


// ✅ Log in progress tracker
// ✅ Log in progress_tracker with consistent status text
$pt = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, NOW())");
if ($pt) {
    $stat = 'Approved';
    $remarks = 'Approved by admin';
    $expenses = '';
    $pt->bind_param("isss", $id, $stat, $remarks, $expenses);
    $pt->execute();
    $pt->close();
}

// ✅ Update original form status
$update = $conn->prepare("UPDATE client_forms SET status = 'approved' WHERE id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

echo json_encode(['success' => $success]);
$conn->close();
?>

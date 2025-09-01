<?php
// reject.php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) exit(json_encode(['success'=>false,'error'=>'DB connection failed']));

$id = intval($_POST['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($id <= 0 || empty($reason)) exit(json_encode(['success'=>false,'error'=>'Invalid input']));

// Get form details
$form = $conn->query("SELECT * FROM client_forms WHERE id = $id")->fetch_assoc();
$details = json_encode($form);

// Insert into rejected_requests
$stmt = $conn->prepare("INSERT INTO rejected_requests (client_name, type, reason, details, rejected_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $form['name'] . ' ' . $form['last_name'], $form['type'], $reason, $details);
$stmt->execute();
$stmt->close();

// Update client_forms status
$conn->query("UPDATE client_forms SET status='Rejected', rejection_reason='".$conn->real_escape_string($reason)."' WHERE id=$id");

echo json_encode(['success'=>true]);
$conn->close();
?>

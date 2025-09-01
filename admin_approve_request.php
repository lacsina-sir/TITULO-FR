<?php
// Approves a client request and adds it to pending updates

$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    // Update client_forms status
    $update = $conn->prepare("UPDATE client_forms SET status = 'Approved' WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
    $update->close();

    // Get form details for update
    $insert = $conn->prepare("INSERT INTO pending_updates (client_name, status, last_updated, transaction_number, request_type) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("sssss", $clientName, $status, $now, $form['transaction_number'], $form['type']);
    $getForm->execute();
    $formResult = $getForm->get_result();
    $form = $formResult->fetch_assoc();
    $getForm->close();

    if ($form) {
        // Insert into pending_updates
        $clientName = '';
        $getClient = $conn->prepare("SELECT name, last_name FROM users WHERE id = ?");
        $getClient->bind_param("i", $form['user_id']);
        $getClient->execute();
        $clientResult = $getClient->get_result();
        $client = $clientResult->fetch_assoc();
        $getClient->close();
        if ($client) {
            $clientName = $client['name'] . ' ' . $client['last_name'];
        }
        $status = 'Pending';
        $now = date('Y-m-d H:i:s');
        $insert = $conn->prepare("INSERT INTO pending_updates (client_name, status, last_updated) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $clientName, $status, $now);
        $insert->execute();
        $insert->close();
    }
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit();
?>

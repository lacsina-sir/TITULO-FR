<?php
    $conn = new mysqli("localhost", "root", "", "titulo_db");
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'error' => 'DB connection failed']));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['client_id'])) {
        $message = trim($_POST['message']);
        $client_id = intval($_POST['client_id']);
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender, client_id, message) VALUES ('admin', ?, ?)");
        $stmt->bind_param("is", $client_id, $message);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
?>

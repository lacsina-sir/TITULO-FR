<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid ID"]);
    exit;
}

$res = $conn->query("SELECT admin_updates FROM pending_updates WHERE id = $id");
if ($res && $row = $res->fetch_assoc()) {
    $updates = json_decode($row['admin_updates'], true);

    // ✅ If decoding failed or empty, return empty array
    if (!is_array($updates)) {
        $updates = [];
    }

    echo json_encode([
        "success" => true,
        "updates" => $updates
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["success" => false, "error" => "Record not found"]);
}

$conn->close();
exit;
?>

<?php
include 'db_connection.php';

$form_id = intval($_GET['form_id'] ?? 0);

$query = "SELECT status, remarks, DATE_FORMAT(updated_at, '%m/%d/%Y (%h:%i %p)') AS date 
        FROM client_tracking 
        WHERE form_id = ? 
        ORDER BY updated_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();

$timeline = [];
while ($row = $result->fetch_assoc()) {
    $timeline[] = $row;
}

echo json_encode(["success" => true, "timeline" => $timeline]);
?>

<?php
header('Content-Type: application/json; charset=utf-8');
ob_clean(); // clear any accidental whitespace

$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$id = $_POST['id'] ?? '';
$client_name = $_POST['client_name'] ?? '';
$area = $_POST['area'] ?? '';
$lot = $_POST['lot'] ?? '';
$location = $_POST['location'] ?? '';
$type = $_POST['type'] ?? '';
$surveyplan = $_POST['surveyplan'] ?? '';
$description = $_POST['description'] ?? '';
$status = $_POST['status'] ?? '';
$date_processed = $_POST['date_processed'] ?? '';
$updates = $_POST['updates'] ?? '';
$updates = isset($_POST['updates']) ? $_POST['updates'] : '[]';

if (empty($id)) {
    echo json_encode(["success" => false, "error" => "Missing ID"]);
    exit;
}

// ✅ Get current details to preserve file_paths
$result = $conn->query("SELECT details, admin_updates FROM pending_updates WHERE id = " . intval($id));
if (!$result || $result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Record not found"]);
    exit;
}

$oldDetails = $result->fetch_assoc();
$detailsArr = json_decode($oldDetails['details'] ?? '{}', true) ?: [];

// ✅ Update all fields except file_paths
$detailsArr['name'] = $client_name;
$detailsArr['ls_area'] = $area;
$detailsArr['ls_lot'] = $lot;
$detailsArr['ls_location'] = $location;
$detailsArr['type'] = $type;
$detailsArr['surveyplan'] = $surveyplan;
$detailsArr['description'] = $description;
$detailsArr['date_processed'] = $date_processed;

$details = json_encode($detailsArr, JSON_UNESCAPED_UNICODE);

// ✅ Save details, status, and admin updates permanently
$stmt = $conn->prepare("UPDATE pending_updates SET details=?, status=?, admin_updates=?, last_updated=NOW() WHERE id=?");
$stmt->bind_param("sssi", $details, $status, $updates, $id);
$success = $stmt->execute();
$stmt->close();

$formatted_date = ($date_processed && strtotime($date_processed)) ? date('m/d/Y', strtotime($date_processed)) : '—';

echo json_encode([
    "success" => $success,
    "updated_name" => $client_name,
    "updated_status" => $status,
    "updated_date_processed" => $formatted_date,
    "last_updated" => date('F j, Y, g:i a')
], JSON_UNESCAPED_UNICODE);

$conn->close();
exit;
?>

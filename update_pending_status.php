<?php
header('Content-Type: application/json; charset=utf-8');
// suppress PHP warnings from being output (they would break JSON parsing on client)
ini_set('display_errors', '0');
error_reporting(0);
// clear output buffer if one exists
if (ob_get_level()) ob_clean();

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
// Fetch pending_updates row (use prepared statement)
$stmt = $conn->prepare("SELECT id, user_id, details, admin_updates FROM pending_updates WHERE id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "DB prepare failed: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "DB execute failed: " . $stmt->error]);
    $stmt->close();
    exit;
}
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Record not found"]);
    $stmt->close();
    exit;
}
$oldRow = $result->fetch_assoc();
$stmt->close();

$detailsArr = json_decode($oldRow['details'] ?? '{}', true) ?: [];
// Determine the underlying client form id if present in details (client_forms.id)
$client_form_id = $detailsArr['id'] ?? $detailsArr['form_id'] ?? null;
// If not available, fallback to pending_updates.user_id (this is the client's user id)
$client_user_id = $oldRow['user_id'] ?? null;

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
// Update pending_updates with new details and admin_updates payload
// prepare update statement
$stmt = $conn->prepare("UPDATE pending_updates SET details=?, status=?, admin_updates=?, last_updated=NOW() WHERE id=?");
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "DB prepare failed (update): " . $conn->error]);
    exit;
}
$stmt->bind_param("sssi", $details, $status, $updates, $id);
$success = $stmt->execute();
if ($success === false) {
    echo json_encode(["success" => false, "error" => "DB execute failed (update): " . $stmt->error]);
    $stmt->close();
    exit;
}
$stmt->close();

// Save tracking records for client (use progress_tracker table)
$updatesArr = [];
if (!empty($updates)) {
    $updatesArr = json_decode($updates, true);
    if (!is_array($updatesArr)) $updatesArr = [];
}

// If there are admin updates (array of rows), insert them individually into progress_tracker
if (!empty($updatesArr) && is_array($updatesArr)) {
    $insertTrack = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, ?)");
    if (!$insertTrack) {
        echo json_encode(["success" => false, "error" => "DB prepare failed (insertTrack): " . $conn->error]);
        exit;
    }
    foreach ($updatesArr as $u) {
        $uDate = $u['date'] ?? date('Y-m-d H:i:s');
        $uStatus = $u['status'] ?? $status;
        $uRemarks = $u['remarks'] ?? '';
        $uExpenses = $u['expenses'] ?? '';
        // Use client_form_id if available, otherwise try client_user_id
        $trackClientId = $client_form_id ?? $client_user_id ?? 0;
        $insertTrack->bind_param("issss", $trackClientId, $uStatus, $uRemarks, $uExpenses, $uDate);
        if (!$insertTrack->execute()) {
            // non-fatal: continue but record the error to return later
            $lastInsertError = $insertTrack->error;
        }
    }
    $insertTrack->close();
} else {
    // If no admin updates array, still insert one progress_tracker row to record the status change
    $insertTrack = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, ?)");
    if (!$insertTrack) {
        echo json_encode(["success" => false, "error" => "DB prepare failed (insertTrack single): " . $conn->error]);
        exit;
    }
    $uDate = date('Y-m-d H:i:s');
    $trackClientId = $client_form_id ?? $client_user_id ?? 0;
    $insertTrack->bind_param("issss", $trackClientId, $status, '', '', $uDate);
    if (!$insertTrack->execute()) {
        $lastInsertError = $insertTrack->error;
    }
    $insertTrack->close();
}


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

// Update client_forms status
$updateForm = $conn->prepare("UPDATE client_forms SET status = ? WHERE id = ?");
$updateForm->bind_param("si", $latestStatus, $formId);
$updateForm->execute();

// Insert into progress_tracker
foreach ($updates as $u) {
    $insert = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("issss", $formId, $u['status'], $u['remarks'], $u['expenses'], $u['date']);
    $insert->execute();
}

?>

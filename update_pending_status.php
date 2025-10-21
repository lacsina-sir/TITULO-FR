<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(0);
if (ob_get_level()) ob_clean();

date_default_timezone_set('Asia/Manila');

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
$purpose = $_POST['purpose'] ?? '';

if (empty($id)) {
    echo json_encode(["success" => false, "error" => "Missing ID"]);
    exit;
}

// ✅ Get current details to preserve file_paths
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
$client_form_id = $detailsArr['id'] ?? $detailsArr['form_id'] ?? null;
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
$detailsArr['purpose'] = $purpose ?: ($detailsArr['purpose'] ?? '');

$details = json_encode($detailsArr, JSON_UNESCAPED_UNICODE);

// ✅ Save details, status, and admin updates permanently
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

if (!empty($client_form_id)) {
    $updFields = [];
    $params = [];
    $types = '';
    if ($area !== '') { $updFields[] = 'ls_area = ?'; $params[] = $area; $types .= 's'; }
    if ($lot !== '') { $updFields[] = 'ls_lot = ?'; $params[] = $lot; $types .= 's'; }
    if ($location !== '') { $updFields[] = 'ls_location = ?'; $params[] = $location; $types .= 's'; }
    if ($purpose !== '') { $updFields[] = 'purpose = ?'; $params[] = $purpose; $types .= 's'; }
    if (!empty($updFields)) {
        $types .= 'i';
        $params[] = $client_form_id;
        $sql = "UPDATE client_forms SET " . implode(', ', $updFields) . " WHERE id = ?";
        $ustmt = $conn->prepare($sql);
        if ($ustmt) {
            // bind params dynamically
            $bindNames = array_merge([$types], $params);
            $tmp = [];
            foreach ($bindNames as $k => $v) $tmp[$k] = &$bindNames[$k];
            call_user_func_array([$ustmt, 'bind_param'], $tmp);
            $ustmt->execute();
            $ustmt->close();
        }
    }
}

// Save tracking records for client
$updatesArr = [];
if (!empty($updates)) {
    $updatesArr = json_decode($updates, true);
    if (!is_array($updatesArr)) $updatesArr = [];
}

if (!empty($updatesArr) && is_array($updatesArr)) {
    $insertTrack = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, ?)");
    if (!$insertTrack) {
        echo json_encode(["success" => false, "error" => "DB prepare failed (insertTrack): " . $conn->error]);
        exit;
    }
    $trackClientId = $client_form_id ?? $client_user_id ?? 0;
    $lastStatusRow = null;
    $lastTrackStmt = $conn->prepare("SELECT status, remarks, expenses FROM progress_tracker WHERE client_id = ? ORDER BY updated_at DESC LIMIT 1");
    if ($lastTrackStmt) {
        $lastTrackStmt->bind_param("i", $trackClientId);
        if ($lastTrackStmt->execute()) {
            $lr = $lastTrackStmt->get_result();
            if ($lr && $lr->num_rows) $lastStatusRow = $lr->fetch_assoc();
        }
        $lastTrackStmt->close();
    }
    foreach ($updatesArr as $u) {
        $uDate = date('Y-m-d H:i:s');
        $uStatus = isset($u['status']) ? trim((string)$u['status']) : '';
        $uRemarks = isset($u['remarks']) ? trim((string)$u['remarks']) : '';
        $uExpenses = isset($u['expenses']) ? trim((string)$u['expenses']) : '';

        $trackClientId = intval($trackClientId);

        $isDuplicate = false;
        $dupStmt = $conn->prepare("SELECT id FROM progress_tracker WHERE client_id = ? AND TRIM(COALESCE(status,'')) = ? AND TRIM(COALESCE(remarks,'')) = ? AND TRIM(COALESCE(expenses,'')) = ? ORDER BY updated_at DESC LIMIT 1");
        if ($dupStmt) {
            $dupStmt->bind_param("isss", $trackClientId, $uStatus, $uRemarks, $uExpenses);
            if ($dupStmt->execute()) {
                $dr = $dupStmt->get_result();
                if ($dr && $dr->num_rows) {
                    $isDuplicate = true;
                }
            }
            $dupStmt->close();
        }

        if ($isDuplicate) {
            continue;
        }
        $insertTrack->bind_param("issss", $trackClientId, $uStatus, $uRemarks, $uExpenses, $uDate);
        if (!$insertTrack->execute()) {
            $lastInsertError = $insertTrack->error;
        } else {
            $lastStatusRow = ['status' => $uStatus, 'remarks' => $uRemarks, 'expenses' => $uExpenses];
        }
    }
    $insertTrack->close();
} else {
    $insertTrack = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, ?)");
    if (!$insertTrack) {
        echo json_encode(["success" => false, "error" => "DB prepare failed (insertTrack single): " . $conn->error]);
        exit;
    }
    $uDate = date('Y-m-d H:i:s');
    $trackClientId = $client_form_id ?? $client_user_id ?? 0;
    $shouldInsert = true;
    $checkLast = $conn->prepare("SELECT status, remarks, expenses FROM progress_tracker WHERE client_id = ? ORDER BY updated_at DESC LIMIT 1");
    if ($checkLast) {
        $checkLast->bind_param("i", $trackClientId);
        if ($checkLast->execute()) {
            $lr = $checkLast->get_result();
            if ($lr && $lr->num_rows) {
                $lastRow = $lr->fetch_assoc();
                $lastS = trim((string)$lastRow['status']);
                $lastR = trim((string)$lastRow['remarks']);
                $lastE = trim((string)$lastRow['expenses']);
                if (strcasecmp($lastS, trim((string)$status)) === 0 && $lastR === '' && $lastE === '') {
                    $shouldInsert = false;
                }
            }
        }
        $checkLast->close();
    }
    if ($shouldInsert) {
        $dupStmt2 = $conn->prepare("SELECT id FROM progress_tracker WHERE client_id = ? AND TRIM(COALESCE(status,'')) = ? AND TRIM(COALESCE(remarks,'')) = ? AND TRIM(COALESCE(expenses,'')) = ? ORDER BY updated_at DESC LIMIT 1");
        $skip = false;
        if ($dupStmt2) {
            $dupStmt2->bind_param("isss", $trackClientId, $status, '', '');
            if ($dupStmt2->execute()) {
                $dr2 = $dupStmt2->get_result();
                if ($dr2 && $dr2->num_rows) $skip = true;
            }
            $dupStmt2->close();
        }
        if (!$skip) {
        $insertTrack->bind_param("issss", $trackClientId, $status, '', '', $uDate);
        if (!$insertTrack->execute()) {
            $lastInsertError = $insertTrack->error;
        }
        }
    }
    $insertTrack->close();
}


$formatted_date = ($date_processed && strtotime($date_processed)) ? date('m/d/Y', strtotime($date_processed)) : '—';

echo json_encode([
    "success" => $success,
    "updated_name" => $client_name,
    "updated_status" => $status,
    "updated_date_processed" => $formatted_date,
    "last_updated" => date('Y-m-d H:i:s'),
    "updated_purpose" => $detailsArr['purpose'] ?? '',
    "updated_area" => $detailsArr['ls_area'] ?? ''
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

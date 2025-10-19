<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "titulo_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// Accept form-encoded POST or raw JSON/urlencoded body from fetch
$rawInput = file_get_contents('php://input');
if (empty($_POST) && $rawInput) {
    // try JSON
    $parsed = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
        $_POST = array_merge($_POST, $parsed);
    } else {
        // try parse_str for urlencoded bodies
        parse_str($rawInput, $parsedStr);
        if (is_array($parsedStr)) {
            $_POST = array_merge($_POST, $parsedStr);
        }
    }
}

if (!isset($_POST['id'], $_POST['reason'])) {
    echo json_encode(['success' => false, 'error' => 'Missing id or reason']);
    exit;
}

$id = intval($_POST['id']);
$reason = trim($_POST['reason']);

// Fetch client form
$stmt = $conn->prepare("SELECT * FROM client_forms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();
$stmt->close();

if (!$form) {
    echo json_encode(['success' => false, 'error' => 'Request not found']);
    exit;
}

// Prepare data
$clientName = $form['name'] . ' ' . $form['last_name'];
$type = $form['type'];
$userId = $form['user_id'];
$now = date('Y-m-d H:i:s');

$file_paths = '';
if (!empty($form['file_paths'])) {
    $file_paths = $form['file_paths'];
} elseif (!empty($_POST['file_paths'])) {
    // if payload provided file_paths (array or json string)
    if (is_array($_POST['file_paths'])) {
        $file_paths = json_encode($_POST['file_paths']);
    } else {
        // could be a JSON string or comma-separated
        $file_paths = trim($_POST['file_paths']);
    }
}

// Prepare and execute insert with error checking
$insert_sql_with_files = "INSERT INTO rejected_requests (user_id, client_name, type, reason, rejected_at, file_paths) VALUES (?, ?, ?, ?, ?, ?)";
$insert_sql_no_files = "INSERT INTO rejected_requests (user_id, client_name, type, reason, rejected_at) VALUES (?, ?, ?, ?, ?)";

$insert = $conn->prepare($insert_sql_with_files);
if ($insert !== false) {
    $bind = $insert->bind_param("isssss", $userId, $clientName, $type, $reason, $now, $file_paths);
    if ($bind === false) {
        echo json_encode(['success' => false, 'error' => 'Bind failed: ' . $insert->error]);
        $insert->close();
        $conn->close();
        exit;
    }
    $success = $insert->execute();
    if ($success === false) {
        echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $insert->error]);
        $insert->close();
        $conn->close();
        exit;
    }
    $insert->close();
} else {
    // If table doesn't have file_paths (column count mismatch), fall back to insert without file_paths
    if (stripos($conn->error, 'Column count') !== false || stripos($conn->error, 'Unknown column') !== false) {
        $insert = $conn->prepare($insert_sql_no_files);
        if ($insert === false) {
            echo json_encode(['success' => false, 'error' => 'Fallback prepare failed: ' . $conn->error]);
            $conn->close();
            exit;
        }
    $bind = $insert->bind_param("issss", $userId, $clientName, $type, $reason, $now);
        if ($bind === false) {
            echo json_encode(['success' => false, 'error' => 'Fallback bind failed: ' . $insert->error]);
            $insert->close();
            $conn->close();
            exit;
        }
        $success = $insert->execute();
        if ($success === false) {
            echo json_encode(['success' => false, 'error' => 'Fallback execute failed: ' . $insert->error]);
            $insert->close();
            $conn->close();
            exit;
        }
        $insert->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
        $conn->close();
        exit;
    }
}

// Update client_forms status and save rejection reason
$update = $conn->prepare("UPDATE client_forms SET status = 'Rejected', rejection_reason = ? WHERE id = ?");
$update->bind_param("si", $reason, $id);
$update->execute();
$update->close();

// Insert into progress_tracker so client tracking shows the rejection
$pt = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, expenses, updated_at) VALUES (?, ?, ?, ?, NOW())");
if ($pt) {
    $stat = 'Rejected';
    $remarks = $reason;
    $expenses = '';
    $pt->bind_param("isss", $id, $stat, $remarks, $expenses);
    $pt->execute();
    $pt->close();
}

echo json_encode(['success' => $success]);
$conn->close();
?>

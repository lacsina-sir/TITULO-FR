<?php
// Database connection
session_start();
require 'db_connection.php';

// Validate and sanitize input
$id = intval($_POST['id']);
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$survey_type = trim($_POST['survey_type']);
$location = trim($_POST['location']);
$status = trim($_POST['status']);
$date_submitted = $_POST['date_submitted'];

// Combine first and last name
$client_name = $first_name . ' ' . $last_name;

// Prepare and execute update query
$stmt = $conn->prepare("UPDATE survey_files SET client_name = ?, survey_type = ?, location = ?, status = ?, date_submitted = ? WHERE id = ?");
$stmt->bind_param("sssssi", $client_name, $survey_type, $location, $status, $date_submitted, $id);

if ($stmt->execute()) {
    echo "Survey file updated successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

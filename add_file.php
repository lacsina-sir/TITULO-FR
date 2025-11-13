<?php
// Database connection
session_start();
require 'db_connection.php';

// Validate and sanitize input
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$survey_type = trim($_POST['survey_type']);
$location = trim($_POST['location']);
$status = trim($_POST['status']);
$date_submitted = $_POST['date_submitted'];

// Combine first and last name
$client_name = $first_name . ' ' . $last_name;

// Prepare and execute insert query
$stmt = $conn->prepare("INSERT INTO survey_files (client_name, survey_type, location, status, date_submitted) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $client_name, $survey_type, $location, $status, $date_submitted);

if ($stmt->execute()) {
    echo "Survey file saved successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

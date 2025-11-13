<?php
$host = 'sql107.infinityfree.com';
$dbname = 'if0_40404405_titulo_db';
$username = 'if0_40404405';
$password = 'titulofr2025';

$conn = new mysqli("sql107.infinityfree.com", "if0_40404405", "titulofr2025", "if0_40404405_titulo_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

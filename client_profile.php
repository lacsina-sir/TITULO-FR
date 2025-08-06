<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'compass_north';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);

    $updateSql = "UPDATE users SET first_name=?, last_name=?, email=?, contact_number=? WHERE id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $contact_number, $userId);
    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
    } else {
        $message = "Failed to update profile.";
    }
}

$sql = "SELECT first_name, last_name, email, contact_number FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Profile | Titulo</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom right, #0f2027, #203a43, #2c5364);
            color: #fff;
        }

        .topnav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #111;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
            z-index: 1000;
        }

        .topnav .brand {
            font-size: 20px;
            font-weight: bold;
        }

        .topnav .nav-links {
            display: flex;
            gap: 20px;
        }

        .topnav .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .topnav .nav-links a:hover {
            text-decoration: underline;
        }

        .main {
            padding: 100px 20px 40px;
            max-width: 600px;
            margin: auto;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #444;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 200, 255, 0.4);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ddd;
        }

        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: #222;
            border: 1px solid #555;
            border-radius: 6px;
            color: white;
        }

        input[type="submit"] {
            background-color: #00c8ff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0099cc;
        }

        .message {
            margin-top: 15px;
            font-size: 14px;
            color: #0f0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="topnav">
    <div class="brand">Titulo Client Portal</div>
    <div class="nav-links">
        <a href="client_dashboard.php">Dashboard</a>
        <a href="client_files.php">Files</a>
        <a href="client_profile.php">Profile</a>
        <a href="client_chat.php">Chats</a>
        <a href="client-side_tracking.php">Tracking</a>
        <a href="index.php">Logout</a>
    </div>
</div>

<div class="main">
    <h1>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>!</h1>

    <form method="post" action="client_profile.php">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>">

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">

        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" required value="<?= htmlspecialchars($user['contact_number']) ?>">

        <input type="submit" value="Save Changes">
    </form>

    <?php if (!empty($message)) : ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>

</body>
</html>


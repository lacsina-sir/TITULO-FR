<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit();
}

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'titulo_db';

// create connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$userId = (int) $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // collect and sanitize inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // basic validation
    if ($first_name === '' || $last_name === '') {
        $message = "First name and last name are required.";
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please provide a valid email address.";
    } else {
        // check email uniqueness (exclude current user)
        $checkEmailSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        if ($checkStmt = $conn->prepare($checkEmailSql)) {
            $checkStmt->bind_param("si", $email, $userId);
            $checkStmt->execute();
            $checkStmt->store_result();
            if ($checkStmt->num_rows > 0) {
                $message = "That email address is already in use by another account.";
                $checkStmt->close();
            } else {
                $checkStmt->close();
                
                $updateSql = "UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?";
                if ($stmt = $conn->prepare($updateSql)) {
                    $stmt->bind_param("sssi", $first_name, $last_name, $email, $userId);
                    if ($stmt->execute()) {
                        $message = "Profile updated successfully.";
                        //refresh session values:
                        $_SESSION['first_name'] = $first_name;
                        $_SESSION['last_name'] = $last_name;
                        $_SESSION['email'] = $email;
                    } else {
                        $message = "Failed to update profile. Please try again.";
                    }
                    $stmt->close();
                } else {
                    $message = "Database error (prepare failed).";
                }
            }
        } else {
            $message = "Database error (prepare failed).";
        }
    }
}

// fetch current user data for form (no contact_number)
$sql = "SELECT first_name, last_name, email FROM users WHERE id=?";
$user = ['first_name'=>'', 'last_name'=>'', 'email'=>''];
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user = $row;
    }
    $stmt->close();
}

$conn->close();
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
            min-height: 100vh;
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

        .form-section {
            background: rgba(255,255,255,0.05);
            border-left: 5px solid #00ffcc;
            padding: 32px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section h2 {
            color: #00ffcc;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #fff;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 15px;
        }

        button[type="submit"] {
            width: 103%;
            padding: 10px;
            background: #00ffcc;
            color: #222;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: #009bb5;
        }

        .message {
            margin-top: 20px;
            font-size: 14px;
            color: #00ffcc;
            text-align: center;
            font-weight: bold;
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
        <a href="client_form.php">Forms</a>
        <a href="client-side_tracking.php">Tracking</a>
        <a href="index.php">Logout</a>
    </div>
</div>

<div class="main">
    <div class="form-section">
        <h2>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>!</h2>

        <form method="post" action="client_profile.php">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <button type="submit">Save Changes</button>
        </form>

        <?php if (!empty($message)) : ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

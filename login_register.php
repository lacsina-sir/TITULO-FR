<?php
session_start();
require 'db_connection.php';

$register_msg = '';
$login_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);

        if ($stmt->execute()) {
            $register_msg = "Registration successful. You can now log in.";
        } else {
            $register_msg = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    if (isset($_POST['login'])) {
        // Login logic
        $email = trim($_POST['login_email']);
        $password = $_POST['login_password'];

        $stmt = $conn->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $first_name, $last_name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: client_dashboard.php");
                }
                exit();
            } else {
                $login_msg = "Invalid password.";
            }
        } else {
            $login_msg = "User not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login & Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1e1e2f;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        form {
            background: #2e2e4f;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            width: 300px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            border: none;
        }
        input[type="submit"] {
            background: #4CAF50;
            color: white;
            cursor: pointer;
        }
        h2 {
            margin-bottom: 10px;
        }
        .message {
            margin-top: 10px;
            color: #ff7373;
        }
    </style>
</head>
<body>

<h1>Welcome to Titulo Login/Register</h1>

<form method="POST">
    <h2>Register</h2>
    <input type="text" name="first_name" placeholder="First Name" required />
    <input type="text" name="last_name" placeholder="Last Name" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <select name="role" required>
        <option value="client">Client</option>
        <option value="admin">Admin</option>
    </select>
    <input type="submit" name="register" value="Register" />
    <?php if ($register_msg): ?>
        <div class="message"><?= $register_msg ?></div>
    <?php endif; ?>
</form>

<form method="POST">
    <h2>Login</h2>
    <input type="email" name="login_email" placeholder="Email" required />
    <input type="password" name="login_password" placeholder="Password" required />
    <input type="submit" name="login" value="Login" />
    <?php if ($login_msg): ?>
        <div class="message"><?= $login_msg ?></div>
    <?php endif; ?>
</form>

</body>
</html>
